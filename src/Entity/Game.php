<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $playedAt = null;

    #[ORM\Column(length: 20)]
    private string $status = 'in_progress';

    #[ORM\Column(nullable: true)]
    private ?int $winnerTeam = null;

    #[ORM\Column]
    private int $team1Score = 0;

    #[ORM\Column]
    private int $team2Score = 0;

    #[ORM\Column(nullable: true)]
    private ?int $durationSeconds = null;

    #[ORM\OneToMany(targetEntity: GamePlayer::class, mappedBy: 'game', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $gamePlayers;

    public function __construct()
    {
        $this->gamePlayers = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setPlayedAtValue(): void
    {
        if ($this->playedAt === null) {
            $this->playedAt = new \DateTimeImmutable();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayedAt(): ?\DateTimeImmutable
    {
        return $this->playedAt;
    }

    public function setPlayedAt(\DateTimeImmutable $playedAt): static
    {
        $this->playedAt = $playedAt;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getWinnerTeam(): ?int
    {
        return $this->winnerTeam;
    }

    public function setWinnerTeam(?int $winnerTeam): static
    {
        $this->winnerTeam = $winnerTeam;
        return $this;
    }

    public function getTeam1Score(): int
    {
        return $this->team1Score;
    }

    public function setTeam1Score(int $team1Score): static
    {
        $this->team1Score = $team1Score;
        return $this;
    }

    public function getTeam2Score(): int
    {
        return $this->team2Score;
    }

    public function setTeam2Score(int $team2Score): static
    {
        $this->team2Score = $team2Score;
        return $this;
    }

    public function getDurationSeconds(): ?int
    {
        return $this->durationSeconds;
    }

    public function setDurationSeconds(?int $durationSeconds): static
    {
        $this->durationSeconds = $durationSeconds;
        return $this;
    }

    /**
     * @return Collection<int, GamePlayer>
     */
    public function getGamePlayers(): Collection
    {
        return $this->gamePlayers;
    }

    public function addGamePlayer(GamePlayer $gamePlayer): static
    {
        if (!$this->gamePlayers->contains($gamePlayer)) {
            $this->gamePlayers->add($gamePlayer);
            $gamePlayer->setGame($this);
        }
        return $this;
    }

    public function removeGamePlayer(GamePlayer $gamePlayer): static
    {
        if ($this->gamePlayers->removeElement($gamePlayer)) {
            if ($gamePlayer->getGame() === $this) {
                $gamePlayer->setGame(null);
            }
        }
        return $this;
    }

    public function getTeam1Players(): array
    {
        return $this->gamePlayers->filter(fn($gp) => $gp->getTeam() === 1)->toArray();
    }

    public function getTeam2Players(): array
    {
        return $this->gamePlayers->filter(fn($gp) => $gp->getTeam() === 2)->toArray();
    }

    public function incrementTeam1Score(): void
    {
        $this->team1Score++;
        $this->checkWinCondition();
    }

    public function incrementTeam2Score(): void
    {
        $this->team2Score++;
        $this->checkWinCondition();
    }

    private function checkWinCondition(): void
    {
        if ($this->team1Score >= 10) {
            $this->status = 'completed';
            $this->winnerTeam = 1;
        } elseif ($this->team2Score >= 10) {
            $this->status = 'completed';
            $this->winnerTeam = 2;
        }
    }
}
