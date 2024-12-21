<?php

namespace App\Controller;

use App\Entity\Player;
use App\Form\PlayerType;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Psr\Log\LoggerInterface;

#[Route('/player')]
class PlayerController extends AbstractController
{
    public function __construct(private LoggerInterface $logger)
    {
        // Créer le dossier uploads s'il n'existe pas
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/players';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
    }

    #[Route('/', name: 'app_player_index', methods: ['GET'])]
    public function index(PlayerRepository $playerRepository): Response
    {
        $players = $playerRepository->findBy([], ['position' => 'ASC']);
        
        // Organiser les joueurs par position
        $playersByPosition = [];
        foreach ($players as $player) {
            $position = $player->getPosition();
            if (!isset($playersByPosition[$position])) {
                $playersByPosition[$position] = [];
            }
            $playersByPosition[$position][] = $player;
            
            // Log pour vérifier les photos
            if ($player->getPhotoFilename()) {
                $photoPath = $this->getParameter('players_directory').'/'.$player->getPhotoFilename();
                $this->logger->info('Photo path for player {player}: {path} (exists: {exists})', [
                    'player' => $player->getFirstName() . ' ' . $player->getLastName(),
                    'path' => $photoPath,
                    'exists' => file_exists($photoPath) ? 'yes' : 'no'
                ]);
            }
        }
        
        return $this->render('player/index.html.twig', [
            'players_by_position' => $playersByPosition,
        ]);
    }

    #[Route('/new', name: 'app_player_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $player = new Player();
        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $uploadDir = $this->getParameter('players_directory');
                    $this->logger->info('Uploading new photo to: {path}', [
                        'path' => $uploadDir.'/'.$newFilename
                    ]);

                    $photoFile->move($uploadDir, $newFilename);
                    $player->setPhotoFilename($newFilename);
                } catch (\Exception $e) {
                    $this->logger->error('Error uploading photo: {error}', [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $entityManager->persist($player);
            $entityManager->flush();

            return $this->redirectToRoute('app_player_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('player/new.html.twig', [
            'player' => $player,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_player_show', methods: ['GET'])]
    public function show(Player $player): Response
    {
        return $this->render('player/show.html.twig', [
            'player' => $player,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_player_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Player $player, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $uploadDir = $this->getParameter('players_directory');
                    $this->logger->info('Uploading edited photo to: {path}', [
                        'path' => $uploadDir.'/'.$newFilename
                    ]);

                    $photoFile->move($uploadDir, $newFilename);
                    
                    // Supprimer l'ancienne photo si elle existe
                    if ($player->getPhotoFilename()) {
                        $oldPhotoPath = $uploadDir.'/'.$player->getPhotoFilename();
                        if (file_exists($oldPhotoPath)) {
                            unlink($oldPhotoPath);
                            $this->logger->info('Deleted old photo: {path}', [
                                'path' => $oldPhotoPath
                            ]);
                        }
                    }
                    
                    $player->setPhotoFilename($newFilename);
                } catch (\Exception $e) {
                    $this->logger->error('Error uploading photo: {error}', [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_player_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('player/edit.html.twig', [
            'player' => $player,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_player_delete', methods: ['POST'])]
    public function delete(Request $request, Player $player, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$player->getId(), $request->request->get('_token'))) {
            // Supprimer la photo si elle existe
            if ($player->getPhotoFilename()) {
                $photoPath = $this->getParameter('players_directory').'/'.$player->getPhotoFilename();
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                    $this->logger->info('Deleted photo during player deletion: {path}', [
                        'path' => $photoPath
                    ]);
                }
            }

            $entityManager->remove($player);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_player_index', [], Response::HTTP_SEE_OTHER);
    }
}
