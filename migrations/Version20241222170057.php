<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241222170057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE club (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, pays_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, histoire CLOB DEFAULT NULL, blason VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_B8EE3872A6E44244 FOREIGN KEY (pays_id) REFERENCES pays (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B8EE3872A6E44244 ON club (pays_id)');
        $this->addSql('CREATE TABLE pays (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, histoire CLOB DEFAULT NULL, drapeau VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__card AS SELECT id, player_id, summary, notable_action, number, position, image_filename, start_season, end_season, club, obtenu FROM card');
        $this->addSql('DROP TABLE card');
        $this->addSql('CREATE TABLE card (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, player_id INTEGER NOT NULL, summary CLOB DEFAULT NULL, notable_action CLOB DEFAULT NULL, number INTEGER NOT NULL, position VARCHAR(255) NOT NULL, image_filename VARCHAR(255) DEFAULT NULL, start_season INTEGER NOT NULL, end_season INTEGER DEFAULT NULL, club VARCHAR(255) NOT NULL, obtenu BOOLEAN DEFAULT 0, CONSTRAINT FK_161498D399E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO card (id, player_id, summary, notable_action, number, position, image_filename, start_season, end_season, club, obtenu) SELECT id, player_id, summary, notable_action, number, position, image_filename, start_season, end_season, club, obtenu FROM __temp__card');
        $this->addSql('DROP TABLE __temp__card');
        $this->addSql('CREATE INDEX IDX_161498D399E6F5DF ON card (player_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE club');
        $this->addSql('DROP TABLE pays');
        $this->addSql('ALTER TABLE card ADD COLUMN championnat VARCHAR(255) DEFAULT NULL');
    }
}
