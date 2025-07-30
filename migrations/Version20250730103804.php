<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250730103804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE participant_session (guid VARBINARY(128) NOT NULL, sess_data LONGBLOB NOT NULL, sess_lifetime INT UNSIGNED NOT NULL, sess_time INT UNSIGNED NOT NULL, INDEX sess_lifetime_idx (sess_lifetime), PRIMARY KEY(guid)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_bin` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DA6FBBAFD17F50A6 ON race (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_527EDB25D17F50A6 ON task (uuid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE participant_session');
        $this->addSql('DROP INDEX UNIQ_527EDB25D17F50A6 ON task');
        $this->addSql('DROP INDEX UNIQ_DA6FBBAFD17F50A6 ON race');
    }
}

