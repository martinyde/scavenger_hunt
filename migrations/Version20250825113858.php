<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250825113858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE highscore (id INT AUTO_INCREMENT NOT NULL, participant_id INT NOT NULL, race_id INT NOT NULL, scavenger_hunt_id INT NOT NULL, progress_task_entry INT NOT NULL, progress_task_solution INT NOT NULL, time INT NOT NULL, created DATETIME NOT NULL, participant_name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_901BB3929D1C3019 (participant_id), INDEX IDX_901BB3926E59D40D (race_id), INDEX IDX_901BB3927104D71F (scavenger_hunt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participant (id INT AUTO_INCREMENT NOT NULL, race_id INT NOT NULL, name VARCHAR(255) NOT NULL, progress_entry_count INT DEFAULT NULL, progress_solution_count INT DEFAULT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', finished TINYINT(1) DEFAULT NULL, finished_time INT DEFAULT NULL, INDEX IDX_D79F6B116E59D40D (race_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participant_task_entry (participant_id INT NOT NULL, task_id INT NOT NULL, INDEX IDX_D045604E9D1C3019 (participant_id), INDEX IDX_D045604E8DB60186 (task_id), PRIMARY KEY(participant_id, task_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participant_task_solution (participant_id INT NOT NULL, task_id INT NOT NULL, INDEX IDX_5A0BDB0B9D1C3019 (participant_id), INDEX IDX_5A0BDB0B8DB60186 (task_id), PRIMARY KEY(participant_id, task_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE race (id INT AUTO_INCREMENT NOT NULL, scavenger_hunt_id INT NOT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', timer DATETIME DEFAULT NULL COMMENT \'(DC2Type:date_point)\', race_duration INT DEFAULT NULL, task_access JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', active TINYINT(1) NOT NULL, type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_DA6FBBAFD17F50A6 (uuid), INDEX IDX_DA6FBBAF7104D71F (scavenger_hunt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE scavenger_hunt (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_D34CC77A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, scavenger_hunt_id INT DEFAULT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', pass_key VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, solutions LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', text_before LONGTEXT DEFAULT NULL, text_after LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_527EDB25D17F50A6 (uuid), INDEX IDX_527EDB257104D71F (scavenger_hunt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participant_session (guid VARBINARY(128) NOT NULL, sess_data LONGBLOB NOT NULL, sess_lifetime INT UNSIGNED NOT NULL, sess_time INT UNSIGNED NOT NULL, INDEX sess_lifetime_idx (sess_lifetime), PRIMARY KEY(guid)) DEFAULT CHARACTER SET utf8 COLLATE `utf8mb3_bin` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE highscore ADD CONSTRAINT FK_901BB3929D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id)');
        $this->addSql('ALTER TABLE highscore ADD CONSTRAINT FK_901BB3926E59D40D FOREIGN KEY (race_id) REFERENCES race (id)');
        $this->addSql('ALTER TABLE highscore ADD CONSTRAINT FK_901BB3927104D71F FOREIGN KEY (scavenger_hunt_id) REFERENCES scavenger_hunt (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B116E59D40D FOREIGN KEY (race_id) REFERENCES race (id)');
        $this->addSql('ALTER TABLE participant_task_entry ADD CONSTRAINT FK_D045604E9D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participant_task_entry ADD CONSTRAINT FK_D045604E8DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participant_task_solution ADD CONSTRAINT FK_5A0BDB0B9D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participant_task_solution ADD CONSTRAINT FK_5A0BDB0B8DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE race ADD CONSTRAINT FK_DA6FBBAF7104D71F FOREIGN KEY (scavenger_hunt_id) REFERENCES scavenger_hunt (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE scavenger_hunt ADD CONSTRAINT FK_D34CC77A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB257104D71F FOREIGN KEY (scavenger_hunt_id) REFERENCES scavenger_hunt (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE highscore DROP FOREIGN KEY FK_901BB3929D1C3019');
        $this->addSql('ALTER TABLE highscore DROP FOREIGN KEY FK_901BB3926E59D40D');
        $this->addSql('ALTER TABLE highscore DROP FOREIGN KEY FK_901BB3927104D71F');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B116E59D40D');
        $this->addSql('ALTER TABLE participant_task_entry DROP FOREIGN KEY FK_D045604E9D1C3019');
        $this->addSql('ALTER TABLE participant_task_entry DROP FOREIGN KEY FK_D045604E8DB60186');
        $this->addSql('ALTER TABLE participant_task_solution DROP FOREIGN KEY FK_5A0BDB0B9D1C3019');
        $this->addSql('ALTER TABLE participant_task_solution DROP FOREIGN KEY FK_5A0BDB0B8DB60186');
        $this->addSql('ALTER TABLE race DROP FOREIGN KEY FK_DA6FBBAF7104D71F');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE scavenger_hunt DROP FOREIGN KEY FK_D34CC77A76ED395');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB257104D71F');
        $this->addSql('DROP TABLE highscore');
        $this->addSql('DROP TABLE participant');
        $this->addSql('DROP TABLE participant_task_entry');
        $this->addSql('DROP TABLE participant_task_solution');
        $this->addSql('DROP TABLE race');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE scavenger_hunt');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP TABLE participant_session');
    }
}
