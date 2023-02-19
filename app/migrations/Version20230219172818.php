<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230219172818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE apprenticeship ADD title VARCHAR(512) NOT NULL, ADD company_name VARCHAR(512) NOT NULL, CHANGE azubi_id azubi_id INT DEFAULT NULL, CHANGE ausbilder_id ausbilder_id INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6904B3755242FFC4 ON apprenticeship (invite_token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_6904B3755242FFC4 ON apprenticeship');
        $this->addSql('ALTER TABLE apprenticeship DROP title, DROP company_name, CHANGE azubi_id azubi_id VARCHAR(255) DEFAULT NULL, CHANGE ausbilder_id ausbilder_id VARCHAR(255) NOT NULL');
    }
}
