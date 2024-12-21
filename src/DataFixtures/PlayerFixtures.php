<?php

namespace App\DataFixtures;

use App\Entity\Player;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PlayerFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Gardiens
        $this->createPlayer($manager, 'Hugo', 'Lloris', new \DateTime('1986-12-26'), 'Gardien', 1, 'Tottenham', 'France', 0, 0);
        
        // Défenseurs
        $this->createPlayer($manager, 'Raphaël', 'Varane', new \DateTime('1993-04-25'), 'Défenseur', 4, 'Real Madrid', 'France', 0, 0);
        $this->createPlayer($manager, 'Samuel', 'Umtiti', new \DateTime('1993-11-14'), 'Défenseur', 5, 'Barcelona', 'France', 0, 0);
        $this->createPlayer($manager, 'Benjamin', 'Pavard', new \DateTime('1996-03-28'), 'Défenseur', 2, 'Stuttgart', 'France', 1, 0);
        $this->createPlayer($manager, 'Lucas', 'Hernandez', new \DateTime('1996-02-14'), 'Défenseur', 21, 'Atletico Madrid', 'France', 0, 0);
        
        // Milieux
        $this->createPlayer($manager, 'Paul', 'Pogba', new \DateTime('1993-03-15'), 'Milieu', 6, 'Manchester United', 'France', 10, 15);
        $this->createPlayer($manager, 'NGolo', 'Kanté', new \DateTime('1991-03-29'), 'Milieu', 13, 'Chelsea', 'France', 4, 8);
        $this->createPlayer($manager, 'Blaise', 'Matuidi', new \DateTime('1987-04-09'), 'Milieu', 14, 'Juventus', 'France', 8, 12);
        
        // Attaquants
        $this->createPlayer($manager, 'Antoine', 'Griezmann', new \DateTime('1991-03-21'), 'Attaquant', 7, 'Atletico Madrid', 'France', 25, 20);
        $this->createPlayer($manager, 'Olivier', 'Giroud', new \DateTime('1986-09-30'), 'Attaquant', 9, 'Chelsea', 'France', 35, 15);

        $manager->flush();
    }

    private function createPlayer(
        ObjectManager $manager,
        string $firstName,
        string $lastName,
        \DateTime $birthDate,
        string $position,
        int $jerseyNumber,
        string $currentClub,
        string $nationality,
        int $goals,
        int $assists
    ): void {
        $player = new Player();
        $player->setFirstName($firstName);
        $player->setLastName($lastName);
        $player->setBirthDate($birthDate);
        $player->setPosition($position);
        $player->setJerseyNumber($jerseyNumber);
        $player->setCurrentClub($currentClub);
        $player->setNationality($nationality);
        $player->setGoals($goals);
        $player->setAssists($assists);
        
        $manager->persist($player);
    }
}
