<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\Profil;
use App\Form\CardType;
use App\Repository\CardRepository;
use App\Repository\ClubRepository;
use App\Repository\PlayerRepository;
use App\Repository\ProfilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/card')]
class CardController extends BaseController
{
    #[Route('/', name: 'app_card_index', methods: ['GET'])]
    public function index(CardRepository $cardRepository): Response
    {
        return $this->render('card/index.html.twig', [
            'cards' => $cardRepository->findBy(['visible' => true]),
            'pending_cards_count' => $this->getPendingCardsCount()
        ]);
    }

    #[Route('/new', name: 'app_card_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        CardRepository $cardRepository,
        ClubRepository $clubRepository,
        ProfilRepository $profilRepository,
        SluggerInterface $slugger
    ): Response {
        $card = new Card();
        $user = $this->getUser();
        $profil = $entityManager->getReference(Profil::class, $user->getId());
        $card->setProfil($profil);
        $card->setVisible(false);
        
        $form = $this->createForm(CardType::class, $card);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
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
                    $card->setImageFilename($newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
            }

            $entityManager->persist($card);
            $entityManager->flush();

            $this->addFlash('info', 'Votre carte a été créée avec succès. Elle sera visible publiquement après validation par un modérateur.');
            return $this->redirectToRoute('app_card_index');
        }

        return $this->render('card/new.html.twig', [
            'card' => $card,
            'form' => $form,
            'pending_cards_count' => $this->getPendingCardsCount()
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

    #[Route('/confirm/{action}/{id}', name: 'app_card_confirm', methods: ['GET'])]
    public function confirm(
        Request $request,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        PlayerRepository $playerRepository,
        ClubRepository $clubRepository,
        CardRepository $cardRepository,
        string $action,
        ?int $id = null
    ): Response
    {
        $tempCard = $session->get('temp_card');
        if (!$tempCard) {
            return $this->redirectToRoute('app_card_index');
        }

        $card = $tempCard['card'];

        // Recharger les entités depuis la base de données
        if ($card->getPlayer()) {
            $card->setPlayer($playerRepository->find($card->getPlayer()->getId()));
        }
        if ($card->getClub()) {
            $card->setClub($clubRepository->find($card->getClub()->getId()));
        }

        if (!$tempCard['is_new']) {
            // Pour une modification, récupérer l'entité existante
            $existingCard = $cardRepository->find($card->getId());
            if (!$existingCard) {
                throw $this->createNotFoundException('Card not found');
            }

            // Mettre à jour les propriétés
            $existingCard->setPlayer($card->getPlayer());
            $existingCard->setClub($card->getClub());
            $existingCard->setPosition($card->getPosition());
            $existingCard->setNumber($card->getNumber());
            $existingCard->setStartSeason($card->getStartSeason());
            $existingCard->setEndSeason($card->getEndSeason());
            $existingCard->setSummary($card->getSummary());
            $existingCard->setNotableAction($card->getNotableAction());
            
            if ($card->getImageFilename() && $card->getImageFilename() !== $existingCard->getImageFilename()) {
                // Supprimer l'ancienne image
                if ($existingCard->getImageFilename()) {
                    $oldImagePath = $this->getParameter('cards_directory') . '/' . $existingCard->getImageFilename();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $existingCard->setImageFilename($card->getImageFilename());
            }
        } else {
            $entityManager->persist($card);
        }

        $entityManager->flush();
        $session->remove('temp_card');

        $this->addFlash('success', 'La carte a été ' . ($tempCard['is_new'] ? 'créée' : 'modifiée') . ' avec succès.');
        return $this->redirectToRoute('app_card_index');
    }

    #[Route('/{id}/edit', name: 'app_card_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Card $card, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        // Vérifier si l'utilisateur a le droit de modifier cette carte
        if ($user->getRole() < 3 && ($user->getId() !== $card->getProfil()->getId() || $card->isVisible())) {
            throw $this->createAccessDeniedException('Vous n\'avez pas le droit de modifier cette carte.');
        }

        $form = $this->createForm(CardType::class, $card);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_card_index');
        }

        return $this->render('card/edit.html.twig', [
            'card' => $card,
            'form' => $form,
            'pending_cards_count' => $this->getPendingCardsCount()
        ]);
    }

    #[Route('/{id}', name: 'app_card_show', methods: ['GET'])]
    public function show(Card $card): Response
    {
        return $this->render('card/show.html.twig', [
            'card' => $card,
            'pending_cards_count' => $this->getPendingCardsCount()
        ]);
    }

    #[Route('/{id}/delete', name: 'app_card_delete', methods: ['POST'])]
    public function delete(Request $request, Card $card, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur a le droit de supprimer cette carte
        if ($this->getUser()->getRole() < 3 && $this->getUser()->getId() !== $card->getProfil()->getId()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas le droit de supprimer cette carte.');
        }

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

    public function getPendingCardsCount(): int
    {
        return parent::getPendingCardsCount();
    }
}
