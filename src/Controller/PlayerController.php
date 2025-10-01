<?php

namespace App\Controller;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayerController extends AbstractController
{
    #[Route('/players', name: 'app_players')]
    public function index(PlayerRepository $playerRepository): Response
    {
        $players = $playerRepository->findAll();

        // Sort by games won (descending)
        usort($players, function(Player $a, Player $b) {
            return $b->getGamesWon() <=> $a->getGamesWon();
        });

        return $this->render('player/index.html.twig', [
            'players' => $players,
        ]);
    }

    #[Route('/player/{id}', name: 'app_player_view')]
    public function view(Player $player): Response
    {
        // Get all games for this player
        $gamePlayers = $player->getGamePlayers();
        $completedGames = [];

        foreach ($gamePlayers as $gp) {
            if ($gp->getGame()->getStatus() === 'completed') {
                $completedGames[] = $gp->getGame();
            }
        }

        // Sort by date (most recent first)
        usort($completedGames, function($a, $b) {
            return $b->getPlayedAt() <=> $a->getPlayedAt();
        });

        return $this->render('player/view.html.twig', [
            'player' => $player,
            'recentGames' => array_slice($completedGames, 0, 20),
        ]);
    }
}
