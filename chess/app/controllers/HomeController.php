<?php

namespace App\Controllers;

class HomeController extends Controller
{
    public function index(): void
    {
        // Получаем последние турниры
        $latestTournaments = [];  // TODO: Implement tournaments retrieval
        
        // Получаем топ игроков
        $topPlayers = [];  // TODO: Implement top players retrieval
        
        $this->view('home.index', [
            'latestTournaments' => $latestTournaments,
            'topPlayers' => $topPlayers
        ]);
    }
}
