<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250821082336 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE highscore (id INT AUTO_INCREMENT NOT NULL, participant_id INT NOT NULL, race_id INT NOT NULL, scavenger_hunt_id INT NOT NULL, progress_task_entry INT NOT NULL, progress_task_solution INT NOT NULL, time INT NOT NULL, created DATETIME NOT NULL, UNIQUE INDEX UNIQ_901BB3929D1C3019 (participant_id), INDEX IDX_901BB3926E59D40D (race_id), INDEX IDX_901BB3927104D71F (scavenger_hunt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE highscore ADD CONSTRAINT FK_901BB3929D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id)');
        $this->addSql('ALTER TABLE highscore ADD CONSTRAINT FK_901BB3926E59D40D FOREIGN KEY (race_id) REFERENCES race (id)');
        $this->addSql('ALTER TABLE highscore ADD CONSTRAINT FK_901BB3927104D71F FOREIGN KEY (scavenger_hunt_id) REFERENCES scavanger_hunt (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE highscore DROP FOREIGN KEY FK_901BB3929D1C3019');
        $this->addSql('ALTER TABLE highscore DROP FOREIGN KEY FK_901BB3926E59D40D');
        $this->addSql('ALTER TABLE highscore DROP FOREIGN KEY FK_901BB3927104D71F');
        $this->addSql('DROP TABLE highscore');
    }
}
