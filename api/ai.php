<?php
// api/ai.php — JSON API endpoint for Qwen AI features
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SessionService.php';
require_once __DIR__ . '/../src/Controllers/AIController.php';
SessionService::start(SessionService::ADMIN_SESSION);

header('Content-Type: application/json');

$action     = $_GET['action']      ?? '';
$electionId = (int) ($_GET['election_id'] ?? 0);

if (!$electionId) {
    echo json_encode(['success' => false, 'message' => 'election_id is required.']); exit;
}

try {
    $ctrl = new AIController();
    match ($action) {
        'analyze' => $ctrl->analyze($electionId),
        'fraud'   => $ctrl->detectFraud($electionId),
        'turnout' => $ctrl->predictTurnout($electionId),
        'summary' => $ctrl->resultsSummary($electionId),
        default   => (function() { echo json_encode(['success'=>false,'message'=>'Unknown action.']); exit; })()
    };
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}