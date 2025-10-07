<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\GamePlayer;
use App\Entity\Player;
use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(GameRepository $gameRepository): Response
    {
        $inProgressGames = $gameRepository->findInProgressGames();
        $completedGames = $gameRepository->findCompletedGames();

        return $this->render('game/index.html.twig', [
            'inProgressGames' => $inProgressGames,
            'completedGames' => array_slice($completedGames, 0, 10),
        ]);
    }

    #[Route('/game/new', name: 'app_game_new')]
    public function new(): Response
    {
        return $this->render('game/new.html.twig');
    }

    #[Route('/game/start', name: 'app_game_start', methods: ['POST'])]
    public function start(
        Request $request,
        EntityManagerInterface $em,
        PlayerRepository $playerRepository
    ): Response {
        $team1Player1Name = trim($request->request->get('team1_player1'));
        $team1Player2Name = trim($request->request->get('team1_player2'));
        $team2Player1Name = trim($request->request->get('team2_player1'));
        $team2Player2Name = trim($request->request->get('team2_player2'));

        // Validate that we have 4 unique players
        $names = [$team1Player1Name, $team1Player2Name, $team2Player1Name, $team2Player2Name];
        if (count(array_filter($names)) !== 4) {
            $this->addFlash('error', 'Please provide names for all 4 players.');
            return $this->redirectToRoute('app_game_new');
        }

        if (count($names) !== count(array_unique($names))) {
            $this->addFlash('error', 'Each player must be unique.');
            return $this->redirectToRoute('app_game_new');
        }

        // Get or create players
        $team1Player1 = $this->getOrCreatePlayer($playerRepository, $em, $team1Player1Name);
        $team1Player2 = $this->getOrCreatePlayer($playerRepository, $em, $team1Player2Name);
        $team2Player1 = $this->getOrCreatePlayer($playerRepository, $em, $team2Player1Name);
        $team2Player2 = $this->getOrCreatePlayer($playerRepository, $em, $team2Player2Name);

        // Create game
        $game = new Game();

        // Create game-player associations
        $gp1 = new GamePlayer();
        $gp1->setGame($game)->setPlayer($team1Player1)->setTeam(1);
        $game->addGamePlayer($gp1);

        $gp2 = new GamePlayer();
        $gp2->setGame($game)->setPlayer($team1Player2)->setTeam(1);
        $game->addGamePlayer($gp2);

        $gp3 = new GamePlayer();
        $gp3->setGame($game)->setPlayer($team2Player1)->setTeam(2);
        $game->addGamePlayer($gp3);

        $gp4 = new GamePlayer();
        $gp4->setGame($game)->setPlayer($team2Player2)->setTeam(2);
        $game->addGamePlayer($gp4);

        $em->persist($game);
        $em->flush();

        return $this->redirectToRoute('app_game_play', ['id' => $game->getId()]);
    }

    #[Route('/game/{id}/play', name: 'app_game_play')]
    public function play(Game $game): Response
    {
        if ($game->getStatus() === 'completed') {
            return $this->redirectToRoute('app_game_view', ['id' => $game->getId()]);
        }

        return $this->render('game/play.html.twig', [
            'game' => $game,
        ]);
    }

    #[Route('/game/{id}/goal/player/{gamePlayerId}', name: 'app_game_goal_player', methods: ['POST'])]
    public function goalByPlayer(
        Game $game,
        int $gamePlayerId,
        EntityManagerInterface $em
    ): Response {
        if ($game->getStatus() !== 'in_progress') {
            return $this->json(['error' => 'Game is not in progress'], 400);
        }

        // Find the GamePlayer
        $gamePlayer = null;
        foreach ($game->getGamePlayers() as $gp) {
            if ($gp->getId() === $gamePlayerId) {
                $gamePlayer = $gp;
                break;
            }
        }

        if (!$gamePlayer) {
            return $this->json(['error' => 'Player not found in game'], 400);
        }

        // Increment player's goal count
        $gamePlayer->incrementGoalsScored();

        // Increment team score
        if ($gamePlayer->getTeam() === 1) {
            $game->incrementTeam1Score();
        } else {
            $game->incrementTeam2Score();
        }

        $em->flush();

        // Build response with all player stats
        $playerStats = [];
        foreach ($game->getGamePlayers() as $gp) {
            $playerStats[] = [
                'id' => $gp->getId(),
                'playerId' => $gp->getPlayer()->getId(),
                'playerName' => $gp->getPlayer()->getName(),
                'team' => $gp->getTeam(),
                'goals' => $gp->getGoalsScored(),
            ];
        }

        return $this->json([
            'team1Score' => $game->getTeam1Score(),
            'team2Score' => $game->getTeam2Score(),
            'status' => $game->getStatus(),
            'winnerTeam' => $game->getWinnerTeam(),
            'playerStats' => $playerStats,
        ]);
    }

    #[Route('/game/{id}/goal/team/{teamNumber}', name: 'app_game_goal_team', methods: ['POST'])]
    public function goalByTeam(
        Game $game,
        int $teamNumber,
        EntityManagerInterface $em
    ): Response {
        if ($game->getStatus() !== 'in_progress') {
            return $this->json(['error' => 'Game is not in progress'], 400);
        }

        if ($teamNumber !== 1 && $teamNumber !== 2) {
            return $this->json(['error' => 'Invalid team number'], 400);
        }

        // Increment team score without attributing to any player
        if ($teamNumber === 1) {
            $game->incrementTeam1Score();
        } else {
            $game->incrementTeam2Score();
        }

        $em->flush();

        // Build response with all player stats
        $playerStats = [];
        foreach ($game->getGamePlayers() as $gp) {
            $playerStats[] = [
                'id' => $gp->getId(),
                'playerId' => $gp->getPlayer()->getId(),
                'playerName' => $gp->getPlayer()->getName(),
                'team' => $gp->getTeam(),
                'goals' => $gp->getGoalsScored(),
            ];
        }

        return $this->json([
            'team1Score' => $game->getTeam1Score(),
            'team2Score' => $game->getTeam2Score(),
            'status' => $game->getStatus(),
            'winnerTeam' => $game->getWinnerTeam(),
            'playerStats' => $playerStats,
        ]);
    }

    #[Route('/game/{id}/view', name: 'app_game_view')]
    public function view(Game $game): Response
    {
        return $this->render('game/view.html.twig', [
            'game' => $game,
        ]);
    }

    #[Route('/game/{id}/rematch', name: 'app_game_rematch', methods: ['POST'])]
    public function rematch(Game $game, EntityManagerInterface $em): Response
    {
        // Create a new game with the same players
        $newGame = new Game();

        // Copy all players to the new game with their original teams
        foreach ($game->getGamePlayers() as $gamePlayer) {
            $newGamePlayer = new GamePlayer();
            $newGamePlayer->setGame($newGame)
                ->setPlayer($gamePlayer->getPlayer())
                ->setTeam($gamePlayer->getTeam());
            $newGame->addGamePlayer($newGamePlayer);
        }

        $em->persist($newGame);
        $em->flush();

        return $this->redirectToRoute('app_game_play', ['id' => $newGame->getId()]);
    }

    #[Route('/games/history', name: 'app_games_history')]
    public function history(GameRepository $gameRepository): Response
    {
        $games = $gameRepository->findCompletedGames();

        return $this->render('game/history.html.twig', [
            'games' => $games,
        ]);
    }

    private function getOrCreatePlayer(
        PlayerRepository $playerRepository,
        EntityManagerInterface $em,
        string $name
    ): Player {
        // Case-insensitive search
        $player = $playerRepository->createQueryBuilder('p')
            ->where('LOWER(p.name) = LOWER(:name)')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$player) {
            $player = new Player();
            $player->setName($name);
            $em->persist($player);
        }

        return $player;
    }
}
