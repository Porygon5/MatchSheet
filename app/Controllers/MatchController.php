<?php

require_once __DIR__ . '/../Models/MatchModel.php';

class MatchController
{
    private $model;

    public function __construct($pdo)
    {
        $this->model = new MatchModel($pdo);
    }

    public function index()
    {
        $matchs = $this->model->getAll();
        require __DIR__ . '/../Views/feuilles_match.php';
    }
}
