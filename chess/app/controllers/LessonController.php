<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Lesson;

class LessonController extends Controller
{
    private $lessonModel;

    public function __construct()
    {
        parent::__construct();
        $this->lessonModel = new Lesson();
    }

    public function index()
    {
        $category = $_GET['category'] ?? null;
        $level = $_GET['level'] ?? null;
        
        $lessons = $this->lessonModel->getLessons($category, $level);
        $categories = $this->lessonModel->getCategories();
        $levels = $this->lessonModel->getLevels();

        return $this->render('lesson/index', [
            'lessons' => $lessons,
            'categories' => $categories,
            'levels' => $levels,
            'currentCategory' => $category,
            'currentLevel' => $level,
            'title' => 'Уроки',
            'current_page' => 'lessons'
        ]);
    }

    public function view($id)
    {
        $lesson = $this->lessonModel->getLessonById($id);
        if (!$lesson) {
            $this->redirect('/lessons');
        }

        // Если урок платный и пользователь не имеет доступа
        if ($lesson['is_premium'] && (!isset($_SESSION['user_id']) || !$this->lessonModel->hasAccess($_SESSION['user_id'], $id))) {
            $_SESSION['error'] = 'Для доступа к этому уроку необходима премиум подписка';
            $this->redirect('/lessons');
        }

        // Отметить урок как просмотренный для авторизованного пользователя
        if (isset($_SESSION['user_id'])) {
            $this->lessonModel->markAsViewed($_SESSION['user_id'], $id);
        }

        return $this->render('lesson/view', [
            'lesson' => $lesson,
            'title' => $lesson['title'],
            'current_page' => 'lessons'
        ]);
    }

    public function create()
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect('/lessons');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'category_id' => $_POST['category_id'] ?? null,
                'level' => $_POST['level'] ?? 'beginner',
                'is_premium' => isset($_POST['is_premium']),
                'created_by' => $_SESSION['user_id']
            ];

            if ($this->lessonModel->createLesson($data)) {
                $_SESSION['success'] = 'Урок успешно создан';
                $this->redirect('/lessons');
            }
        }

        $categories = $this->lessonModel->getCategories();
        $levels = $this->lessonModel->getLevels();

        return $this->render('lesson/create', [
            'categories' => $categories,
            'levels' => $levels,
            'title' => 'Создать урок',
            'current_page' => 'lessons'
        ]);
    }

    public function edit($id)
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect('/lessons');
        }

        $lesson = $this->lessonModel->getLessonById($id);
        if (!$lesson) {
            $this->redirect('/lessons');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $id,
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'category_id' => $_POST['category_id'] ?? null,
                'level' => $_POST['level'] ?? 'beginner',
                'is_premium' => isset($_POST['is_premium'])
            ];

            if ($this->lessonModel->updateLesson($data)) {
                $_SESSION['success'] = 'Урок успешно обновлен';
                $this->redirect('/lessons/view/' . $id);
            }
        }

        $categories = $this->lessonModel->getCategories();
        $levels = $this->lessonModel->getLevels();

        return $this->render('lesson/edit', [
            'lesson' => $lesson,
            'categories' => $categories,
            'levels' => $levels,
            'title' => 'Редактировать урок',
            'current_page' => 'lessons'
        ]);
    }

    public function delete($id)
    {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            $this->redirect('/lessons');
        }

        if ($this->lessonModel->deleteLesson($id)) {
            $_SESSION['success'] = 'Урок успешно удален';
        }

        $this->redirect('/lessons');
    }
}
