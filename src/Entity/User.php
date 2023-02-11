<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Delete;
use App\State\UserDataProcessor;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Post(validationContext: ['groups' => ['Default', 'create']]),
        new Get(security: "is_granted('ROLE_USER')"),
        new Put(security: "is_granted('ROLE_USER') and id == user.getId()"),
        new Delete(security: "is_granted('ROLE_ADMIN')")
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    processor: UserDataProcessor::class
    )]
#[UniqueEntity('email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[Groups('user:write')]
    #[SerializedName('password')]
    #[Assert\NotBlank(groups: ['create'])]
    #[Assert\Length(
        min: 6,
        max: 20,
        minMessage: 'Password must be at least {{ limit }} characters long',
        maxMessage: 'Password cannot be longer than {{ limit }} characters',
    )]
    #[Assert\Regex(
        pattern: '/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
        message: 'Password should contain at least one small letter, big letter and digit',
    )]
    private ?string $plainPassword = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write', 'player:read'])]
    #[Assert\NotBlank]
    private ?string $Name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write', 'player:read'])]
    #[Assert\NotBlank]
    private ?string $Surname = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\NotBlank]
    private ?string $License = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['user:read', 'user:write'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $BirthDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $Team = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Player::class)]
    #[Groups('user:read')]
    #[SerializedName('players')]
    private Collection $user;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: TrainingUnit::class)]
    private Collection $trainingUnits;

    public function __construct()
    {
        $this->user = new ArrayCollection();
        $this->trainingUnits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->Surname;
    }

    public function setSurname(string $Surname): self
    {
        $this->Surname = $Surname;

        return $this;
    }

    public function getLicense(): ?string
    {
        return $this->License;
    }

    public function setLicense(string $License): self
    {
        $this->License = $License;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->BirthDate;
    }

    public function setBirthDate(\DateTimeInterface $BirthDate): self
    {
        $this->BirthDate = $BirthDate;

        return $this;
    }

    public function getTeam(): ?string
    {
        return $this->Team;
    }

    public function setTeam(?string $Team): self
    {
        $this->Team = $Team;

        return $this;
    }

    /**
     * @return Collection<int, Player>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(Player $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user->add($user);
            $user->setUser($this);
        }

        return $this;
    }

    public function removeUser(Player $user): self
    {
        if ($this->user->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getUser() === $this) {
                $user->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TrainingUnit>
     */
    public function getTrainingUnits(): Collection
    {
        return $this->trainingUnits;
    }

    public function addTrainingUnit(TrainingUnit $trainingUnit): self
    {
        if (!$this->trainingUnits->contains($trainingUnit)) {
            $this->trainingUnits->add($trainingUnit);
            $trainingUnit->setUser($this);
        }

        return $this;
    }

    public function removeTrainingUnit(TrainingUnit $trainingUnit): self
    {
        if ($this->trainingUnits->removeElement($trainingUnit)) {
            // set the owning side to null (unless already changed)
            if ($trainingUnit->getUser() === $this) {
                $trainingUnit->setUser(null);
            }
        }

        return $this;
    }
}
