<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Quest;

class QuestController extends Controller
{
    private $questModel;

    public function __construct()
    {
        parent::__construct();
        $this->questModel = new Quest();
    }

    public function index()
    {
        $status = $_GET['status'] ?? null;
        $category = $_GET['category'] ?? null;

        $quests = $this->questModel->getQuests($status, $category);
        $categories = $this->questModel->getCategories();
        $userProgress = null;

        if ($this->isAuthenticated()) {
            $userProgress = $this->questModel->getUserProgress($_SESSION['user_id']);
        }

        return $this->render('quest/index', [
            'quests' => $quests,
            'categories' => $categories,
            'userProgress' => $userProgress,
            'currentStatus' => $status,
            'currentCategory' => $category,
            'title' => 'Квесты',
            'current_page' => 'quests'
        ]);
    }

    public function start($id)
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        $quest = $this->questModel->getQuestById($id);
        if (!$quest) {
            $this->redirect('/quests');
        }

        // Проверяем, не начат ли уже этот квест
        if ($this->questModel->isQuestStarted($_SESSION['user_id'], $id)) {
            $this->redirect('/quests/continue/' . $id);
        }

        // Начинаем квест
        if ($this->questModel->startQuest($_SESSION['user_id'], $id)) {
            $this->redirect('/quests/continue/' . $id);
        }

        $this->redirect('/quests');
    }

    public function continue($id)
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        $quest = $this->questModel->getQuestById($id);
        $progress = $this->questModel->getQuestProgress($_SESSION['user_id'], $id);

        if (!$quest || !$progress) {
            $this->redirect('/quests');
        }

        return $this->render('quest/continue', [
            'quest' => $quest,
            'progress' => $progress,
            'title' => $quest['title'],
            'current_page' => 'quests'
        ]);
    }

    public function submit($id)
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'quest_id' => $id,
                'user_id' => $_SESSION['user_id'],
                'answer' => $_POST['answer'] ?? '',
                'moves' => $_POST['moves'] ?? []
            ];

            $result = $this->questModel->submitQuestStep($data);
            
            if ($result['completed']) {
                $_SESSION['success'] = 'Квест успешно завершен! Вы получили ' . $result['reward'] . ' очков.';
                $this->redirect('/quests/complete/' . $id);
            } elseif ($result['success']) {
                $_SESSION['success'] = 'Правильно! Переходим к следующему этапу.';
                $this->redirect('/quests/continue/' . $id);
            } else {
                $_SESSION['error'] = 'Неправильный ответ. Попробуйте еще раз.';
                $this->redirect('/quests/continue/' . $id);
            }
        }

        $this->redirect('/quests');
    }

    public function complete($id)
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        $quest = $this->questModel->getQuestById($id);
        $completion = $this->questModel->getQuestCompletion($_SESSION['user_id'], $id);

        if (!$quest || !$completion) {
            $this->redirect('/quests');
        }

        return $this->render('quest/complete', [
            'quest' => $quest,
            'completion' => $completion,
            'title' => 'Квест завершен',
            'current_page' => 'quests'
        ]);
    }

    public function leaderboard()
    {
        $timeframe = $_GET['timeframe'] ?? 'all';
        $category = $_GET['category'] ?? null;

        $leaderboard = $this->questModel->getLeaderboard($timeframe, $category);
        $categories = $this->questModel->getCategories();

        return $this->render('quest/leaderboard', [
            'leaderboard' => $leaderboard,
            'categories' => $categories,
            'currentTimeframe' => $timeframe,
            'currentCategory' => $category,
            'title' => 'Таблица лидеров',
            'current_page' => 'quests'
        ]);
    }
}
