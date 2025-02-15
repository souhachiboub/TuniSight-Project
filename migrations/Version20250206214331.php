<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250206214331 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B87555154CC8505A');
        $this->addSql('DROP INDEX IDX_B87555154CC8505A ON activite');
        $this->addSql('ALTER TABLE activite DROP offre_id');
        $this->addSql('ALTER TABLE offre ADD activitie_id INT NOT NULL');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866FEB0ED4F5 FOREIGN KEY (activitie_id) REFERENCES activite (id)');
        $this->addSql('CREATE INDEX IDX_AF86866FEB0ED4F5 ON offre (activitie_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite ADD offre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B87555154CC8505A FOREIGN KEY (offre_id) REFERENCES offre (id)');
        $this->addSql('CREATE INDEX IDX_B87555154CC8505A ON activite (offre_id)');
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866FEB0ED4F5');
        $this->addSql('DROP INDEX IDX_AF86866FEB0ED4F5 ON offre');
        $this->addSql('ALTER TABLE offre DROP activitie_id');
    }
}
