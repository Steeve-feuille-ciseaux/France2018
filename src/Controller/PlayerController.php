<?php

namespace App\Controller;

use App\Entity\Player;
use App\Form\PlayerType;
use App\Repository\PlayerRepository;
use App\Repository\ClubRepository;
use App\Repository\PaysRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/player')]
class PlayerController extends AbstractController
{
    private string $uploadDir;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClubRepository $clubRepository,
        private PaysRepository $paysRepository,
        string $projectDir
    ) {
        $this->uploadDir = $projectDir . '/public/uploads/player';
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    #[Route('/', name: 'app_player_index', methods: ['GET'])]
    public function index(PlayerRepository $playerRepository): Response
    {
        $players = $playerRepository->findBy([], ['position' => 'ASC']);
        
        // Ordre personnalisé des positions
        $positionOrder = [
            'Attaquant' => 1,
            'Milieu' => 2,
            'Défenseur' => 3,
            'Gardien' => 4
        ];
        
        // Organiser les joueurs par position
        $playersByPosition = [];
        foreach ($players as $player) {
            $position = $player->getPosition();
            if (!isset($playersByPosition[$position])) {
                $playersByPosition[$position] = [];
            }
            $playersByPosition[$position][] = $player;
        }
        
        // Trier le tableau des positions selon l'ordre personnalisé
        uksort($playersByPosition, function($a, $b) use ($positionOrder) {
            return ($positionOrder[$a] ?? 999) - ($positionOrder[$b] ?? 999);
        });
        
        return $this->render('player/index.html.twig', [
            'players_by_position' => $playersByPosition,
        ]);
    }

    #[Route('/new', name: 'app_player_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $player = new Player();
        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de la photo
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move($this->uploadDir, $newFilename);
                    $player->setPhotoFilename($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la photo');
                    return $this->redirectToRoute('app_player_new');
                }
            }

            $this->entityManager->persist($player);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_player_index');
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
    public function edit(Request $request, Player $player, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    // Supprimer l'ancienne photo si elle existe
                    if ($player->getPhotoFilename()) {
                        $oldFile = $this->uploadDir . '/' . $player->getPhotoFilename();
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }

                    $photoFile->move($this->uploadDir, $newFilename);
                    $player->setPhotoFilename($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la photo');
                    return $this->redirectToRoute('app_player_edit', ['id' => $player->getId()]);
                }
            }

            $this->entityManager->flush();
            return $this->redirectToRoute('app_player_index');
        }

        return $this->render('player/edit.html.twig', [
            'player' => $player,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_player_delete', methods: ['POST'])]
    public function delete(Request $request, Player $player): Response
    {
        if ($this->isCsrfTokenValid('delete'.$player->getId(), $request->request->get('_token'))) {
            // Supprimer la photo si elle existe
            if ($player->getPhotoFilename()) {
                $photoFile = $this->uploadDir . '/' . $player->getPhotoFilename();
                if (file_exists($photoFile)) {
                    unlink($photoFile);
                }
            }

            $this->entityManager->remove($player);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_player_index');
    }
}
