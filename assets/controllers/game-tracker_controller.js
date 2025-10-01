import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['team1Score', 'team2Score', 'playerGoals', 'winnerModal', 'winnerAnnouncement', 'finalScore'];
    static values = {
        gameId: Number
    };

    async scorePlayerGoal(event) {
        const gamePlayerId = event.currentTarget.dataset.gamePlayerId;
        const button = event.currentTarget;

        // Disable button temporarily to prevent double-clicks
        button.disabled = true;

        try {
            const response = await fetch(`/game/${this.gameIdValue}/goal/player/${gamePlayerId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error('Failed to score goal');
            }

            const data = await response.json();

            // Update team scores
            this.team1ScoreTarget.textContent = data.team1Score;
            this.team2ScoreTarget.textContent = data.team2Score;

            // Update individual player goals
            data.playerStats.forEach(player => {
                const playerGoalElement = this.playerGoalsTargets.find(
                    el => el.dataset.playerId === player.id.toString()
                );
                if (playerGoalElement) {
                    playerGoalElement.textContent = player.goals;
                }
            });

            // Check if game is completed
            if (data.status === 'completed') {
                this.showWinner(data);
            }
        } catch (error) {
            console.error('Error scoring goal:', error);
            alert('Failed to score goal. Please try again.');
        } finally {
            // Re-enable button after a short delay
            setTimeout(() => {
                button.disabled = false;
            }, 500);
        }
    }

    showWinner(data) {
        const winnerTeam = data.winnerTeam;
        const team1Score = data.team1Score;
        const team2Score = data.team2Score;

        this.winnerAnnouncementTarget.textContent = `Team ${winnerTeam} Wins!`;
        this.finalScoreTarget.textContent = `Final Score: ${team1Score} - ${team2Score}`;
        this.winnerModalTarget.style.display = 'flex';
    }
}
