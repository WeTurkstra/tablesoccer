import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['team1Score', 'team2Score', 'playerGoals', 'winnerModal', 'winnerAnnouncement', 'finalScore'];
    static values = {
        gameId: Number
    };

    wakeLock = null;

    async connect() {
        await this.requestWakeLock();

        // Re-acquire wake lock when page becomes visible again
        document.addEventListener('visibilitychange', this.handleVisibilityChange);
    }

    disconnect() {
        this.releaseWakeLock();
        document.removeEventListener('visibilitychange', this.handleVisibilityChange);
    }

    handleVisibilityChange = async () => {
        if (document.visibilityState === 'visible') {
            await this.requestWakeLock();
        }
    }

    async requestWakeLock() {
        try {
            if ('wakeLock' in navigator) {
                this.wakeLock = await navigator.wakeLock.request('screen');
                console.log('Wake lock acquired - screen will stay active');
            }
        } catch (err) {
            console.error('Failed to acquire wake lock:', err);
        }
    }

    releaseWakeLock() {
        if (this.wakeLock) {
            this.wakeLock.release();
            this.wakeLock = null;
            console.log('Wake lock released');
        }
    }

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

    async scoreTeamGoal(event) {
        const teamNumber = event.currentTarget.dataset.teamNumber;
        const button = event.currentTarget;

        // Disable button temporarily to prevent double-clicks
        button.disabled = true;

        try {
            const response = await fetch(`/game/${this.gameIdValue}/goal/team/${teamNumber}`, {
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

            // Update individual player goals (no changes but refresh from server)
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
