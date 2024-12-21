<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241221212136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE career_history (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, player_id INTEGER NOT NULL, club VARCHAR(255) NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, achievements CLOB DEFAULT NULL, CONSTRAINT FK_C4DB9FC799E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C4DB9FC799E6F5DF ON career_history (player_id)');
        $this->addSql('ALTER TABLE player ADD COLUMN biography CLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE player ADD COLUMN notable_actions CLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE career_history');
        $this->addSql('CREATE TEMPORARY TABLE __temp__player AS SELECT id, first_name, last_name, birth_date, position, jersey_number, current_club, nationality, world_cups, champions_league, europe_league, national_championship, national_cup, photo_filename FROM player');
        $this->addSql('DROP TABLE player');
        $this->addSql('CREATE TABLE player (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, birth_date DATE NOT NULL, position VARCHAR(50) NOT NULL, jersey_number INTEGER NOT NULL, current_club VARCHAR(255) NOT NULL, nationality VARCHAR(100) NOT NULL, world_cups INTEGER NOT NULL, champions_league INTEGER NOT NULL, europe_league INTEGER NOT NULL, national_championship INTEGER NOT NULL, national_cup INTEGER NOT NULL, photo_filename VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO player (id, first_name, last_name, birth_date, position, jersey_number, current_club, nationality, world_cups, champions_league, europe_league, national_championship, national_cup, photo_filename) SELECT id, first_name, last_name, birth_date, position, jersey_number, current_club, nationality, world_cups, champions_league, europe_league, national_championship, national_cup, photo_filename FROM __temp__player');
        $this->addSql('DROP TABLE __temp__player');
    }
}
