<?php

namespace App\Command;

use App\Entity\Profil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creates the admin user'
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Créer l'administrateur
        $admin = new Profil();
        $admin->setPseudo('admin1');
        $admin->setRole(4);
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin1');
        $admin->setPassword($hashedPassword);
        $this->entityManager->persist($admin);

        // Créer test1 (role 1)
        $test1 = new Profil();
        $test1->setPseudo('test1');
        $test1->setRole(1);
        $hashedPassword = $this->passwordHasher->hashPassword($test1, 'test1');
        $test1->setPassword($hashedPassword);
        $this->entityManager->persist($test1);

        // Créer test2 (role 2)
        $test2 = new Profil();
        $test2->setPseudo('test2');
        $test2->setRole(2);
        $hashedPassword = $this->passwordHasher->hashPassword($test2, 'test2');
        $test2->setPassword($hashedPassword);
        $this->entityManager->persist($test2);

        // Créer test3 (role 3)
        $test3 = new Profil();
        $test3->setPseudo('test3');
        $test3->setRole(3);
        $hashedPassword = $this->passwordHasher->hashPassword($test3, 'test3');
        $test3->setPassword($hashedPassword);
        $this->entityManager->persist($test3);

        $this->entityManager->flush();

        $output->writeln('Utilisateurs créés avec succès :');
        $output->writeln('- admin1 (role 4)');
        $output->writeln('- test1 (role 1)');
        $output->writeln('- test2 (role 2)');
        $output->writeln('- test3 (role 3)');

        return Command::SUCCESS;
    }
}
