<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250710083858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE participant (id INT AUTO_INCREMENT NOT NULL, race_id INT NOT NULL, name VARCHAR(255) NOT NULL, progress_task_entry JSON NOT NULL COMMENT \'(DC2Type:json)\', progress_task_solution JSON NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_D79F6B116E59D40D (race_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE race (id INT AUTO_INCREMENT NOT NULL, scavenger_hunt_id INT NOT NULL, timer DATETIME DEFAULT NULL COMMENT \'(DC2Type:date_point)\', race_duration INT DEFAULT NULL, task_access JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_DA6FBBAF7104D71F (scavenger_hunt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE scavanger_hunt (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, scavanger_hunt_id INT DEFAULT NULL, pass_key VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, solutions LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', text_before LONGTEXT DEFAULT NULL, text_after LONGTEXT DEFAULT NULL, INDEX IDX_527EDB25B1BDBD89 (scavanger_hunt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B116E59D40D FOREIGN KEY (race_id) REFERENCES race (id)');
        $this->addSql('ALTER TABLE race ADD CONSTRAINT FK_DA6FBBAF7104D71F FOREIGN KEY (scavenger_hunt_id) REFERENCES scavanger_hunt (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25B1BDBD89 FOREIGN KEY (scavanger_hunt_id) REFERENCES scavanger_hunt (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B116E59D40D');
        $this->addSql('ALTER TABLE race DROP FOREIGN KEY FK_DA6FBBAF7104D71F');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25B1BDBD89');
        $this->addSql('DROP TABLE participant');
        $this->addSql('DROP TABLE race');
        $this->addSql('DROP TABLE scavanger_hunt');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
