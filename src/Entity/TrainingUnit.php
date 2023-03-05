<?php

namespace App\Entity;

use App\Entity\User;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\TrainingUnitRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: TrainingUnitRepository::class)]
#[ORM\EntityListeners(["App\Doctrine\TrainingUnitUserListener"])]
#[ApiResource(
    shortName: 'Trainings', 
    operations: [
        new Post(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_USER') and object.getUser() == user",
                securityMessage: 'Only owner can read training unit'),
        new Put(security: "is_granted('ROLE_USER') and object.getUser() == user",
                securityMessage: 'Only owner can edit training unit',
                denormalizationContext: ['groups' => ['training:edit']]),
        new Delete(security: "is_granted('ROLE_USER') and object.getUser() == user",
                securityMessage: 'Only owner can delete training unit')
    ],
    normalizationContext: ['groups' => ['training:read']],
    denormalizationContext: ['groups' => ['training:write']],
    )]
#[ApiResource(
    shortName: 'Trainings', 
    uriTemplate: '/trainings/{id}/attendance',
    operations: [
        new Get(security: "is_granted('ROLE_USER') and object.getUser() == user",
                securityMessage: 'Only owner can read training unit'),
    ],
    normalizationContext: ['groups' => ['training:attendance:read']] ,
    )]
#[ApiResource(
    shortName: 'Trainings', 
    uriTemplate: '/users/{id}/trainings.{_format}',
    uriVariables: [
        'id' => new Link(
            fromClass: User::class, 
            fromProperty: 'trainingUnits'           
        )        
    ],
    operations: [new GetCollection(
                    security: "is_granted('ROLE_USER') and id == user.getId()",
                    securityMessage: 'Only owner can watch players',
                    order: ['date'])
                ],
    normalizationContext: ['groups' => ['user:trainings:collection:read']],
                
    )]
#[ApiFilter(DateFilter::class, properties: ['date'])]
class TrainingUnit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['training:read', 'training:write', 'training:edit', 'user:trainings:collection:read'])]
    #[Assert\NotBlank]
    private ?string $topic = null;

    #[ORM\Column]
    #[Groups(['training:read', 'training:write', 'training:edit', 'user:trainings:collection:read'])]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Assert\DivisibleBy(
        value: 5,
        message: 'Training duration must be a multiple of {{ compared_value }}.',
    )]
    private ?int $duration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'])]
    #[Groups(['training:read', 'training:write', 'training:edit', 'user:trainings:collection:read'])]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['training:read', 'training:write', 'training:edit'])]
    #[Assert\NotBlank]
    private ?string $warmPart = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['training:read', 'training:write', 'training:edit'])]
    #[Assert\NotBlank]
    private ?string $firstMainPart = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['training:read', 'training:write', 'training:edit'])]
    private ?string $secondMainPart = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['training:read', 'training:write', 'training:edit'])]
    #[Assert\NotBlank]
    private ?string $endPart = null;

    #[ORM\ManyToOne(inversedBy: 'trainingUnits')]
    #[Groups(['training:read'])]
    #[SerializedName('coach')]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: Player::class, inversedBy: 'completedTrainings')]
    #[OrderBy(["name" => "ASC", "surname" => "ASC"])]
    #[Groups(['training:edit', 'training:attendance:read'])]
    private Collection $playersAttendanceList;

    public function __construct()
    {
        $this->playersAttendanceList = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTopic(): ?string
    {
        return $this->topic;
    }

    public function setTopic(string $topic): self
    {
        $this->topic = $topic;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getWarmPart(): ?string
    {
        return $this->warmPart;
    }

    public function setWarmPart(string $warmPart): self
    {
        $this->warmPart = $warmPart;

        return $this;
    }

    public function getFirstMainPart(): ?string
    {
        return $this->firstMainPart;
    }

    public function setFirstMainPart(string $firstMainPart): self
    {
        $this->firstMainPart = $firstMainPart;

        return $this;
    }

    public function getSecondMainPart(): ?string
    {
        return $this->secondMainPart;
    }

    public function setSecondMainPart(?string $secondMainPart): self
    {
        $this->secondMainPart = $secondMainPart;

        return $this;
    }

    public function getEndPart(): ?string
    {
        return $this->endPart;
    }

    public function setEndPart(string $endPart): self
    {
        $this->endPart = $endPart;

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
     * @return Collection<int, Player>
     */
    public function getPlayersAttendanceList(): Collection
    {
        return $this->playersAttendanceList;
    }

    public function addPlayersAttendanceList(Player $playersAttendanceList): self
    {
        if (!$this->playersAttendanceList->contains($playersAttendanceList)) {
            $this->playersAttendanceList->add($playersAttendanceList);
        }

        return $this;
    }

    public function removePlayersAttendanceList(Player $playersAttendanceList): self
    {
        $this->playersAttendanceList->removeElement($playersAttendanceList);

        return $this;
    }
}
