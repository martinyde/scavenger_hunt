<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250825112346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE highscore ADD CONSTRAINT FK_901BB3927104D71F FOREIGN KEY (scavenger_hunt_id) REFERENCES scavenger_hunt (id)');
        $this->addSql('ALTER TABLE race ADD CONSTRAINT FK_DA6FBBAF7104D71F FOREIGN KEY (scavenger_hunt_id) REFERENCES scavenger_hunt (id)');
        $this->addSql('DROP INDEX IDX_527EDB25B1BDBD89 ON task');
        $this->addSql('ALTER TABLE task CHANGE scavanger_hunt_id scavenger_hunt_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB257104D71F FOREIGN KEY (scavenger_hunt_id) REFERENCES scavenger_hunt (id)');
        $this->addSql('CREATE INDEX IDX_527EDB257104D71F ON task (scavenger_hunt_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB257104D71F');
        $this->addSql('DROP INDEX IDX_527EDB257104D71F ON task');
        $this->addSql('ALTER TABLE task CHANGE scavenger_hunt_id scavanger_hunt_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_527EDB25B1BDBD89 ON task (scavanger_hunt_id)');
        $this->addSql('ALTER TABLE race DROP FOREIGN KEY FK_DA6FBBAF7104D71F');
        $this->addSql('ALTER TABLE highscore DROP FOREIGN KEY FK_901BB3927104D71F');
    }
}
