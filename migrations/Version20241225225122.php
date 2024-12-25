<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241225225122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, player_id INTEGER NOT NULL, club_id INTEGER DEFAULT NULL, profil_id INTEGER NOT NULL, visible BOOLEAN DEFAULT 0 NOT NULL, summary CLOB DEFAULT NULL, notable_action CLOB DEFAULT NULL, number INTEGER NOT NULL, position VARCHAR(255) NOT NULL, image_filename VARCHAR(255) DEFAULT NULL, start_season INTEGER NOT NULL, end_season INTEGER DEFAULT NULL, obtenu BOOLEAN DEFAULT 0, CONSTRAINT FK_161498D399E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_161498D361190A32 FOREIGN KEY (club_id) REFERENCES club (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_161498D3275ED078 FOREIGN KEY (profil_id) REFERENCES profil (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_161498D399E6F5DF ON card (player_id)');
        $this->addSql('CREATE INDEX IDX_161498D361190A32 ON card (club_id)');
        $this->addSql('CREATE INDEX IDX_161498D3275ED078 ON card (profil_id)');
        $this->addSql('CREATE TABLE club (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, pays_id INTEGER NOT NULL, nom VARCHAR(255) NOT NULL, histoire CLOB DEFAULT NULL, blason VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_B8EE3872A6E44244 FOREIGN KEY (pays_id) REFERENCES pays (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B8EE3872A6E44244 ON club (pays_id)');
        $this->addSql('CREATE TABLE pays (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, histoire CLOB DEFAULT NULL, drapeau VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE player (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, club_id INTEGER NOT NULL, nationality_id INTEGER NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, birth_date DATE NOT NULL, position VARCHAR(50) NOT NULL, jersey_number INTEGER NOT NULL, world_cups INTEGER NOT NULL, champions_league INTEGER NOT NULL, europe_league INTEGER NOT NULL, national_championship INTEGER NOT NULL, national_cup INTEGER NOT NULL, photo_filename VARCHAR(255) DEFAULT NULL, summary CLOB DEFAULT NULL, CONSTRAINT FK_98197A6561190A32 FOREIGN KEY (club_id) REFERENCES club (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_98197A651C9DA55 FOREIGN KEY (nationality_id) REFERENCES pays (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_98197A6561190A32 ON player (club_id)');
        $this->addSql('CREATE INDEX IDX_98197A651C9DA55 ON player (nationality_id)');
        $this->addSql('CREATE TABLE profil (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, pseudo VARCHAR(25) NOT NULL, role INTEGER NOT NULL, password VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E6D6B29786CC499D ON profil (pseudo)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , available_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , delivered_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE club');
        $this->addSql('DROP TABLE pays');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE profil');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
