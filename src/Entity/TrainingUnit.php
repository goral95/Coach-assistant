<?php

namespace App\Entity;

use App\Repository\TrainingUnitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrainingUnitRepository::class)]
class TrainingUnit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $topic = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $warmPart = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $firstMainPart = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $secondMainPart = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $endPart = null;

    #[ORM\ManyToOne(inversedBy: 'trainingUnits')]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: Player::class, inversedBy: 'completedTrainings')]
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
