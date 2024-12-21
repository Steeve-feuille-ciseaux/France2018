<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241221231227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du champ club dans l\'entité Card';
    }

    public function up(Schema $schema): void
    {
        // Créer une table temporaire avec la nouvelle structure
        $this->addSql('CREATE TEMPORARY TABLE __temp__card AS SELECT id, player_id, summary, notable_action, number, position, image_filename, start_season, end_season FROM card');
        $this->addSql('DROP TABLE card');
        
        // Recréer la table avec le nouveau champ
        $this->addSql('CREATE TABLE card (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
            player_id INTEGER NOT NULL,
            summary VARCHAR(255) DEFAULT NULL,
            notable_action VARCHAR(255) DEFAULT NULL,
            number INTEGER NOT NULL,
            position VARCHAR(255) NOT NULL,
            image_filename VARCHAR(255) DEFAULT NULL,
            start_season INTEGER NOT NULL,
            end_season INTEGER DEFAULT NULL,
            club VARCHAR(255) NOT NULL DEFAULT "Non spécifié",
            CONSTRAINT FK_161498D399E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        
        // Restaurer les données
        $this->addSql('INSERT INTO card (id, player_id, summary, notable_action, number, position, image_filename, start_season, end_season, club) 
            SELECT id, player_id, summary, notable_action, number, position, image_filename, start_season, end_season, "Non spécifié" FROM __temp__card');
        
        // Supprimer la table temporaire
        $this->addSql('DROP TABLE __temp__card');
        
        // Recréer l'index
        $this->addSql('CREATE INDEX IDX_161498D399E6F5DF ON card (player_id)');
    }

    public function down(Schema $schema): void
    {
        // Créer une table temporaire sans le champ club
        $this->addSql('CREATE TEMPORARY TABLE __temp__card AS SELECT id, player_id, summary, notable_action, number, position, image_filename, start_season, end_season FROM card');
        $this->addSql('DROP TABLE card');
        
        // Recréer la table sans le champ club
        $this->addSql('CREATE TABLE card (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            player_id INTEGER NOT NULL,
            summary VARCHAR(255) DEFAULT NULL,
            notable_action VARCHAR(255) DEFAULT NULL,
            number INTEGER NOT NULL,
            position VARCHAR(255) NOT NULL,
            image_filename VARCHAR(255) DEFAULT NULL,
            start_season INTEGER NOT NULL,
            end_season INTEGER DEFAULT NULL,
            CONSTRAINT FK_161498D399E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        
        // Restaurer les données
        $this->addSql('INSERT INTO card (id, player_id, summary, notable_action, number, position, image_filename, start_season, end_season) 
            SELECT id, player_id, summary, notable_action, number, position, image_filename, start_season, end_season FROM __temp__card');
        
        // Supprimer la table temporaire
        $this->addSql('DROP TABLE __temp__card');
        
        // Recréer l'index
        $this->addSql('CREATE INDEX IDX_161498D399E6F5DF ON card (player_id)');
    }
}
