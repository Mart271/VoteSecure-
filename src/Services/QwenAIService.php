<?php
// src/Services/QwenAIService.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/Candidate.php';
require_once __DIR__ . '/../Models/Voter.php';
require_once __DIR__ . '/../Models/Ballot.php';
require_once __DIR__ . '/../Models/Election.php';
require_once __DIR__ . '/../Models/AuditLog.php';

class QwenAIService
{
    private string   $apiKey;
    private string   $apiUrl;
    private string   $model;
    private AuditLog $audit;

    public function __construct()
    {
        $this->apiKey = QWEN_API_KEY;
        $this->apiUrl = QWEN_API_URL;
        $this->model  = QWEN_MODEL;
        $this->audit  = new AuditLog();
    }

    public function analyzeElection(int $electionId, ?int $userId = null): array
    {
        $data = $this->buildElectionPayload($electionId);
        if (!$data) return $this->errorResult('Election not found.');
        $prompt = $this->buildAnalysisPrompt($data);
        $result = $this->callQwen($prompt);
        $parsed = $this->parseJson($result);
        $this->audit->log('AI_ANALYSIS', "AI election analytics generated for Election #{$electionId}.", $userId);
        return ['success' => true, 'analysis' => $parsed, 'data' => $data];
    }

    public function detectFraud(int $electionId, ?int $userId = null): array
    {
        $data = $this->buildFraudPayload($electionId);
        $prompt = <<<PROMPT
You are a voting fraud detection expert for an election management system.

Analyze the following voting data for anomalies and potential fraud indicators:

Election: {$data['election_title']}
- Total registered voters: {$data['total_voters']}
- Total votes cast: {$data['total_voted']}
- Voter turnout: {$data['turnout_pct']}%
- Unique IP addresses used: {$data['unique_ips']}
- Votes per hour breakdown: {$data['hourly_distribution']}
- Multiple votes from same subnet: {$data['subnet_clusters']}

Provide your analysis in this exact JSON format:
{
  "risk_level": "LOW|MEDIUM|HIGH",
  "risk_score": 0-100,
  "anomalies": ["list of detected anomalies"],
  "flags": ["specific red flags if any"],
  "recommendations": ["action items for election admin"],
  "summary": "one paragraph plain-language summary"
}
PROMPT;
        $result = $this->callQwen($prompt);
        $parsed = $this->parseJson($result);
        $this->audit->log('AI_FRAUD_DETECTION', "AI fraud detection run for Election #{$electionId}. Risk: " . ($parsed['risk_level'] ?? 'N/A'), $userId);
        return ['success' => true, 'fraud_report' => $parsed, 'raw' => $result];
    }

    public function predictTurnout(int $electionId, ?int $userId = null): array
    {
        $election = (new Election())->getWithStats($electionId);
        if (!$election) return $this->errorResult('Election not found.');
        $historicalStats = (new Voter())->getAllVoterStats();
        $prompt = <<<PROMPT
You are a political data analyst. Predict voter turnout for an upcoming election.

Election details:
- Title: {$election['title']}
- Scheduled: {$election['start_datetime']} to {$election['end_datetime']}
- Registered voters: {$election['voter_count']}
- Type: Organizational / School election
- Historical avg turnout in this system: {$historicalStats['total_voters']} registered, {$historicalStats['total_voted']} voted

Respond in JSON:
{
  "predicted_turnout_pct": 0-100,
  "confidence": "LOW|MEDIUM|HIGH",
  "predicted_votes": number,
  "key_factors": ["factors affecting turnout"],
  "strategies_to_boost": ["actionable recommendations to increase participation"],
  "summary": "paragraph summary"
}
PROMPT;
        $result = $this->callQwen($prompt);
        $parsed = $this->parseJson($result);
        $this->audit->log('AI_TURNOUT_PREDICTION', "Turnout prediction for Election #{$electionId}.", $userId);
        return ['success' => true, 'prediction' => $parsed, 'raw' => $result];
    }

    public function generateResultsSummary(int $electionId, ?int $userId = null): array
    {
        $candidateModel = new Candidate();
        $voterModel     = new Voter();
        $election       = (new Election())->findById($electionId);
        if (!$election) return $this->errorResult('Election not found.');
        $results     = $candidateModel->getVoteCounts($electionId);
        $turnout     = $voterModel->getTurnout($electionId);
        $resultsText = '';
        $currentPos  = '';
        foreach ($results as $r) {
            if ($r['position_name'] !== $currentPos) {
                $currentPos   = $r['position_name'];
                $resultsText .= "\n{$currentPos}:\n";
            }
            $resultsText .= "  - {$r['full_name']} ({$r['party_affiliation']}): {$r['vote_count']} votes\n";
        }
        $prompt = <<<PROMPT
You are an election results analyst. Write a concise, professional, and neutral summary of the following election results.

Election: {$election['title']}
Date: {$election['start_datetime']}
Voter turnout: {$turnout['voted']} of {$turnout['total']} ({$turnout['pct']}%)

Results by position:
{$resultsText}

Write a 3-5 paragraph official results summary suitable for public announcement. Include:
1. Overview of the election
2. Winner for each position and their vote share
3. Notable observations (close races, landslide wins, etc.)
4. Voter participation assessment
Keep it factual, neutral, and professional.
PROMPT;
        $summary = $this->callQwen($prompt);
        $this->audit->log('AI_RESULTS_SUMMARY', "AI results summary for Election #{$electionId}.", $userId);
        return ['success' => true, 'summary' => $summary];
    }

    // ─────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────

 private function callQwen(string $prompt): string
{
    $payload = json_encode([
        'model'    => $this->model,
        'messages' => [
            ['role' => 'system', 'content' => 'You are VoteSecure AI, an expert election analytics assistant. Be precise, data-driven, and professional.'],
            ['role' => 'user',   'content' => $prompt],
        ],
        'max_tokens'  => 1500,
        'temperature' => 0.3,
    ]);

    $ch = curl_init($this->apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) return "AI service unavailable: {$error}";
    if ($httpCode !== 200) return "AI service returned HTTP {$httpCode}. Response: {$response}";

    $data = json_decode($response, true);
    return $data['choices'][0]['message']['content'] ?? 'No response from AI.';
}

    private function buildElectionPayload(int $electionId): ?array
    {
        $election = (new Election())->getWithStats($electionId);
        if (!$election) return null;
        $results  = (new Candidate())->getVoteCounts($electionId);
        $turnout  = (new Voter())->getTurnout($electionId);
        return [
            'election_title' => $election['title'],
            'total_voters'   => $turnout['total'],
            'total_voted'    => $turnout['voted'],
            'turnout_pct'    => $turnout['pct'],
            'results'        => $results,
        ];
    }

    private function buildAnalysisPrompt(array $data): string
    {
        $resultsText = '';
        foreach ($data['results'] as $r) {
            $resultsText .= "  {$r['position_name']} — {$r['full_name']} ({$r['party_affiliation']}): {$r['vote_count']} votes\n";
        }
        return <<<PROMPT
Analyze the following election data and provide comprehensive analytics.

Election: {$data['election_title']}
Turnout: {$data['total_voted']}/{$data['total_voters']} ({$data['turnout_pct']}%)

Results:
{$resultsText}

Respond in JSON format:
{
  "turnout_assessment": "Low|Moderate|High",
  "overall_summary": "brief paragraph",
  "position_insights": [
    {
      "position": "name",
      "winner": "name",
      "margin": "percentage",
      "competitiveness": "Landslide|Competitive|Very Close",
      "insight": "brief observation"
    }
  ],
  "participation_recommendations": ["list of recommendations"],
  "data_highlights": ["key stats worth noting"]
}
PROMPT;
    }

    private function buildFraudPayload(int $electionId): array
    {
        $db      = Database::getInstance();
        $turnout = (new Voter())->getTurnout($electionId);
        $ipData  = $db->query("SELECT ip_address, COUNT(*) AS cnt FROM ballots WHERE election_id = ? GROUP BY ip_address ORDER BY cnt DESC", [$electionId]);
        $hourly  = $db->query("SELECT HOUR(submitted_at) AS hr, COUNT(*) AS cnt FROM ballots WHERE election_id = ? GROUP BY hr ORDER BY hr", [$electionId]);
        $uniqueIps      = count($ipData);
        $hourlyStr      = implode(', ', array_map(fn($r) => "Hour {$r['hr']}: {$r['cnt']}", $hourly));
        $subnetClusters = array_filter($ipData, fn($r) => $r['cnt'] > 2);
        return [
            'election_title'      => "Election #{$electionId}",
            'total_voters'        => $turnout['total'],
            'total_voted'         => $turnout['voted'],
            'turnout_pct'         => $turnout['pct'],
            'unique_ips'          => $uniqueIps,
            'hourly_distribution' => $hourlyStr ?: 'No data',
            'subnet_clusters'     => count($subnetClusters) > 0
                ? implode(', ', array_map(fn($r) => "{$r['ip_address']} ({$r['cnt']} votes)", $subnetClusters))
                : 'None detected',
        ];
    }

    private function parseJson(string $text): array
    {
        $clean = trim($text);
        $clean = preg_replace('/^```(?:json)?\s*/m', '', $clean);
        $clean = preg_replace('/\s*```\s*$/m', '', $clean);
        $clean = trim($clean);

        $decoded = json_decode($clean, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{[\s\S]*\}/', $clean, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return ['raw_response' => $text];
    }

    private function errorResult(string $msg): array
    {
        return ['success' => false, 'message' => $msg];
    }
}