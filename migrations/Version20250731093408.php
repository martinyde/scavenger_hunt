<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250731093408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE participant_task_entry (participant_id INT NOT NULL, task_id INT NOT NULL, INDEX IDX_D045604E9D1C3019 (participant_id), INDEX IDX_D045604E8DB60186 (task_id), PRIMARY KEY(participant_id, task_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participant_task_solution (participant_id INT NOT NULL, task_id INT NOT NULL, INDEX IDX_5A0BDB0B9D1C3019 (participant_id), INDEX IDX_5A0BDB0B8DB60186 (task_id), PRIMARY KEY(participant_id, task_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE participant_task_entry ADD CONSTRAINT FK_D045604E9D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participant_task_entry ADD CONSTRAINT FK_D045604E8DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participant_task_solution ADD CONSTRAINT FK_5A0BDB0B9D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participant_task_solution ADD CONSTRAINT FK_5A0BDB0B8DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participant_task_entry DROP FOREIGN KEY FK_D045604E9D1C3019');
        $this->addSql('ALTER TABLE participant_task_entry DROP FOREIGN KEY FK_D045604E8DB60186');
        $this->addSql('ALTER TABLE participant_task_solution DROP FOREIGN KEY FK_5A0BDB0B9D1C3019');
        $this->addSql('ALTER TABLE participant_task_solution DROP FOREIGN KEY FK_5A0BDB0B8DB60186');
        $this->addSql('DROP TABLE participant_task_entry');
        $this->addSql('DROP TABLE participant_task_solution');
    }
}
