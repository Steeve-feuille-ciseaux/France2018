<?php

namespace App\Controller;

use App\Entity\Profil;
use App\Entity\Card;
use App\Form\AdminUserType;
use App\Repository\ProfilRepository;
use App\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/users')]
#[IsGranted('ROLE_USER')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'app_admin_user_index', methods: ['GET'])]
    public function index(ProfilRepository $profilRepository): Response
    {
        if ($this->getUser()->getRole() !== 4) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        return $this->render('admin_user/index.html.twig', [
            'profils' => $profilRepository->findAll(),
        ]);
    }

    #[Route('/mon-profil', name: 'app_admin_user_my_profile', methods: ['GET'])]
    public function myProfile(): Response
    {
        return $this->render('admin_user/show.html.twig', [
            'profil' => $this->getUser(),
            'is_own_profile' => true,
        ]);
    }

    #[Route('/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->getUser()->getRole() !== 4) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        $profil = new Profil();
        $form = $this->createForm(AdminUserType::class, $profil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $profil->setPassword(
                $passwordHasher->hashPassword(
                    $profil,
                    $form->get('password')->getData()
                )
            );
            
            $entityManager->persist($profil);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_user/new.html.twig', [
            'profil' => $profil,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_show', methods: ['GET'])]
    public function show(Profil $profil): Response
    {
        if ($this->getUser()->getRole() !== 4) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        return $this->render('admin_user/show.html.twig', [
            'profil' => $profil,
            'is_own_profile' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Profil $profil, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->getUser()->getRole() !== 4) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        $form = $this->createForm(AdminUserType::class, $profil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('password')->getData()) {
                $profil->setPassword(
                    $passwordHasher->hashPassword(
                        $profil,
                        $form->get('password')->getData()
                    )
                );
            }
            
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_user/edit.html.twig', [
            'profil' => $profil,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, Profil $profil, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()->getRole() !== 4) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        if ($this->isCsrfTokenValid('delete'.$profil->getId(), $request->request->get('_token'))) {
            $entityManager->remove($profil);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/cards', name: 'app_admin_user_cards', methods: ['GET'])]
    public function userCards(Profil $profil, CardRepository $cardRepository): Response
    {
        if ($this->getUser()->getRole() !== 4) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        $cards = $cardRepository->findBy([
            'profil' => $profil,
            'visible' => false
        ]);

        return $this->render('admin_user/cards.html.twig', [
            'profil' => $profil,
            'cards' => $cards,
        ]);
    }

    #[Route('/card/{id}/validate', name: 'app_admin_user_validate_card', methods: ['GET', 'POST'])]
    public function validateCard(
        Card $card,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        if ($this->getUser()->getRole() !== 4) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        $card->setVisible(true);
        $entityManager->flush();

        $this->addFlash('success', 'La carte a été validée avec succès.');
        return $this->redirectToRoute('app_admin_user_cards', ['id' => $card->getProfil()->getId()]);
    }

    #[Route('/cards/non-valides', name: 'app_admin_user_non_valid_cards', methods: ['GET'])]
    public function nonValidCards(CardRepository $cardRepository): Response
    {
        if ($this->getUser()->getRole() !== 4) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        $cards = $cardRepository->findBy([
            'visible' => false
        ]);

        return $this->render('admin_user/non_valid_cards.html.twig', [
            'cards' => $cards,
        ]);
    }

    #[Route('/card/{id}/refuse', name: 'app_admin_user_refuse_card', methods: ['GET', 'POST'])]
    public function refuseCard(Card $card, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()->getRole() !== 4) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        if ($request->isMethod('POST')) {
            $entityManager->remove($card);
            $entityManager->flush();

            $this->addFlash('success', 'La carte a été refusée avec succès.');
            return $this->redirectToRoute('app_admin_user_non_valid_cards');
        }

        return $this->render('admin_user/refuse_card.html.twig', [
            'card' => $card,
        ]);
    }
}