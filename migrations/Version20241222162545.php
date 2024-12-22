<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241222162545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__card AS SELECT id, player_id, summary, notable_action, number, position, image_filename, start_season, end_season, club FROM card');
        $this->addSql('DROP TABLE card');
        $this->addSql('CREATE TABLE card (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, player_id INTEGER NOT NULL, summary CLOB DEFAULT NULL, notable_action CLOB DEFAULT NULL, number INTEGER NOT NULL, position VARCHAR(255) NOT NULL, image_filename VARCHAR(255) DEFAULT NULL, start_season INTEGER NOT NULL, end_season INTEGER DEFAULT NULL, club VARCHAR(255) NOT NULL, obtenu BOOLEAN DEFAULT 0, CONSTRAINT FK_161498D399E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO card (id, player_id, summary, notable_action, number, position, image_filename, start_season, end_season, club) SELECT id, player_id, summary, notable_action, number, position, image_filename, start_season, end_season, club FROM __temp__card');
        $this->addSql('DROP TABLE __temp__card');
        $this->addSql('CREATE INDEX IDX_161498D399E6F5DF ON card (player_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__card AS SELECT id, player_id, summary, notable_action, number, position, image_filename, start_season, end_season, club FROM card');
        $this->addSql('DROP TABLE card');
        $this->addSql('CREATE TABLE card (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, player_id INTEGER NOT NULL, summary VARCHAR(255) DEFAULT NULL, notable_action VARCHAR(255) DEFAULT NULL, number INTEGER NOT NULL, position VARCHAR(255) NOT NULL, image_filename VARCHAR(255) DEFAULT NULL, start_season INTEGER NOT NULL, end_season INTEGER DEFAULT NULL, club VARCHAR(255) DEFAULT \'"Non spécifié"\' NOT NULL, CONSTRAINT FK_161498D399E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO card (id, player_id, summary, notable_action, number, position, image_filename, start_season, end_season, club) SELECT id, player_id, summary, notable_action, number, position, image_filename, start_season, end_season, club FROM __temp__card');
        $this->addSql('DROP TABLE __temp__card');
        $this->addSql('CREATE INDEX IDX_161498D399E6F5DF ON card (player_id)');
    }
}
