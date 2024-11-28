<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Tournament;
use App\Models\Quest;

class DashboardController extends Controller {
    private User $userModel;
    private Tournament $tournamentModel;
    private Quest $questModel;

    public function __construct() {
        $this->requireAuth();
        $this->userModel = new User();
        $this->tournamentModel = new Tournament();
        $this->questModel = new Quest();
    }

    public function index(): void {
        $userId = $this->getCurrentUserId();
        $user = $this->userModel->read($userId);
        
        if (!$user) {
            $this->redirect('logout');
            return;
        }

        $data = [
            'user' => $user,
            'stats' => $this->userModel->getTournamentStats($userId),
            'quests' => $this->userModel->getQuestProgress($userId),
            'tournaments' => $this->tournamentModel->getUpcomingTournaments(),
            'activities' => $this->getRecentActivities($userId)
        ];

        $this->view('dashboard/index', $data);
    }

    public function profile(): void {
        $userId = $this->getCurrentUserId();
        $user = $this->userModel->read($userId);

        if ($this->isPost()) {
            if (!$this->validateCSRF()) {
                $this->json(['error' => 'Invalid CSRF token']);
                return;
            }

            $data = $this->getPostData();
            
            // Validate and update profile
            $updateResult = $this->userModel->update($userId, $data);
            
            if ($updateResult) {
                $this->json(['success' => true, 'message' => 'Профиль обновлен']);
            } else {
                $this->json(['error' => 'Ошибка при обновлении профиля']);
            }
        } else {
            $this->view('dashboard/profile', [
                'user' => $user,
                'csrf_token' => $this->generateCSRFToken()
            ]);
        }
    }

    private function getRecentActivities(int $userId): array {
        $activities = [];

        // Get tournament activities
        $tournamentResults = $this->tournamentModel->getRecentResults($userId, 5);
        foreach ($tournamentResults as $result) {
            $activities[] = [
                'type' => 'tournament',
                'description' => "Участие в турнире \"{$result['tournament_name']}\"",
                'date' => $result['created_at']
            ];
        }

        // Get quest activities
        $questResults = $this->questModel->getRecentCompletions($userId, 5);
        foreach ($questResults as $result) {
            $activities[] = [
                'type' => 'quest',
                'description' => "Выполнен квест \"{$result['quest_name']}\"",
                'date' => $result['completed_at']
            ];
        }

        // Sort activities by date
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($activities, 0, 5);
    }

    public function stats(): void {
        $userId = $this->getCurrentUserId();
        
        $data = [
            'tournament_stats' => $this->tournamentModel->getDetailedStats($userId),
            'quest_stats' => $this->questModel->getDetailedStats($userId),
            'rating_history' => $this->userModel->getRatingHistory($userId),
            'achievements' => $this->userModel->getAchievements($userId)
        ];

        if ($this->isGet() && isset($_GET['format']) && $_GET['format'] === 'json') {
            $this->json($data);
        } else {
            $this->view('dashboard/stats', $data);
        }
    }
}
