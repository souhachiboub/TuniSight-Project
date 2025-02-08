<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250208115446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite ADD offre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B87555154CC8505A FOREIGN KEY (offre_id) REFERENCES offre (id)');
        $this->addSql('CREATE INDEX IDX_B87555154CC8505A ON activite (offre_id)');
        $this->addSql('ALTER TABLE user ADD voucher_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64928AA1B6F FOREIGN KEY (voucher_id) REFERENCES voucher (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64928AA1B6F ON user (voucher_id)');
        $this->addSql('ALTER TABLE voucher ADD type_reduction VARCHAR(255) NOT NULL, DROP is_used, DROP user_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B87555154CC8505A');
        $this->addSql('DROP INDEX IDX_B87555154CC8505A ON activite');
        $this->addSql('ALTER TABLE activite DROP offre_id');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64928AA1B6F');
        $this->addSql('DROP INDEX UNIQ_8D93D64928AA1B6F ON user');
        $this->addSql('ALTER TABLE user DROP voucher_id');
        $this->addSql('ALTER TABLE voucher ADD is_used TINYINT(1) DEFAULT NULL, ADD user_id INT DEFAULT NULL, DROP type_reduction');
    }
}
