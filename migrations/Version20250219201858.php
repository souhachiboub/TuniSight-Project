<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250219201858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, publication_id INT NOT NULL, url VARCHAR(255) NOT NULL, INDEX IDX_C53D045F38B217A7 (publication_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE likes (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, publication_id INT NOT NULL, INDEX IDX_49CA4E7DA76ED395 (user_id), INDEX IDX_49CA4E7D38B217A7 (publication_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE likes_commentaire (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, commentaire_id INT NOT NULL, INDEX IDX_1A3A4385A76ED395 (user_id), INDEX IDX_1A3A4385BA9CD190 (commentaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, reclamation_id INT NOT NULL, date DATETIME NOT NULL, reponse VARCHAR(5000) NOT NULL, UNIQUE INDEX UNIQ_5FB6DEC72D6BA2D9 (reclamation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F38B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id)');
        $this->addSql('ALTER TABLE likes ADD CONSTRAINT FK_49CA4E7DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE likes ADD CONSTRAINT FK_49CA4E7D38B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id)');
        $this->addSql('ALTER TABLE likes_commentaire ADD CONSTRAINT FK_1A3A4385A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE likes_commentaire ADD CONSTRAINT FK_1A3A4385BA9CD190 FOREIGN KEY (commentaire_id) REFERENCES commentaire (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC72D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamation (id)');
        $this->addSql('ALTER TABLE activite ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE commentaire ADD date DATE NOT NULL, DROP nbr_like');
        $this->addSql('ALTER TABLE offre ADD activitie_id INT NOT NULL');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866FEB0ED4F5 FOREIGN KEY (activitie_id) REFERENCES activite (id)');
        $this->addSql('CREATE INDEX IDX_AF86866FEB0ED4F5 ON offre (activitie_id)');
        $this->addSql('DROP INDEX `primary` ON produit_panier');
        $this->addSql('ALTER TABLE produit_panier ADD PRIMARY KEY (panier_id, produit_id)');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27BCF5E72D');
        $this->addSql('DROP INDEX IDX_29A5EC27BCF5E72D ON produit');
        $this->addSql('ALTER TABLE produit ADD quantite INT NOT NULL, ADD disponibilite TINYINT(1) NOT NULL, ADD reference VARCHAR(255) NOT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE nbr_produits_id nbr_produits_id INT DEFAULT NULL, CHANGE categorie_id categorie_produit_id INT NOT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC2791FDB457 FOREIGN KEY (categorie_produit_id) REFERENCES categorie_produit (id)');
        $this->addSql('CREATE INDEX IDX_29A5EC2791FDB457 ON produit (categorie_produit_id)');
        $this->addSql('ALTER TABLE publication DROP image, DROP nbr_like, CHANGE contenu contenu VARCHAR(5000) NOT NULL');
        $this->addSql('ALTER TABLE reclamation ADD date_envoie DATE NOT NULL, CHANGE description description VARCHAR(5000) NOT NULL');
        $this->addSql('ALTER TABLE user ADD motdepasse VARCHAR(255) NOT NULL, ADD confirmpwd VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE voucher ADD user_id INT DEFAULT NULL, ADD is_used TINYINT(1) DEFAULT NULL, DROP type_reduction, CHANGE valeur_reduction valeur_reduction INT NOT NULL');
        $this->addSql('ALTER TABLE voucher ADD CONSTRAINT FK_1392A5D8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_1392A5D8A76ED395 ON voucher (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F38B217A7');
        $this->addSql('ALTER TABLE likes DROP FOREIGN KEY FK_49CA4E7DA76ED395');
        $this->addSql('ALTER TABLE likes DROP FOREIGN KEY FK_49CA4E7D38B217A7');
        $this->addSql('ALTER TABLE likes_commentaire DROP FOREIGN KEY FK_1A3A4385A76ED395');
        $this->addSql('ALTER TABLE likes_commentaire DROP FOREIGN KEY FK_1A3A4385BA9CD190');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC72D6BA2D9');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE likes');
        $this->addSql('DROP TABLE likes_commentaire');
        $this->addSql('DROP TABLE reponse');
        $this->addSql('ALTER TABLE activite DROP updated_at, CHANGE image image VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE commentaire ADD nbr_like INT NOT NULL, DROP date');
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866FEB0ED4F5');
        $this->addSql('DROP INDEX IDX_AF86866FEB0ED4F5 ON offre');
        $this->addSql('ALTER TABLE offre DROP activitie_id');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC2791FDB457');
        $this->addSql('DROP INDEX IDX_29A5EC2791FDB457 ON produit');
        $this->addSql('ALTER TABLE produit ADD categorie_id INT NOT NULL, DROP categorie_produit_id, DROP quantite, DROP disponibilite, DROP reference, CHANGE user_id user_id INT NOT NULL, CHANGE nbr_produits_id nbr_produits_id INT NOT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_produit (id)');
        $this->addSql('CREATE INDEX IDX_29A5EC27BCF5E72D ON produit (categorie_id)');
        $this->addSql('DROP INDEX `PRIMARY` ON produit_panier');
        $this->addSql('ALTER TABLE produit_panier ADD PRIMARY KEY (produit_id, panier_id)');
        $this->addSql('ALTER TABLE publication ADD image VARCHAR(255) NOT NULL, ADD nbr_like INT NOT NULL, CHANGE contenu contenu VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE reclamation DROP date_envoie, CHANGE description description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user DROP motdepasse, DROP confirmpwd');
        $this->addSql('ALTER TABLE voucher DROP FOREIGN KEY FK_1392A5D8A76ED395');
        $this->addSql('DROP INDEX IDX_1392A5D8A76ED395 ON voucher');
        $this->addSql('ALTER TABLE voucher ADD type_reduction VARCHAR(255) NOT NULL, DROP user_id, DROP is_used, CHANGE valeur_reduction valeur_reduction VARCHAR(255) NOT NULL');
    }
}
