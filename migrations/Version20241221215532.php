<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241221215532 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__player AS SELECT id, first_name, last_name, birth_date, position, jersey_number, current_club, nationality, photo_filename, world_cups, champions_league, europe_league, national_championship, national_cup FROM player');
        $this->addSql('DROP TABLE player');
        $this->addSql('CREATE TABLE player (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, birth_date DATE NOT NULL, position VARCHAR(50) NOT NULL, jersey_number INTEGER NOT NULL, current_club VARCHAR(255) NOT NULL, nationality VARCHAR(100) NOT NULL, photo_filename VARCHAR(255) DEFAULT NULL, world_cups INTEGER NOT NULL, champions_league INTEGER NOT NULL, europe_league INTEGER NOT NULL, national_championship INTEGER NOT NULL, national_cup INTEGER NOT NULL)');
        $this->addSql('INSERT INTO player (id, first_name, last_name, birth_date, position, jersey_number, current_club, nationality, photo_filename, world_cups, champions_league, europe_league, national_championship, national_cup) SELECT id, first_name, last_name, birth_date, position, jersey_number, current_club, nationality, photo_filename, world_cups, champions_league, europe_league, national_championship, national_cup FROM __temp__player');
        $this->addSql('DROP TABLE __temp__player');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player ADD COLUMN biography CLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE player ADD COLUMN notable_actions CLOB DEFAULT NULL');
    }
}
