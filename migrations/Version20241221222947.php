<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241221222947 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, player_id INTEGER NOT NULL, summary CLOB DEFAULT NULL, notable_action CLOB DEFAULT NULL, number INTEGER NOT NULL, position VARCHAR(255) NOT NULL, image_filename VARCHAR(255) DEFAULT NULL, start_season INTEGER NOT NULL, end_season INTEGER DEFAULT NULL, CONSTRAINT FK_161498D399E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_161498D399E6F5DF ON card (player_id)');
        $this->addSql('ALTER TABLE player ADD COLUMN summary CLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE card');
        $this->addSql('CREATE TEMPORARY TABLE __temp__player AS SELECT id, first_name, last_name, birth_date, position, jersey_number, current_club, nationality, world_cups, champions_league, europe_league, national_championship, national_cup, photo_filename FROM player');
        $this->addSql('DROP TABLE player');
        $this->addSql('CREATE TABLE player (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, birth_date DATE NOT NULL, position VARCHAR(50) NOT NULL, jersey_number INTEGER NOT NULL, current_club VARCHAR(255) NOT NULL, nationality VARCHAR(100) NOT NULL, world_cups INTEGER NOT NULL, champions_league INTEGER NOT NULL, europe_league INTEGER NOT NULL, national_championship INTEGER NOT NULL, national_cup INTEGER NOT NULL, photo_filename VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO player (id, first_name, last_name, birth_date, position, jersey_number, current_club, nationality, world_cups, champions_league, europe_league, national_championship, national_cup, photo_filename) SELECT id, first_name, last_name, birth_date, position, jersey_number, current_club, nationality, world_cups, champions_league, europe_league, national_championship, national_cup, photo_filename FROM __temp__player');
        $this->addSql('DROP TABLE __temp__player');
    }
}
