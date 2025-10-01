<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251001181033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game (id SERIAL NOT NULL, played_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(20) NOT NULL, winner_team INT DEFAULT NULL, team1_score INT NOT NULL, team2_score INT NOT NULL, duration_seconds INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN game.played_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE game_player (id SERIAL NOT NULL, game_id INT NOT NULL, player_id INT NOT NULL, team INT NOT NULL, goals_scored INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E52CD7ADE48FD905 ON game_player (game_id)');
        $this->addSql('CREATE INDEX IDX_E52CD7AD99E6F5DF ON game_player (player_id)');
        $this->addSql('CREATE TABLE player (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_98197A655E237E06 ON player (name)');
        $this->addSql('COMMENT ON COLUMN player.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE game_player ADD CONSTRAINT FK_E52CD7ADE48FD905 FOREIGN KEY (game_id) REFERENCES game (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_player ADD CONSTRAINT FK_E52CD7AD99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE game_player DROP CONSTRAINT FK_E52CD7ADE48FD905');
        $this->addSql('ALTER TABLE game_player DROP CONSTRAINT FK_E52CD7AD99E6F5DF');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE game_player');
        $this->addSql('DROP TABLE player');
    }
}
