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
use Psr\Log\LoggerInterface;

#[Route('/player')]
class PlayerController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private ClubRepository $clubRepository,
        private PaysRepository $paysRepository
    ) {
        // Créer le dossier uploads s'il n'existe pas
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/player';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
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
        $session = $request->getSession();
        $player = new Player();

        // Si on revient de la page de vérification pour modifier
        if ($session->has('player_temp_data')) {
            $tempData = $session->get('player_temp_data');
            $player->setFirstName($tempData['firstName']);
            $player->setLastName($tempData['lastName']);
            $player->setBirthDate(new \DateTime($tempData['birthDate']));
            $player->setNationality($this->paysRepository->find($tempData['nationality']));
            $player->setPosition($tempData['position']);
            $player->setJerseyNumber($tempData['jerseyNumber']);
            $player->setClub($this->clubRepository->find($tempData['club']));
            $player->setWorldCups($tempData['worldCups']);
            
            // Supprimer les données temporaires
            $session->remove('player_temp_data');
        }

        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Stocker le fichier photo temporairement en session
            $photoFile = $form->get('photoFile')->getData();
            $photoData = null;
            if ($photoFile) {
                $photoData = [
                    'name' => $photoFile->getClientOriginalName(),
                    'tmp_name' => $photoFile->getPathname(),
                    'type' => $photoFile->getMimeType(),
                ];
            }

            // Stocker les données du joueur en session pour la vérification
            $session->set('player_data', [
                'player' => $player,
                'photo_data' => $photoData,
                'club_id' => $player->getClub()->getId(),
                'nationality_id' => $player->getNationality()->getId()
            ]);

            return $this->redirectToRoute('app_player_check');
        }

        return $this->render('player/new.html.twig', [
            'player' => $player,
            'form' => $form,
        ]);
    }

    #[Route('/check', name: 'app_player_check', methods: ['GET', 'POST'])]
    public function check(Request $request, SluggerInterface $slugger): Response
    {
        $session = $request->getSession();
        $playerData = $session->get('player_data');

        if (!$playerData) {
            return $this->redirectToRoute('app_player_new');
        }

        $player = $playerData['player'];
        $photoData = $playerData['photo_data'];
        $tmpPhotoFilename = null;
        
        // Si une photo a été uploadée, la stocker temporairement
        if ($photoData) {
            $originalFilename = pathinfo($photoData['name'], PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $extension = $photoData['type'] === 'image/png' ? 'png' : 'jpg';
            $tmpPhotoFilename = 'tmp-'.$safeFilename.'-'.uniqid().'.'.$extension;

            try {
                $uploadDir = $this->getParameter('players_directory').'/tmp';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Créer un fichier temporaire pour la prévisualisation
                $tmpFile = new \Symfony\Component\HttpFoundation\File\UploadedFile(
                    $photoData['tmp_name'],
                    $photoData['name'],
                    $photoData['type'],
                    null,
                    true
                );
                $tmpFile->move($uploadDir, $tmpPhotoFilename);
                
                // Mettre à jour les données de session avec le nom du fichier temporaire
                $photoData['preview_filename'] = $tmpPhotoFilename;
                $session->set('player_data', [
                    'player' => $player,
                    'photo_data' => $photoData,
                    'club_id' => $playerData['club_id'],
                    'nationality_id' => $playerData['nationality_id']
                ]);
            } catch (\Exception $e) {
                $this->logger->error('Error creating preview: {error}', [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Récupérer les entités Club et Pays depuis la base de données
        $club = $this->clubRepository->find($playerData['club_id']);
        $nationality = $this->paysRepository->find($playerData['nationality_id']);
        
        if (!$club || !$nationality) {
            $this->addFlash('error', 'Club ou pays invalide');
            return $this->redirectToRoute('app_player_new');
        }
        
        $player->setClub($club);
        $player->setNationality($nationality);

        if ($request->isMethod('POST')) {
            if ($request->request->get('action') === 'confirm') {
                // Si une photo a été uploadée, la déplacer du dossier temporaire vers le dossier final
                if ($photoData && isset($photoData['preview_filename'])) {
                    try {
                        $tmpPath = $this->getParameter('players_directory').'/tmp/'.$photoData['preview_filename'];
                        $finalFilename = str_replace('tmp-', '', $photoData['preview_filename']);
                        $finalPath = $this->getParameter('players_directory').'/'.$finalFilename;
                        
                        if (rename($tmpPath, $finalPath)) {
                            $player->setPhotoFilename($finalFilename);
                        }
                    } catch (\Exception $e) {
                        $this->logger->error('Error moving photo: {error}', [
                            'error' => $e->getMessage()
                        ]);
                        $this->addFlash('error', 'Erreur lors du déplacement de la photo');
                    }
                }

                $this->entityManager->persist($player);
                $this->entityManager->flush();

                // Nettoyer le dossier temporaire
                $this->cleanupTempFiles();

                $session->remove('player_data');
                return $this->redirectToRoute('app_player_index');
            } elseif ($request->request->get('action') === 'modify') {
                // Stocker les données temporairement pour les réutiliser dans le formulaire
                $session->set('player_temp_data', [
                    'firstName' => $player->getFirstName(),
                    'lastName' => $player->getLastName(),
                    'birthDate' => $player->getBirthDate()->format('Y-m-d'),
                    'nationality' => $player->getNationality()->getId(),
                    'position' => $player->getPosition(),
                    'jerseyNumber' => $player->getJerseyNumber(),
                    'club' => $player->getClub()->getId(),
                    'worldCups' => $player->getWorldCups()
                ]);
                
                // Nettoyer le dossier temporaire
                $this->cleanupTempFiles();
                
                $session->remove('player_data');
                return $this->redirectToRoute('app_player_new');
            }
        }

        return $this->render('player/check.html.twig', [
            'player' => $player,
            'has_photo' => $photoData !== null,
            'photo_preview' => $photoData['preview_filename'] ?? null
        ]);
    }

    private function cleanupTempFiles(): void
    {
        $tmpDir = $this->getParameter('players_directory').'/tmp';
        if (file_exists($tmpDir)) {
            $files = glob($tmpDir.'/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
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
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $uploadDir = $this->getParameter('players_directory');
                    $this->logger->info('Uploading new photo to: {path}', [
                        'path' => $uploadDir.'/'.$newFilename
                    ]);

                    $photoFile->move($uploadDir, $newFilename);
                    
                    // Supprimer l'ancienne photo si elle existe
                    if ($player->getPhotoFilename()) {
                        $oldFile = $uploadDir.'/'.$player->getPhotoFilename();
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
                    
                    $player->setPhotoFilename($newFilename);
                } catch (\Exception $e) {
                    $this->logger->error('Error uploading photo: {error}', [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('app_player_index', [], Response::HTTP_SEE_OTHER);
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
                $uploadDir = $this->getParameter('players_directory');
                $oldFile = $uploadDir.'/'.$player->getPhotoFilename();
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            
            $this->entityManager->remove($player);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_player_index', [], Response::HTTP_SEE_OTHER);
    }
}
