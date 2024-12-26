<?php

namespace App\Controller;

use App\Repository\CardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends AbstractController
{
    private CardRepository $cardRepository;

    public function __construct(CardRepository $cardRepository)
    {
        $this->cardRepository = $cardRepository;
    }

    public function getPendingCardsCount(): int
    {
        if (!$this->getUser() || $this->getUser()->getRole() < 2) {
            return 0;
        }

        return $this->cardRepository->count(['visible' => false]);
    }
}
