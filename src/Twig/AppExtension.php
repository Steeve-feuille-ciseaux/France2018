<?php

namespace App\Twig;

use App\Repository\CardRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private CardRepository $cardRepository;

    public function __construct(CardRepository $cardRepository)
    {
        $this->cardRepository = $cardRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pending_cards_count', [$this, 'getPendingCardsCount']),
        ];
    }

    public function getPendingCardsCount(): int
    {
        return $this->cardRepository->count(['visible' => false]);
    }
}
