<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241227102213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add createdBy field to Card entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__card AS SELECT id, player_id, club_id, profil_id, visible, summary, notable_action, number, position, image_filename, start_season, end_season, obtenu FROM card');
        $this->addSql('DROP TABLE card');
        $this->addSql('CREATE TABLE card (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, player_id INTEGER NOT NULL, club_id INTEGER DEFAULT NULL, profil_id INTEGER NOT NULL, created_by_id INTEGER NOT NULL, visible BOOLEAN DEFAULT 0 NOT NULL, summary CLOB DEFAULT NULL, notable_action CLOB DEFAULT NULL, number INTEGER NOT NULL, position VARCHAR(255) NOT NULL, image_filename VARCHAR(255) DEFAULT NULL, start_season INTEGER NOT NULL, end_season INTEGER DEFAULT NULL, obtenu BOOLEAN DEFAULT 0, CONSTRAINT FK_161498D399E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_161498D361190A32 FOREIGN KEY (club_id) REFERENCES club (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_161498D3275ED078 FOREIGN KEY (profil_id) REFERENCES profil (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_161498D3B03A8386 FOREIGN KEY (created_by_id) REFERENCES profil (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO card (id, player_id, club_id, profil_id, visible, summary, notable_action, number, position, image_filename, start_season, end_season, obtenu, created_by_id) SELECT id, player_id, club_id, profil_id, visible, summary, notable_action, number, position, image_filename, start_season, end_season, obtenu, profil_id FROM __temp__card');
        $this->addSql('DROP TABLE __temp__card');
        $this->addSql('CREATE INDEX IDX_161498D3275ED078 ON card (profil_id)');
        $this->addSql('CREATE INDEX IDX_161498D361190A32 ON card (club_id)');
        $this->addSql('CREATE INDEX IDX_161498D399E6F5DF ON card (player_id)');
        $this->addSql('CREATE INDEX IDX_161498D3B03A8386 ON card (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__card AS SELECT id, player_id, club_id, profil_id, visible, summary, notable_action, number, position, image_filename, start_season, end_season, obtenu FROM card');
        $this->addSql('DROP TABLE card');
        $this->addSql('CREATE TABLE card (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, player_id INTEGER NOT NULL, club_id INTEGER DEFAULT NULL, profil_id INTEGER NOT NULL, visible BOOLEAN DEFAULT 0 NOT NULL, summary CLOB DEFAULT NULL, notable_action CLOB DEFAULT NULL, number INTEGER NOT NULL, position VARCHAR(255) NOT NULL, image_filename VARCHAR(255) DEFAULT NULL, start_season INTEGER NOT NULL, end_season INTEGER DEFAULT NULL, obtenu BOOLEAN DEFAULT 0, CONSTRAINT FK_161498D399E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_161498D361190A32 FOREIGN KEY (club_id) REFERENCES club (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_161498D3275ED078 FOREIGN KEY (profil_id) REFERENCES profil (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO card (id, player_id, club_id, profil_id, visible, summary, notable_action, number, position, image_filename, start_season, end_season, obtenu) SELECT id, player_id, club_id, profil_id, visible, summary, notable_action, number, position, image_filename, start_season, end_season, obtenu FROM __temp__card');
        $this->addSql('DROP TABLE __temp__card');
        $this->addSql('CREATE INDEX IDX_161498D399E6F5DF ON card (player_id)');
        $this->addSql('CREATE INDEX IDX_161498D361190A32 ON card (club_id)');
        $this->addSql('CREATE INDEX IDX_161498D3275ED078 ON card (profil_id)');
    }
}
