<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241222174428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__player AS SELECT id, first_name, last_name, birth_date, position, jersey_number, world_cups, champions_league, europe_league, national_championship, national_cup, photo_filename, summary FROM player');
        $this->addSql('DROP TABLE player');
        $this->addSql('CREATE TABLE player (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, club_id INTEGER NOT NULL, nationality_id INTEGER NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, birth_date DATE NOT NULL, position VARCHAR(50) NOT NULL, jersey_number INTEGER NOT NULL, world_cups INTEGER NOT NULL, champions_league INTEGER NOT NULL, europe_league INTEGER NOT NULL, national_championship INTEGER NOT NULL, national_cup INTEGER NOT NULL, photo_filename VARCHAR(255) DEFAULT NULL, summary CLOB DEFAULT NULL, CONSTRAINT FK_98197A6561190A32 FOREIGN KEY (club_id) REFERENCES club (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_98197A651C9DA55 FOREIGN KEY (nationality_id) REFERENCES pays (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO player (id, first_name, last_name, birth_date, position, jersey_number, world_cups, champions_league, europe_league, national_championship, national_cup, photo_filename, summary) SELECT id, first_name, last_name, birth_date, position, jersey_number, world_cups, champions_league, europe_league, national_championship, national_cup, photo_filename, summary FROM __temp__player');
        $this->addSql('DROP TABLE __temp__player');
        $this->addSql('CREATE INDEX IDX_98197A6561190A32 ON player (club_id)');
        $this->addSql('CREATE INDEX IDX_98197A651C9DA55 ON player (nationality_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__player AS SELECT id, first_name, last_name, birth_date, position, jersey_number, world_cups, champions_league, europe_league, national_championship, national_cup, photo_filename, summary FROM player');
        $this->addSql('DROP TABLE player');
        $this->addSql('CREATE TABLE player (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, birth_date DATE NOT NULL, position VARCHAR(50) NOT NULL, jersey_number INTEGER NOT NULL, world_cups INTEGER NOT NULL, champions_league INTEGER NOT NULL, europe_league INTEGER NOT NULL, national_championship INTEGER NOT NULL, national_cup INTEGER NOT NULL, photo_filename VARCHAR(255) DEFAULT NULL, summary CLOB DEFAULT NULL, current_club VARCHAR(255) NOT NULL, nationality VARCHAR(100) NOT NULL)');
        $this->addSql('INSERT INTO player (id, first_name, last_name, birth_date, position, jersey_number, world_cups, champions_league, europe_league, national_championship, national_cup, photo_filename, summary) SELECT id, first_name, last_name, birth_date, position, jersey_number, world_cups, champions_league, europe_league, national_championship, national_cup, photo_filename, summary FROM __temp__player');
        $this->addSql('DROP TABLE __temp__player');
    }
}
