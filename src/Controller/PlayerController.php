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
            $player->setNationality($tempData['nationality']);
            $player->setPosition($tempData['position']);
            $player->setJerseyNumber($tempData['jerseyNumber']);
            $player->setCurrentClub($tempData['currentClub']);
            $player->setWorldCups($tempData['worldCups']);
            $player->setChampionsLeague($tempData['championsLeague']);
            $player->setEuropeLeague($tempData['europeLeague']);
            $player->setNationalChampionship($tempData['nationalChampionship']);
            $player->setNationalCup($tempData['nationalCup']);
            
            // Supprimer les données temporaires
            $session->remove('player_temp_data');
        }

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

            // Stocker les données du joueur en session pour la vérification
            $session->set('player_data', [
                'player' => $player,
                'photo_filename' => $player->getPhotoFilename()
            ]);

            return $this->redirectToRoute('app_player_check');
        }

        return $this->render('player/new.html.twig', [
            'player' => $player,
            'form' => $form,
        ]);
    }

    #[Route('/check', name: 'app_player_check', methods: ['GET', 'POST'])]
    public function check(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        $playerData = $session->get('player_data');

        if (!$playerData) {
            return $this->redirectToRoute('app_player_new');
        }

        $player = $playerData['player'];

        if ($request->isMethod('POST')) {
            if ($request->request->get('action') === 'confirm') {
                $entityManager->persist($player);
                $entityManager->flush();

                $session->remove('player_data');
                return $this->redirectToRoute('app_player_index');
            } elseif ($request->request->get('action') === 'modify') {
                // Stocker les données temporairement pour les réutiliser dans le formulaire
                $session->set('player_temp_data', [
                    'firstName' => $player->getFirstName(),
                    'lastName' => $player->getLastName(),
                    'birthDate' => $player->getBirthDate()->format('Y-m-d'),
                    'nationality' => $player->getNationality(),
                    'position' => $player->getPosition(),
                    'jerseyNumber' => $player->getJerseyNumber(),
                    'currentClub' => $player->getCurrentClub(),
                    'worldCups' => $player->getWorldCups(),
                    'championsLeague' => $player->getChampionsLeague(),
                    'europeLeague' => $player->getEuropeLeague(),
                    'nationalChampionship' => $player->getNationalChampionship(),
                    'nationalCup' => $player->getNationalCup()
                ]);
                
                $session->remove('player_data');
                return $this->redirectToRoute('app_player_new');
            }
        }

        return $this->render('player/check.html.twig', [
            'player' => $player,
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

            $this->addFlash('success', 'Le joueur a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_player_index', [], Response::HTTP_SEE_OTHER);
    }
}
