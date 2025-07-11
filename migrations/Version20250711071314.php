<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250711071314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B116E59D40D FOREIGN KEY (race_id) REFERENCES race (id)');
        $this->addSql('ALTER TABLE race ADD active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE race ADD CONSTRAINT FK_DA6FBBAF7104D71F FOREIGN KEY (scavenger_hunt_id) REFERENCES scavanger_hunt (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE scavanger_hunt ADD CONSTRAINT FK_4DF6C0DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_4DF6C0DA76ED395 ON scavanger_hunt (user_id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25B1BDBD89 FOREIGN KEY (scavanger_hunt_id) REFERENCES scavanger_hunt (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25B1BDBD89');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B116E59D40D');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE race DROP FOREIGN KEY FK_DA6FBBAF7104D71F');
        $this->addSql('ALTER TABLE race DROP active');
        $this->addSql('ALTER TABLE scavanger_hunt DROP FOREIGN KEY FK_4DF6C0DA76ED395');
        $this->addSql('DROP INDEX IDX_4DF6C0DA76ED395 ON scavanger_hunt');
    }
}
