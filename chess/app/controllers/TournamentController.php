<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Tournament;

class TournamentController extends Controller
{
    private $tournamentModel;

    public function __construct()
    {
        parent::__construct();
        $this->tournamentModel = new Tournament();
    }

    public function index()
    {
        $tournaments = $this->tournamentModel->getAllTournaments();
        return $this->render('tournament/index', [
            'tournaments' => $tournaments,
            'title' => 'Турниры',
            'current_page' => 'tournaments'
        ]);
    }

    public function create()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'start_date' => $_POST['start_date'] ?? '',
                'end_date' => $_POST['end_date'] ?? '',
                'max_participants' => $_POST['max_participants'] ?? 0,
                'created_by' => $_SESSION['user_id']
            ];

            if ($this->tournamentModel->createTournament($data)) {
                $this->redirect('/tournaments');
            }
        }

        return $this->render('tournament/create', [
            'title' => 'Создать турнир',
            'current_page' => 'tournaments'
        ]);
    }

    public function view($id)
    {
        $tournament = $this->tournamentModel->getTournamentById($id);
        if (!$tournament) {
            $this->redirect('/tournaments');
        }

        return $this->render('tournament/view', [
            'tournament' => $tournament,
            'title' => $tournament['title'],
            'current_page' => 'tournaments'
        ]);
    }

    public function edit($id)
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        $tournament = $this->tournamentModel->getTournamentById($id);
        if (!$tournament || $tournament['created_by'] !== $_SESSION['user_id']) {
            $this->redirect('/tournaments');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $id,
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'start_date' => $_POST['start_date'] ?? '',
                'end_date' => $_POST['end_date'] ?? '',
                'max_participants' => $_POST['max_participants'] ?? 0
            ];

            if ($this->tournamentModel->updateTournament($data)) {
                $this->redirect('/tournaments/view/' . $id);
            }
        }

        return $this->render('tournament/edit', [
            'tournament' => $tournament,
            'title' => 'Редактировать турнир',
            'current_page' => 'tournaments'
        ]);
    }

    public function delete($id)
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        $tournament = $this->tournamentModel->getTournamentById($id);
        if (!$tournament || $tournament['created_by'] !== $_SESSION['user_id']) {
            $this->redirect('/tournaments');
        }

        if ($this->tournamentModel->deleteTournament($id)) {
            $this->redirect('/tournaments');
        }
    }
}
