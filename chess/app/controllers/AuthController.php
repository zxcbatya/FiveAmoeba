<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use Exception;

class AuthController extends Controller {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * @throws Exception
     */
    public function login(): void {
        if ($this->isAuthenticated()) {
            $this->redirect('dashboard');
        }

        if ($this->isPost()) {
            if (!$this->validateCSRF()) {
                $this->view('auth/login', ['error' => 'Invalid CSRF token']);
                return;
            }

            $data = $this->getPostData();
            $user = $this->userModel->findByEmail($data['email']);

            if ($user && $this->userModel->verifyPassword($data['password'], $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                
                $this->redirect('dashboard');
            } else {
                $this->view('auth/login', ['error' => 'Неверный email или пароль']);
            }
        } else {
            $this->view('auth/login', ['csrf_token' => $this->generateCSRFToken()]);
        }
    }

    /**
     * @throws Exception
     */
    public function register(): void {
        if ($this->isAuthenticated()) {
            $this->redirect('dashboard');
        }

        if ($this->isPost()) {
            if (!$this->validateCSRF()) {
                $this->view('auth/register', ['error' => 'Invalid CSRF token']);
                return;
            }

            $data = $this->getPostData();
            
            // Validate required fields
            $requiredFields = ['full_name', 'email', 'password'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $this->view('auth/register', [
                        'error' => 'Все обязательные поля должны быть заполнены',
                        'data' => $data
                    ]);
                    return;
                }
            }

            // Check if email already exists
            if ($this->userModel->findByEmail($data['email'])) {
                $this->view('auth/register', [
                    'error' => 'Этот email уже зарегистрирован',
                    'data' => $data
                ]);
                return;
            }

            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            // Create user
            $userId = $this->userModel->create($data);
            if ($userId) {
                $_SESSION['success'] = 'Регистрация успешно завершена!';
                $this->redirect('login');
            } else {
                $this->view('auth/register', [
                    'error' => 'Ошибка при регистрации',
                    'data' => $data
                ]);
            }
        } else {
            $this->view('auth/register', ['csrf_token' => $this->generateCSRFToken()]);
        }
    }

    public function logout(): void {
        session_destroy();
        $this->redirect('login');
    }
}
