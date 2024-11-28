<?php

namespace App\Controllers;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        // Добавляем базовые данные для всех представлений
        $data['current_page'] = $this->getCurrentPage();
        
        extract($data);
        
        $viewPath = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View {$view} not found");
        }
        
        require $viewPath;
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function json($data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function getCurrentPage(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        $path = parse_url($uri, PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        return $segments[0] ?? 'home';
    }
}
