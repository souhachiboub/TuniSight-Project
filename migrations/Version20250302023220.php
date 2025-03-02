<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250302023220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite CHANGE libelle libelle VARCHAR(255) NOT NULL, CHANGE description description VARCHAR(255) NOT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL, CHANGE localisation localisation VARCHAR(255) NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B8755515A76ED395 FOREIGN KEY (user_id) REFERENCES user_entity (id)');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B8755515A73F0036 FOREIGN KEY (ville_id) REFERENCES ville (id)');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B8755515BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_activite (id)');
        $this->addSql('CREATE INDEX IDX_B8755515A76ED395 ON activite (user_id)');
        $this->addSql('CREATE INDEX IDX_B8755515A73F0036 ON activite (ville_id)');
        $this->addSql('CREATE INDEX IDX_B8755515BCF5E72D ON activite (categorie_id)');
        $this->addSql('ALTER TABLE activite_reservation ADD CONSTRAINT FK_62FB3C789B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activite_reservation ADD CONSTRAINT FK_62FB3C78B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE avis ADD user_id INT NOT NULL, ADD activite_id INT NOT NULL');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0A76ED395 FOREIGN KEY (user_id) REFERENCES user_entity (id)');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF09B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id)');
        $this->addSql('CREATE INDEX IDX_8F91ABF0A76ED395 ON avis (user_id)');
        $this->addSql('CREATE INDEX IDX_8F91ABF09B0F88B1 ON avis (activite_id)');
        $this->addSql('ALTER TABLE commande ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES user_entity (id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67DA76ED395 ON commande (user_id)');
        $this->addSql('ALTER TABLE commentaire ADD publication_id INT NOT NULL, ADD date DATE NOT NULL, CHANGE nbr_like user_id INT NOT NULL');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCA76ED395 FOREIGN KEY (user_id) REFERENCES user_entity (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC38B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id)');
        $this->addSql('CREATE INDEX IDX_67F068BCA76ED395 ON commentaire (user_id)');
        $this->addSql('CREATE INDEX IDX_67F068BC38B217A7 ON commentaire (publication_id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F38B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE likes ADD CONSTRAINT FK_49CA4E7DA76ED395 FOREIGN KEY (user_id) REFERENCES user_entity (id)');
        $this->addSql('ALTER TABLE likes ADD CONSTRAINT FK_49CA4E7D38B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id)');
        $this->addSql('ALTER TABLE likes_commentaire ADD CONSTRAINT FK_1A3A4385A76ED395 FOREIGN KEY (user_id) REFERENCES user_entity (id)');
        $this->addSql('ALTER TABLE likes_commentaire ADD CONSTRAINT FK_1A3A4385BA9CD190 FOREIGN KEY (commentaire_id) REFERENCES commentaire (id)');
        $this->addSql('ALTER TABLE notification ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user_entity (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAA76ED395 ON notification (user_id)');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866FEB0ED4F5 FOREIGN KEY (activitie_id) REFERENCES activite (id)');
        $this->addSql('ALTER TABLE produit_panier ADD CONSTRAINT FK_D39EC6C8F77D927C FOREIGN KEY (panier_id) REFERENCES panier (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produit_panier ADD CONSTRAINT FK_D39EC6C8F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produit ADD user_id INT DEFAULT NULL, ADD categorie_produit_id INT NOT NULL, ADD nbr_produits_id INT DEFAULT NULL, ADD ligne_commande_id INT DEFAULT NULL, ADD quantite INT NOT NULL, ADD disponibilite TINYINT(1) NOT NULL, ADD reference VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27A76ED395 FOREIGN KEY (user_id) REFERENCES user_entity (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC2791FDB457 FOREIGN KEY (categorie_produit_id) REFERENCES categorie_produit (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC2727FC0A64 FOREIGN KEY (nbr_produits_id) REFERENCES stock (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27E10FEE63 FOREIGN KEY (ligne_commande_id) REFERENCES ligne_commande (id)');
        $this->addSql('CREATE INDEX IDX_29A5EC27A76ED395 ON produit (user_id)');
        $this->addSql('CREATE INDEX IDX_29A5EC2791FDB457 ON produit (categorie_produit_id)');
        $this->addSql('CREATE INDEX IDX_29A5EC2727FC0A64 ON produit (nbr_produits_id)');
        $this->addSql('CREATE INDEX IDX_29A5EC27E10FEE63 ON produit (ligne_commande_id)');
        $this->addSql('ALTER TABLE publication DROP image, CHANGE contenu contenu VARCHAR(5000) NOT NULL, CHANGE nbr_like user_id INT NOT NULL');
        $this->addSql('ALTER TABLE publication ADD CONSTRAINT FK_AF3C6779A76ED395 FOREIGN KEY (user_id) REFERENCES user_entity (id)');
        $this->addSql('CREATE INDEX IDX_AF3C6779A76ED395 ON publication (user_id)');
        $this->addSql('ALTER TABLE reclamation ADD user_id INT NOT NULL, ADD date_envoie DATE NOT NULL, CHANGE description description VARCHAR(5000) NOT NULL');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404A76ED395 FOREIGN KEY (user_id) REFERENCES user_entity (id)');
        $this->addSql('CREATE INDEX IDX_CE606404A76ED395 ON reclamation (user_id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC72D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamation (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES user_entity (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849559B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id)');
        $this->addSql('ALTER TABLE user_entity ADD CONSTRAINT FK_6B7A5F5528AA1B6F FOREIGN KEY (voucher_id) REFERENCES voucher (id)');
        $this->addSql('ALTER TABLE voucher ADD user_id INT DEFAULT NULL, ADD is_assigned TINYINT(1) DEFAULT NULL, DROP type_reduction, CHANGE valeur_reduction valeur_reduction INT NOT NULL');
        $this->addSql('ALTER TABLE voucher ADD CONSTRAINT FK_1392A5D8A76ED395 FOREIGN KEY (user_id) REFERENCES user_entity (id)');
        $this->addSql('CREATE INDEX IDX_1392A5D8A76ED395 ON voucher (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B8755515A76ED395');
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B8755515A73F0036');
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B8755515BCF5E72D');
        $this->addSql('DROP INDEX IDX_B8755515A76ED395 ON activite');
        $this->addSql('DROP INDEX IDX_B8755515A73F0036 ON activite');
        $this->addSql('DROP INDEX IDX_B8755515BCF5E72D ON activite');
        $this->addSql('DROP INDEX `primary` ON activite');
        $this->addSql('ALTER TABLE activite CHANGE libelle libelle VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE description description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE image image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE localisation localisation VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE activite_reservation DROP FOREIGN KEY FK_62FB3C789B0F88B1');
        $this->addSql('ALTER TABLE activite_reservation DROP FOREIGN KEY FK_62FB3C78B83297E7');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0A76ED395');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF09B0F88B1');
        $this->addSql('DROP INDEX IDX_8F91ABF0A76ED395 ON avis');
        $this->addSql('DROP INDEX IDX_8F91ABF09B0F88B1 ON avis');
        $this->addSql('ALTER TABLE avis DROP user_id, DROP activite_id');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395');
        $this->addSql('DROP INDEX IDX_6EEAA67DA76ED395 ON commande');
        $this->addSql('ALTER TABLE commande DROP user_id');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCA76ED395');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC38B217A7');
        $this->addSql('DROP INDEX IDX_67F068BCA76ED395 ON commentaire');
        $this->addSql('DROP INDEX IDX_67F068BC38B217A7 ON commentaire');
        $this->addSql('ALTER TABLE commentaire ADD nbr_like INT NOT NULL, DROP user_id, DROP publication_id, DROP date');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F38B217A7');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B82EA2E54');
        $this->addSql('ALTER TABLE likes DROP FOREIGN KEY FK_49CA4E7DA76ED395');
        $this->addSql('ALTER TABLE likes DROP FOREIGN KEY FK_49CA4E7D38B217A7');
        $this->addSql('ALTER TABLE likes_commentaire DROP FOREIGN KEY FK_1A3A4385A76ED395');
        $this->addSql('ALTER TABLE likes_commentaire DROP FOREIGN KEY FK_1A3A4385BA9CD190');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('DROP INDEX IDX_BF5476CAA76ED395 ON notification');
        $this->addSql('ALTER TABLE notification DROP user_id');
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866FEB0ED4F5');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27A76ED395');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC2791FDB457');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC2727FC0A64');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27E10FEE63');
        $this->addSql('DROP INDEX IDX_29A5EC27A76ED395 ON produit');
        $this->addSql('DROP INDEX IDX_29A5EC2791FDB457 ON produit');
        $this->addSql('DROP INDEX IDX_29A5EC2727FC0A64 ON produit');
        $this->addSql('DROP INDEX IDX_29A5EC27E10FEE63 ON produit');
        $this->addSql('ALTER TABLE produit DROP user_id, DROP categorie_produit_id, DROP nbr_produits_id, DROP ligne_commande_id, DROP quantite, DROP disponibilite, DROP reference');
        $this->addSql('ALTER TABLE produit_panier DROP FOREIGN KEY FK_D39EC6C8F77D927C');
        $this->addSql('ALTER TABLE produit_panier DROP FOREIGN KEY FK_D39EC6C8F347EFB');
        $this->addSql('ALTER TABLE publication DROP FOREIGN KEY FK_AF3C6779A76ED395');
        $this->addSql('DROP INDEX IDX_AF3C6779A76ED395 ON publication');
        $this->addSql('ALTER TABLE publication ADD image VARCHAR(255) NOT NULL, CHANGE contenu contenu VARCHAR(255) NOT NULL, CHANGE user_id nbr_like INT NOT NULL');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404A76ED395');
        $this->addSql('DROP INDEX IDX_CE606404A76ED395 ON reclamation');
        $this->addSql('ALTER TABLE reclamation DROP user_id, DROP date_envoie, CHANGE description description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC72D6BA2D9');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A76ED395');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849559B0F88B1');
        $this->addSql('ALTER TABLE user_entity DROP FOREIGN KEY FK_6B7A5F5528AA1B6F');
        $this->addSql('ALTER TABLE voucher DROP FOREIGN KEY FK_1392A5D8A76ED395');
        $this->addSql('DROP INDEX IDX_1392A5D8A76ED395 ON voucher');
        $this->addSql('ALTER TABLE voucher ADD type_reduction VARCHAR(255) NOT NULL, DROP user_id, DROP is_assigned, CHANGE valeur_reduction valeur_reduction VARCHAR(255) NOT NULL');
    }
}
