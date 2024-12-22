<?php

namespace App\Controller;

use App\Entity\Pays;
use App\Form\PaysType;
use App\Repository\PaysRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/pays')]
class PaysController extends AbstractController
{
    private string $uploadDir;

    public function __construct(string $projectDir)
    {
        $this->uploadDir = $projectDir . '/public/uploads/drapeaux';
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    #[Route('/', name: 'app_pays_index', methods: ['GET'])]
    public function index(PaysRepository $paysRepository): Response
    {
        return $this->render('pays/index.html.twig', [
            'pays' => $paysRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_pays_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $pays = new Pays();
        $form = $this->createForm(PaysType::class, $pays);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $drapeauFile = $form->get('drapeau')->getData();
            if ($drapeauFile) {
                $originalFilename = pathinfo($drapeauFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$drapeauFile->guessExtension();

                try {
                    $drapeauFile->move($this->uploadDir, $newFilename);
                    $pays->setDrapeau($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du drapeau');
                    return $this->redirectToRoute('app_pays_new');
                }
            }

            $entityManager->persist($pays);
            $entityManager->flush();

            return $this->redirectToRoute('app_pays_index');
        }

        return $this->render('pays/new.html.twig', [
            'pays' => $pays,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pays_show', methods: ['GET'])]
    public function show(Pays $pays): Response
    {
        return $this->render('pays/show.html.twig', [
            'pays' => $pays,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pays_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pays $pays, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(PaysType::class, $pays);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $drapeauFile = $form->get('drapeau')->getData();
            if ($drapeauFile) {
                $originalFilename = pathinfo($drapeauFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$drapeauFile->guessExtension();

                try {
                    // Supprimer l'ancien drapeau si il existe
                    if ($pays->getDrapeau()) {
                        $oldFile = $this->uploadDir . '/' . $pays->getDrapeau();
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }

                    $drapeauFile->move($this->uploadDir, $newFilename);
                    $pays->setDrapeau($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du drapeau');
                    return $this->redirectToRoute('app_pays_edit', ['id' => $pays->getId()]);
                }
            }

            $entityManager->flush();
            return $this->redirectToRoute('app_pays_index');
        }

        return $this->render('pays/edit.html.twig', [
            'pays' => $pays,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pays_delete', methods: ['POST'])]
    public function delete(Request $request, Pays $pays, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pays->getId(), $request->request->get('_token'))) {
            // Supprimer le drapeau si il existe
            if ($pays->getDrapeau()) {
                $drapeauFile = $this->uploadDir . '/' . $pays->getDrapeau();
                if (file_exists($drapeauFile)) {
                    unlink($drapeauFile);
                }
            }

            $entityManager->remove($pays);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_pays_index');
    }
}
