<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\Card;
use App\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin/users')]
class UsersController extends BaseController
{
    #[Route('/cards/non-valides', name: 'admin_non_validated_cards')]
    public function nonValidatedCards(CardRepository $cardRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $cards = $user->getRole() >= 3 
            ? $cardRepository->findBy(['visible' => false])
            : $cardRepository->findBy(['visible' => false, 'profil' => $user]);

        return $this->render('admin/users/cards/non-valides.html.twig', [
            'cards' => $cards,
        ]);
    }

    #[Route('/cards/{id}/validate', name: 'admin_validate_card', methods: ['POST'])]
    public function validateCard(
        Request $request,
        Card $card,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user || $user->getRole() < 3) {
            throw new AccessDeniedException('Vous devez avoir le rôle administrateur pour valider une carte.');
        }

        if ($this->isCsrfTokenValid('validate'.$card->getId(), $request->request->get('_token'))) {
            $card->setVisible(true);
            $entityManager->flush();

            $this->addFlash('success', 'La carte a été validée avec succès.');
        }

        return $this->redirectToRoute('admin_non_validated_cards');
    }
}
