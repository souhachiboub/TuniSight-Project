<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Offre;
use App\Entity\Ville;
use App\Entity\Activite;
use App\Entity\CategorieActivite;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
{
    // $faker = Factory::create();

    // // // Création d'une ville
    // // $ville = new Ville();
    // // $ville->setNom($faker->city);
    // // $manager->persist($ville);

    // // Création de la catégorie
    // $categorie = new CategorieActivite();
    // $categorie->setNom('Sport')
    //     ->setDescription($faker->sentence(10));
    // $manager->persist($categorie);

    // $this->addReference('categorie_sport', $categorie);
    
    // $activites = [];
    // for ($i = 0; $i < 10; $i++) {
    //     $activite = new Activite();
    //     $activite->setLibelle($faker->sentence(3))
    //         ->setDescription($faker->paragraph)
    //         ->setImage($faker->imageUrl(640, 480, 'nature'))
    //         ->setPrix($faker->randomFloat(2, 10, 200))
    //         ->setDuree($faker->numberBetween(1, 5))
    //         ->setCapacite($faker->numberBetween(10, 100))
    //         ->setDisponibilite($faker->boolean)
    //         ->setCategorie($categorie)
    //         ->setLocalisation($faker->sentence());
    //         //->setVille($ville); // Assignation correcte d'une instance de Ville

    //     $manager->persist($activite);
    //     $activites[] = $activite;
    // }

    // for ($i = 1; $i <= 10; $i++) {
    //     $offre = new Offre();
    //     $offre->setReduction($faker->numberBetween(10, 50));

    //     $dateDebut = $faker->dateTimeBetween('-1 month', 'now');
    //     $dateExpiration = $faker->dateTimeBetween($dateDebut, '+3 months');

    //     $offre->setDateDebut($dateDebut);
    //     $offre->setDateExpiration($dateExpiration);
    //     $offre->setActivitie($faker->randomElement($activites));

    //     $manager->persist($offre);
    // }

   // $manager->flush();

   $faker = Factory::create('fr_FR');

   for ($i = 0; $i < 10; $i++) { 
       $user = new User();
       $user->setNom($faker->lastName);
       $user->setPrenom($faker->firstName);
       $user->setUsername($faker->userName);
       $user->setNumTel($faker->randomNumber(8, true)); 
       $user->setCin($faker->randomNumber(8, true)); 
       $user->setEmail($faker->email);
       $user->setMotdepasse('password' . $i); 
       $user->setConfirmpwd('password' . $i); 
       $user->setDateNaissance($faker->dateTimeBetween('-60 years', '-18 years'));
       $user->setAdresse($faker->address);
       $user->setPhotoProfil($faker->imageUrl(200, 200, 'people'));
       $user->setBio($faker->sentence);
       $user->setRole($faker->randomElement(['admin', 'prestataire', 'client']));

       $manager->persist($user);
   }

   $manager->flush();
}

}
