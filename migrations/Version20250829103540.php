<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829103540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE highscore DROP FOREIGN KEY FK_901BB3929D1C3019');
        $this->addSql('ALTER TABLE highscore CHANGE participant_id participant_id INT DEFAULT NULL, CHANGE race_id race_id INT DEFAULT NULL, CHANGE scavenger_hunt_id scavenger_hunt_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE highscore ADD CONSTRAINT FK_901BB3929D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE highscore DROP FOREIGN KEY FK_901BB3929D1C3019');
        $this->addSql('ALTER TABLE highscore CHANGE participant_id participant_id INT NOT NULL, CHANGE race_id race_id INT NOT NULL, CHANGE scavenger_hunt_id scavenger_hunt_id INT NOT NULL');
        $this->addSql('ALTER TABLE highscore ADD CONSTRAINT FK_901BB3929D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id)');
    }
}
