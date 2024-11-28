<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Player;

class PlayerController extends Controller
{
    private $playerModel;

    public function __construct()
    {
        parent::__construct();
        $this->playerModel = new Player();
    }

    public function index()
    {
        $players = $this->playerModel->getAllPlayers();
        return $this->render('player/index', [
            'players' => $players,
            'title' => 'Игроки',
            'current_page' => 'players'
        ]);
    }

    public function view($id)
    {
        $player = $this->playerModel->getPlayerById($id);
        if (!$player) {
            $this->redirect('/players');
        }

        $statistics = $this->playerModel->getPlayerStatistics($id);
        $tournaments = $this->playerModel->getPlayerTournaments($id);

        return $this->render('player/view', [
            'player' => $player,
            'statistics' => $statistics,
            'tournaments' => $tournaments,
            'title' => $player['username'],
            'current_page' => 'players'
        ]);
    }

    public function profile()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        $player = $this->playerModel->getPlayerById($_SESSION['user_id']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $_SESSION['user_id'],
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'about' => $_POST['about'] ?? '',
                'country' => $_POST['country'] ?? '',
                'city' => $_POST['city'] ?? ''
            ];

            if ($this->playerModel->updateProfile($data)) {
                $_SESSION['success'] = 'Профиль успешно обновлен';
                $this->redirect('/players/profile');
            }
        }

        return $this->render('player/profile', [
            'player' => $player,
            'title' => 'Мой профиль',
            'current_page' => 'profile'
        ]);
    }

    public function rating()
    {
        $topPlayers = $this->playerModel->getTopPlayers();
        return $this->render('player/rating', [
            'players' => $topPlayers,
            'title' => 'Рейтинг игроков',
            'current_page' => 'rating'
        ]);
    }

    public function search()
    {
        $query = $_GET['q'] ?? '';
        $players = [];
        
        if ($query) {
            $players = $this->playerModel->searchPlayers($query);
        }

        return $this->render('player/search', [
            'players' => $players,
            'query' => $query,
            'title' => 'Поиск игроков',
            'current_page' => 'players'
        ]);
    }
}
