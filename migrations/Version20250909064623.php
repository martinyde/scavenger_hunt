<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250909064623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE highscore DROP FOREIGN KEY FK_901BB3926E59D40D');
        $this->addSql('ALTER TABLE highscore DROP FOREIGN KEY FK_901BB3927104D71F');
        $this->addSql('ALTER TABLE highscore DROP FOREIGN KEY FK_901BB3929D1C3019');
        $this->addSql('DROP INDEX IDX_901BB3926E59D40D ON highscore');
        $this->addSql('DROP INDEX IDX_901BB3927104D71F ON highscore');
        $this->addSql('DROP INDEX UNIQ_901BB3929D1C3019 ON highscore');
        $this->addSql('ALTER TABLE highscore ADD participant INT DEFAULT NULL, ADD race INT DEFAULT NULL, ADD scavenger_hunt INT DEFAULT NULL, DROP participant_id, DROP race_id, DROP scavenger_hunt_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE highscore ADD participant_id INT DEFAULT NULL, ADD race_id INT DEFAULT NULL, ADD scavenger_hunt_id INT DEFAULT NULL, DROP participant, DROP race, DROP scavenger_hunt');
        $this->addSql('ALTER TABLE highscore ADD CONSTRAINT FK_901BB3926E59D40D FOREIGN KEY (race_id) REFERENCES race (id)');
        $this->addSql('ALTER TABLE highscore ADD CONSTRAINT FK_901BB3927104D71F FOREIGN KEY (scavenger_hunt_id) REFERENCES scavenger_hunt (id)');
        $this->addSql('ALTER TABLE highscore ADD CONSTRAINT FK_901BB3929D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id)');
        $this->addSql('CREATE INDEX IDX_901BB3926E59D40D ON highscore (race_id)');
        $this->addSql('CREATE INDEX IDX_901BB3927104D71F ON highscore (scavenger_hunt_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_901BB3929D1C3019 ON highscore (participant_id)');
    }
}
