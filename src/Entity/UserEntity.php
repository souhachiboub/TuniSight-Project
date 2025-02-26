<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserEntityRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserEntityRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class UserEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $role = null;

    public function getId(): ?int { return $this->id; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }
    public function getRoles(): array { return $this->roles; }
    public function setRoles(array $roles): self { $this->roles = $roles; return $this; }
    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }
    public function getRole(): ?string { return $this->role; }
    public function setRole(string $role): self { $this->role = $role; return $this; }
    public function eraseCredentials() {}

    public function getUserIdentifier(): string
{
    return $this->email; // Ou un autre identifiant unique
}

}
