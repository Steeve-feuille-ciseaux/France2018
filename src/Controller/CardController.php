<?php

namespace App\Controller;

use App\Entity\Card;
use App\Form\CardType;
use App\Repository\CardRepository;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/card')]
class CardController extends AbstractController
{
    #[Route('/', name: 'app_card_index', methods: ['GET'])]
    public function index(CardRepository $cardRepository): Response
    {
        return $this->render('card/index.html.twig', [
            'cards' => $cardRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_card_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, SessionInterface $session, PlayerRepository $playerRepository): Response
    {
        $card = new Card();
        
        // Si on revient de la page de vérification
        $tempCard = $session->get('temp_card');
        if ($tempCard && !$tempCard['card']->getId()) {
            $card = $tempCard['card'];
            // Recharger l'entité Player depuis la base de données
            if ($card->getPlayer()) {
                $card->setPlayer($playerRepository->find($card->getPlayer()->getId()));
            }
        }
        
        $form = $this->createForm(CardType::class, $card);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('cards_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... gérer l'exception si quelque chose se passe pendant le téléchargement du fichier
                }

                $card->setImageFilename($newFilename);
            }

            // Au lieu de persister directement, on stocke dans la session
            $session->set('temp_card', [
                'card' => $card,
                'image_path' => $imageFile ? $this->getParameter('cards_directory').'/'.$newFilename : null,
                'is_new' => true
            ]);

            return $this->redirectToRoute('app_card_check');
        }

        return $this->render('card/new.html.twig', [
            'card' => $card,
            'form' => $form,
        ]);
    }

    #[Route('/check', name: 'app_card_check', methods: ['GET'])]
    public function check(SessionInterface $session, PlayerRepository $playerRepository): Response
    {
        $tempCard = $session->get('temp_card');
        
        if (!$tempCard) {
            return $this->redirectToRoute('app_card_index');
        }

        // Recharger l'entité Player depuis la base de données
        $card = $tempCard['card'];
        if ($card->getPlayer()) {
            $card->setPlayer($playerRepository->find($card->getPlayer()->getId()));
        }

        return $this->render('card/check.html.twig', [
            'card' => $card,
            'is_new' => $tempCard['is_new'] ?? false
        ]);
    }

    #[Route('/edit/back', name: 'app_card_edit_back', methods: ['POST'])]
    public function editBack(Request $request, SessionInterface $session, PlayerRepository $playerRepository): Response
    {
        if (!$this->isCsrfTokenValid('edit_back', $request->request->get('_token'))) {
            return $this->redirectToRoute('app_card_index');
        }

        $tempCard = $session->get('temp_card');
        if (!$tempCard) {
            return $this->redirectToRoute('app_card_index');
        }

        // Recharger l'entité Player depuis la base de données
        $card = $tempCard['card'];
        if ($card->getPlayer()) {
            $card->setPlayer($playerRepository->find($card->getPlayer()->getId()));
        }

        // Si c'est une nouvelle carte, retourner vers le formulaire de création
        if (isset($tempCard['is_new']) && $tempCard['is_new']) {
            return $this->redirectToRoute('app_card_new');
        }

        // Sinon, retourner vers le formulaire d'édition
        return $this->redirectToRoute('app_card_edit', [
            'id' => $card->getId()
        ]);
    }

    #[Route('/confirm', name: 'app_card_confirm', methods: ['POST'])]
    public function confirm(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, PlayerRepository $playerRepository, CardRepository $cardRepository): Response
    {
        if (!$this->isCsrfTokenValid('confirm', $request->request->get('_token'))) {
            return $this->redirectToRoute('app_card_index');
        }

        $tempCard = $session->get('temp_card');
        if (!$tempCard) {
            return $this->redirectToRoute('app_card_index');
        }

        $card = $tempCard['card'];
        
        // Si c'est une modification, on récupère l'entité existante
        if (!$tempCard['is_new']) {
            $existingCard = $cardRepository->find($card->getId());
            if (!$existingCard) {
                throw $this->createNotFoundException('La carte n\'existe pas');
            }
            
            // Met à jour les propriétés de la carte existante
            $existingCard->setPlayer($playerRepository->find($card->getPlayer()->getId()));
            $existingCard->setClub($card->getClub());
            $existingCard->setPosition($card->getPosition());
            $existingCard->setNumber($card->getNumber());
            $existingCard->setStartSeason($card->getStartSeason());
            $existingCard->setEndSeason($card->getEndSeason());
            $existingCard->setSummary($card->getSummary());
            $existingCard->setNotableAction($card->getNotableAction());
            
            if ($card->getImageFilename() && $card->getImageFilename() !== $existingCard->getImageFilename()) {
                // Supprime l'ancienne image si elle existe
                if ($existingCard->getImageFilename()) {
                    $oldImagePath = $this->getParameter('cards_directory').'/'.$existingCard->getImageFilename();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $existingCard->setImageFilename($card->getImageFilename());
            }
            
            $card = $existingCard;
        }
        
        // Persiste la carte
        $entityManager->persist($card);
        $entityManager->flush();

        // Nettoie la session
        $session->remove('temp_card');

        $this->addFlash('success', 'La carte a été ' . ($tempCard['is_new'] ? 'créée' : 'modifiée') . ' avec succès.');
        return $this->redirectToRoute('app_card_index');
    }

    #[Route('/{id}/edit', name: 'app_card_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Card $card, EntityManagerInterface $entityManager, SluggerInterface $slugger, SessionInterface $session, PlayerRepository $playerRepository): Response
    {
        $form = $this->createForm(CardType::class, $card);
        
        // Si on revient de la page de vérification
        $tempCard = $session->get('temp_card');
        if ($tempCard && $tempCard['card']->getId() === $card->getId()) {
            // Recharger l'entité Player depuis la base de données
            $tempCard['card']->setPlayer($playerRepository->find($tempCard['card']->getPlayer()->getId()));
            $form = $this->createForm(CardType::class, $tempCard['card']);
        }
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('cards_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... gérer l'exception
                }

                $card->setImageFilename($newFilename);
            }

            // Stocke dans la session pour la vérification
            $session->set('temp_card', [
                'card' => $card,
                'image_path' => $imageFile ? $this->getParameter('cards_directory').'/'.$newFilename : null,
                'is_new' => false // Important : marquer comme modification
            ]);

            return $this->redirectToRoute('app_card_check');
        }

        return $this->render('card/edit.html.twig', [
            'card' => $card,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_card_show', methods: ['GET'])]
    public function show(Card $card): Response
    {
        return $this->render('card/show.html.twig', [
            'card' => $card,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_card_delete', methods: ['POST'])]
    public function delete(Request $request, Card $card, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$card->getId(), $request->request->get('_token'))) {
            // Supprime l'image si elle existe
            if ($card->getImageFilename()) {
                $imagePath = $this->getParameter('cards_directory').'/'.$card->getImageFilename();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($card);
            $entityManager->flush();
            
            $this->addFlash('success', 'La carte a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_card_index');
    }
}
