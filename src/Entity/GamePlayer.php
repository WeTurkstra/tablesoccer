<?php

namespace App\Entity;

use App\Repository\GamePlayerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GamePlayerRepository::class)]
class GamePlayer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'gamePlayers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'gamePlayers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Player $player = null;

    #[ORM\Column]
    private int $team = 0;

    #[ORM\Column]
    private int $goalsScored = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;
        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;
        return $this;
    }

    public function getTeam(): int
    {
        return $this->team;
    }

    public function setTeam(int $team): static
    {
        $this->team = $team;
        return $this;
    }

    public function getGoalsScored(): int
    {
        return $this->goalsScored;
    }

    public function setGoalsScored(int $goalsScored): static
    {
        $this->goalsScored = $goalsScored;
        return $this;
    }

    public function incrementGoalsScored(): void
    {
        $this->goalsScored++;
    }
}
