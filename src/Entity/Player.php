<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(targetEntity: GamePlayer::class, mappedBy: 'player')]
    private Collection $gamePlayers;

    public function __construct()
    {
        $this->gamePlayers = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
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
            $gamePlayer->setPlayer($this);
        }
        return $this;
    }

    public function removeGamePlayer(GamePlayer $gamePlayer): static
    {
        if ($this->gamePlayers->removeElement($gamePlayer)) {
            if ($gamePlayer->getPlayer() === $this) {
                $gamePlayer->setPlayer(null);
            }
        }
        return $this;
    }

    // Calculated statistics
    public function getTotalGames(): int
    {
        return $this->gamePlayers->filter(fn($gp) => $gp->getGame()->getStatus() === 'completed')->count();
    }

    public function getGamesWon(): int
    {
        return $this->gamePlayers->filter(function($gp) {
            $game = $gp->getGame();
            return $game->getStatus() === 'completed' &&
                   (($gp->getTeam() === 1 && $game->getWinnerTeam() === 1) ||
                    ($gp->getTeam() === 2 && $game->getWinnerTeam() === 2));
        })->count();
    }

    public function getGamesLost(): int
    {
        return $this->getTotalGames() - $this->getGamesWon();
    }

    public function getTotalGoalsScored(): int
    {
        return $this->gamePlayers->reduce(fn($total, $gp) => $total + $gp->getGoalsScored(), 0);
    }

    public function getWinRate(): float
    {
        $total = $this->getTotalGames();
        return $total > 0 ? ($this->getGamesWon() / $total) * 100 : 0;
    }
}
