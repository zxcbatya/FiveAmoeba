<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Training;

class TrainingController extends Controller
{
    private $trainingModel;

    public function __construct()
    {
        parent::__construct();
        $this->trainingModel = new Training();
    }

    public function index()
    {
        $type = $_GET['type'] ?? null;
        $difficulty = $_GET['difficulty'] ?? null;

        $trainings = $this->trainingModel->getTrainings($type, $difficulty);
        $types = $this->trainingModel->getTypes();
        $difficulties = $this->trainingModel->getDifficulties();

        return $this->render('training/index', [
            'trainings' => $trainings,
            'types' => $types,
            'difficulties' => $difficulties,
            'currentType' => $type,
            'currentDifficulty' => $difficulty,
            'title' => 'Тренировки',
            'current_page' => 'training'
        ]);
    }

    public function start($id)
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        $training = $this->trainingModel->getTrainingById($id);
        if (!$training) {
            $this->redirect('/training');
        }

        // Создаем новую сессию тренировки
        $sessionId = $this->trainingModel->createSession([
            'training_id' => $id,
            'user_id' => $_SESSION['user_id']
        ]);

        return $this->render('training/start', [
            'training' => $training,
            'sessionId' => $sessionId,
            'title' => $training['title'],
            'current_page' => 'training'
        ]);
    }

    public function submit($sessionId)
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'session_id' => $sessionId,
                'user_id' => $_SESSION['user_id'],
                'moves' => $_POST['moves'] ?? [],
                'time_spent' => $_POST['time_spent'] ?? 0,
                'completed' => $_POST['completed'] ?? false
            ];

            if ($this->trainingModel->submitSession($data)) {
                $_SESSION['success'] = 'Тренировка успешно завершена';
                $this->redirect('/training/results/' . $sessionId);
            }
        }

        $this->redirect('/training');
    }

    public function results($sessionId)
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        $session = $this->trainingModel->getSessionResults($sessionId, $_SESSION['user_id']);
        if (!$session) {
            $this->redirect('/training');
        }

        return $this->render('training/results', [
            'session' => $session,
            'title' => 'Результаты тренировки',
            'current_page' => 'training'
        ]);
    }

    public function progress()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        $progress = $this->trainingModel->getUserProgress($_SESSION['user_id']);
        $statistics = $this->trainingModel->getUserStatistics($_SESSION['user_id']);

        return $this->render('training/progress', [
            'progress' => $progress,
            'statistics' => $statistics,
            'title' => 'Мой прогресс',
            'current_page' => 'training'
        ]);
    }
}
