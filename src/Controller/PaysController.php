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

        if ($form->isSubmitted() && !$form->isValid()) {
            return $this->render('pays/check.html.twig', [
                'pays' => $pays,
                'form' => $form,
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $drapeauFile = $form->get('drapeau')->getData();
            if ($drapeauFile) {
                $originalFilename = pathinfo($drapeauFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$drapeauFile->guessExtension();

                try {
                    $drapeauFile->move(
                        $this->getParameter('drapeaux_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $pays->setDrapeau($newFilename);
            }

            $entityManager->persist($pays);
            $entityManager->flush();

            return $this->redirectToRoute('app_pays_index', [], Response::HTTP_SEE_OTHER);
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

        if ($form->isSubmitted() && !$form->isValid()) {
            return $this->render('pays/check.html.twig', [
                'pays' => $pays,
                'form' => $form,
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $drapeauFile = $form->get('drapeau')->getData();
            if ($drapeauFile) {
                $originalFilename = pathinfo($drapeauFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$drapeauFile->guessExtension();

                try {
                    $drapeauFile->move(
                        $this->getParameter('drapeaux_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $pays->setDrapeau($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_pays_index', [], Response::HTTP_SEE_OTHER);
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
            $entityManager->remove($pays);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_pays_index', [], Response::HTTP_SEE_OTHER);
    }
}
