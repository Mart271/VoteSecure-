<?php
// admin/results.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SessionService.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';
require_once __DIR__ . '/../src/Models/Election.php';
require_once __DIR__ . '/../src/Models/Candidate.php';
require_once __DIR__ . '/../src/Models/Voter.php';
require_once __DIR__ . '/../src/Models/Position.php';
SessionService::start(SessionService::ADMIN_SESSION);

$ctrl       = new AdminController();
$elModel    = new Election();
$candModel  = new Candidate();
$voterModel = new Voter();
$posModel   = new Position();

// Load all elections that have results
$allElections = $elModel->getAllWithStats();
$elections    = [];

foreach ($allElections as $el) {
    if (!in_array($el['status'], ['active','closed','published'], true)) continue;
    $elId             = $el['election_id'];
    $el['results']    = $candModel->getVoteCounts($elId);
    $el['turnout']    = $voterModel->getTurnout($elId);
    $el['positions']  = $posModel->getByElection($elId);
    $elections[]      = $el;
}

require __DIR__ . '/../views/admin/results.php';