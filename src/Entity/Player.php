<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\PlayerRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
#[ORM\EntityListeners(["App\Doctrine\PlayerUserListener"])]
#[ApiResource(
    operations: [
        new Post(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_USER') and object.getUser() == user",
                securityMessage: 'Only owner can watch player'),
        new Put(security: "is_granted('ROLE_USER') and object.getUser() == user",
                securityMessage: 'Only owner can edit player'),
        new Delete(security: "is_granted('ROLE_USER') and object.getUser() == user",
                securityMessage: 'Only owner can delete player')
    ],
    normalizationContext: ['groups' => ['player:read']],
    denormalizationContext: ['groups' => ['player:write']],
    )]
#[ApiResource(
    uriTemplate: '/users/{id}/players.{_format}',
    uriVariables: [
        'id' => new Link(
            fromClass: User::class, 
            fromProperty: 'user'           
        )        
    ],
    operations: [new GetCollection(
                    security: "is_granted('ROLE_USER') and id == user.getId()",
                    securityMessage: 'Only owner can watch players'),
                new GetCollection(
                    security: "is_granted('ROLE_USER') and id == user.getId()",
                    securityMessage: 'Only owner can watch players',
                    name: 'get_name_asc', uriTemplate: '/users/{id}/players/name-asc', order: ['name', 'surname']),
                new GetCollection(
                    security: "is_granted('ROLE_USER') and id == user.getId()",
                    securityMessage: 'Only owner can watch players',
                    name: 'get_birthdate_asc', uriTemplate: '/users/{id}/players/birth-date-asc', order: ['birthDate', 'name', 'surname'])
                ]
                
)]
#[ApiFilter(DateFilter::class, properties: ['birthDate'])]
#[ApiFilter(SearchFilter::class, properties: [
    'name' => 'istart', 
    'surname' => 'istart', 
    'foot' => 'exact', 
    'position' => 'exact', 
    'city' => 'exact'
    ])]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['player:read', 'player:write', 'user:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['player:read', 'player:write', 'user:read'])]
    private ?string $surname = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[Groups(['player:read', 'player:write', 'user:read'])]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['player:read', 'player:write'])]
    private ?string $foot = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['player:read', 'player:write'])]
    private ?string $position = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['player:read', 'player:write'])]
    private ?string $city = null;

    #[ORM\ManyToOne(inversedBy: 'user')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['player:read'])]
    #[SerializedName('coach')]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: TrainingUnit::class, mappedBy: 'playersAttendanceList')]
    private Collection $completedTrainings;

    public function __construct()
    {
        $this->completedTrainings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getFoot(): ?string
    {
        return $this->foot;
    }

    public function setFoot(?string $foot): self
    {
        $this->foot = $foot;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, TrainingUnit>
     */
    public function getCompletedTrainings(): Collection
    {
        return $this->completedTrainings;
    }

    public function addCompletedTraining(TrainingUnit $completedTraining): self
    {
        if (!$this->completedTrainings->contains($completedTraining)) {
            $this->completedTrainings->add($completedTraining);
            $completedTraining->addPlayersAttendanceList($this);
        }

        return $this;
    }

    public function removeCompletedTraining(TrainingUnit $completedTraining): self
    {
        if ($this->completedTrainings->removeElement($completedTraining)) {
            $completedTraining->removePlayersAttendanceList($this);
        }

        return $this;
    }
}
