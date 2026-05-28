<?php
// voter/portal.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SessionService.php';
require_once __DIR__ . '/../src/Controllers/VoterController.php';
SessionService::start(SessionService::VOTER_SESSION);

$ctrl = new VoterController();
$data = $ctrl->portal();
require __DIR__ . '/../views/voter/portal.php';