<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250215175619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite DROP updated_at, CHANGE image image VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE categorie_activite CHANGE description description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE offre DROP nom');
        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(255) NOT NULL, CHANGE num_tel num_tel INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE categorie_activite CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE offre ADD nom VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(255) DEFAULT NULL, CHANGE num_tel num_tel INT DEFAULT NULL');
    }
}
