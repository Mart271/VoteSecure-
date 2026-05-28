<?php
// views/admin/ai_analytics.php
$pageTitle  = 'AI Analytics';
$activePage = 'ai';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
  <div>
    <div class="page-title">🤖 AI Analytics</div>
    <div class="page-sub">Powered by Qwen AI — election intelligence, fraud detection & insights</div>
  </div>
</div>

<!-- ELECTION SELECTOR -->
<div class="card" style="margin-bottom:24px">
  <div class="card-body" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
    <label style="font-family:'Syne',sans-serif;font-weight:700;color:var(--navy);flex-shrink:0">Select Election:</label>
    <select id="ai-election-sel" style="flex:1;min-width:240px;padding:10px 14px;border:2px solid var(--border);border-radius:8px;font-size:15px">
      <option value="">— Choose an election —</option>
      <?php foreach ($elections as $el): ?>
        <option value="<?= $el['election_id'] ?>" <?= ($selectedId ?? 0) == $el['election_id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($el['title']) ?> [<?= $el['status'] ?>]
        </option>
      <?php endforeach; ?>
    </select>
    <span style="font-family:'DM Mono',monospace;font-size:12px;color:var(--muted)">API: <?= defined('QWEN_API_KEY') && QWEN_API_KEY !== 'YOUR_QWEN_API_KEY_HERE' ? '✅ Configured' : '⚠️ Key not set' ?></span>
  </div>
</div>

<!-- AI FEATURE CARDS -->
<div class="ai-features-grid">

  <div class="ai-feature-card">
    <div class="ai-feature-icon">📊</div>
    <div class="ai-feature-title">Election Analysis</div>
    <div class="ai-feature-desc">Deep analytics on voting patterns, candidate performance, position competitiveness, and turnout assessment.</div>
    <button class="btn btn-primary" onclick="runAI('analyze')">Run Analysis</button>
  </div>

  <div class="ai-feature-card">
    <div class="ai-feature-icon">🔍</div>
    <div class="ai-feature-title">Fraud Detection</div>
    <div class="ai-feature-desc">Detect anomalies, suspicious IP clusters, unusual voting velocity, and flag potential integrity concerns.</div>
    <button class="btn btn-danger" onclick="runAI('fraud')">Detect Fraud</button>
  </div>

  <div class="ai-feature-card">
    <div class="ai-feature-icon">📈</div>
    <div class="ai-feature-title">Turnout Prediction</div>
    <div class="ai-feature-desc">Predict final voter turnout based on current participation rates and historical patterns.</div>
    <button class="btn btn-gold" onclick="runAI('turnout')">Predict Turnout</button>
  </div>

  <div class="ai-feature-card">
    <div class="ai-feature-icon">📝</div>
    <div class="ai-feature-title">Results Summary</div>
    <div class="ai-feature-desc">Auto-generate a professional, neutral official results narrative ready for publication.</div>
    <button class="btn btn-success" onclick="runAI('summary')">Generate Summary</button>
  </div>

</div>

<!-- AI RESULT PANEL -->
<div id="ai-result-panel" style="display:none;margin-top:28px">
  <div class="card">
    <div class="card-head">
      <h3 id="ai-result-title">AI Analysis Result</h3>
      <div style="display:flex;gap:10px;align-items:center">
        <span id="ai-result-time" style="font-family:'DM Mono',monospace;font-size:12px;color:var(--muted)"></span>
        <button class="btn btn-outline btn-sm" onclick="copyResult()">Copy</button>
        <button class="btn btn-outline btn-sm" onclick="closeResult()">Close</button>
      </div>
    </div>
    <div class="card-body">

      <!-- Loading spinner -->
      <div id="ai-loading" style="text-align:center;padding:48px;display:none">
        <div class="ai-spinner"></div>
        <div style="font-family:'DM Mono',monospace;font-size:14px;color:var(--muted);margin-top:16px" id="ai-loading-text">Analyzing with Qwen AI...</div>
      </div>

      <!-- Fraud Risk Badge -->
      <div id="ai-fraud-badge" style="display:none;margin-bottom:20px"></div>

      <!-- Structured JSON Result -->
      <div id="ai-structured-result" style="display:none"></div>

      <!-- Plain Text Result -->
      <div id="ai-text-result" class="ai-text-output"></div>

    </div>
  </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';

async function runAI(type) {
  const elId = document.getElementById('ai-election-sel').value;
  if (!elId) { alert('Please select an election first.'); return; }

  const titles = {
    analyze: '📊 Election Analysis',
    fraud:   '🔍 Fraud Detection Report',
    turnout: '📈 Voter Turnout Prediction',
    summary: '📝 Official Results Summary',
  };
  const loadingTexts = {
    analyze: 'Analyzing voting patterns with Qwen AI...',
    fraud:   'Scanning for anomalies and fraud indicators...',
    turnout: 'Calculating turnout predictions...',
    summary: 'Generating official results narrative...',
  };

  document.getElementById('ai-result-panel').style.display  = 'block';
  document.getElementById('ai-result-title').textContent    = titles[type];
  document.getElementById('ai-loading').style.display       = 'flex';
  document.getElementById('ai-loading').style.flexDirection = 'column';
  document.getElementById('ai-loading-text').textContent    = loadingTexts[type];
  document.getElementById('ai-structured-result').style.display = 'none';
  document.getElementById('ai-fraud-badge').style.display   = 'none';
  document.getElementById('ai-text-result').textContent     = '';

  document.getElementById('ai-result-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });

  try {
    const endpoints = {
      analyze: `${APP_URL}/api/ai.php?action=analyze&election_id=${elId}`,
      fraud:   `${APP_URL}/api/ai.php?action=fraud&election_id=${elId}`,
      turnout: `${APP_URL}/api/ai.php?action=turnout&election_id=${elId}`,
      summary: `${APP_URL}/api/ai.php?action=summary&election_id=${elId}`,
    };

    const resp = await fetch(endpoints[type]);
    const data = await resp.json();

    document.getElementById('ai-loading').style.display = 'none';
    document.getElementById('ai-result-time').textContent = new Date().toLocaleTimeString();

    if (!data.success) {
      document.getElementById('ai-text-result').textContent = '⚠ ' + (data.message || 'AI request failed.');
      return;
    }

    if (type === 'analyze' && data.analysis) {
      renderAnalysis(data.analysis);
    } else if (type === 'fraud' && data.fraud_report) {
      renderFraud(data.fraud_report);
    } else if (type === 'turnout' && data.prediction) {
      renderTurnout(data.prediction);
    } else if (type === 'summary' && data.summary) {
      renderSummary(data.summary);
    } else if (data.raw_response) {
      renderRawResult(data.raw_response);
    } else {
      document.getElementById('ai-text-result').textContent = JSON.stringify(data, null, 2);
    }

  } catch (err) {
    document.getElementById('ai-loading').style.display = 'none';
    document.getElementById('ai-text-result').textContent = '⚠ Network error: ' + err.message;
  }
}

function renderAnalysis(data) {
  if (typeof data === 'string') { renderRawResult(data); return; }
  const el = document.getElementById('ai-structured-result');
  el.style.display = 'block';
  el.innerHTML = `
    <div class="ai-section" style="padding-bottom:8px">
      <div class="ai-kv"><span class="ai-key">Turnout Assessment</span><span class="ai-val">${data.turnout_assessment ?? '—'}</span></div>
      <div class="ai-kv"><span class="ai-key">Overall Insight</span><span class="ai-val">${data.overall_summary ? escapeHtml(data.overall_summary) : '—'}</span></div>
    </div>
    ${data.data_highlights?.length ? `<div class="ai-section"><div class="ai-section-title">📌 Data Highlights</div><ul class="ai-list">${data.data_highlights.map(item => `<li>${escapeHtml(item)}</li>`).join('')}</ul></div>` : ''}
    ${(data.position_insights ?? []).map(p => `
      <div class="ai-section">
        <div class="ai-section-title">📍 ${escapeHtml(p.position)}</div>
        <div class="ai-kv"><span class="ai-key">Winner</span><span class="ai-val ai-winner">🏆 ${escapeHtml(p.winner)}</span></div>
        <div class="ai-kv"><span class="ai-key">Margin</span><span class="ai-val">${escapeHtml(p.margin)}</span></div>
        <div class="ai-kv"><span class="ai-key">Competitiveness</span><span class="ai-val">${escapeHtml(p.competitiveness)}</span></div>
        <div class="ai-kv"><span class="ai-key">Insight</span><span class="ai-val">${escapeHtml(p.insight)}</span></div>
      </div>`).join('')}
    ${data.participation_recommendations?.length ? `
      <div class="ai-section">
        <div class="ai-section-title">💡 Recommendations</div>
        <ul class="ai-list">${data.participation_recommendations.map(r => `<li>${escapeHtml(r)}</li>`).join('')}</ul>
      </div>` : ''}
  `;
  if (!data.position_insights?.length && !data.participation_recommendations?.length && !data.data_highlights?.length) {
    const raw = escapeHtml(JSON.stringify(data, null, 2));
    el.innerHTML += `<pre class="ai-raw-output">${raw}</pre>`;
  }
}

function renderFraud(data) {
  if (typeof data === 'string') { document.getElementById('ai-text-result').textContent = data; return; }
  const riskColors = { LOW: '#1e8449', MEDIUM: '#d35400', HIGH: '#c0392b' };
  const badge = document.getElementById('ai-fraud-badge');
  badge.style.display = 'block';
  badge.innerHTML = `
    <div class="fraud-risk-badge" style="border-color:${riskColors[data.risk_level] ?? '#888'}">
      <div class="fraud-risk-level" style="color:${riskColors[data.risk_level] ?? '#888'}">${data.risk_level ?? '?'} RISK</div>
      <div class="fraud-risk-score">Score: ${data.risk_score ?? '?'}/100</div>
    </div>`;

  const el = document.getElementById('ai-structured-result');
  el.style.display = 'block';
  el.innerHTML = `
    ${data.anomalies?.length ? `<div class="ai-section"><div class="ai-section-title">⚠ Detected Anomalies</div><ul class="ai-list">${data.anomalies.map(a=>`<li>${a}</li>`).join('')}</ul></div>` : ''}
    ${data.flags?.length     ? `<div class="ai-section"><div class="ai-section-title">🚩 Red Flags</div><ul class="ai-list red">${data.flags.map(f=>`<li>${f}</li>`).join('')}</ul></div>` : ''}
    ${data.recommendations?.length ? `<div class="ai-section"><div class="ai-section-title">✅ Recommendations</div><ul class="ai-list">${data.recommendations.map(r=>`<li>${r}</li>`).join('')}</ul></div>` : ''}
    <div class="ai-section"><div class="ai-key">Summary</div><div style="margin-top:8px;line-height:1.7">${data.summary ?? ''}</div></div>
  `;
}

function renderTurnout(data) {
  if (typeof data === 'string') { document.getElementById('ai-text-result').textContent = data; return; }
  const el = document.getElementById('ai-structured-result');
  el.style.display = 'block';
  el.innerHTML = `
    <div class="ai-section" style="display:flex;gap:32px;align-items:center;flex-wrap:wrap">
      <div style="text-align:center">
        <div style="font-family:'Syne',sans-serif;font-size:52px;font-weight:800;color:var(--blue)">${data.predicted_turnout_pct ?? '?'}%</div>
        <div style="font-family:'DM Mono',monospace;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:1px">Predicted Turnout</div>
      </div>
      <div>
        <div class="ai-kv"><span class="ai-key">Predicted Votes</span><span class="ai-val">${data.predicted_votes ?? '?'}</span></div>
        <div class="ai-kv"><span class="ai-key">Confidence</span><span class="ai-val">${data.confidence ?? '?'}</span></div>
        <div class="ai-kv"><span class="ai-key">Summary</span><span class="ai-val">${data.summary ?? '—'}</span></div>
      </div>
    </div>
    ${data.key_factors?.length ? `<div class="ai-section"><div class="ai-section-title">🔑 Key Factors</div><ul class="ai-list">${data.key_factors.map(f=>`<li>${f}</li>`).join('')}</ul></div>` : ''}
    ${data.strategies_to_boost?.length ? `<div class="ai-section"><div class="ai-section-title">💡 Strategies to Boost Turnout</div><ul class="ai-list">${data.strategies_to_boost.map(s=>`<li>${s}</li>`).join('')}</ul></div>` : ''}
  `;
}

function renderSummary(text) {
  const el = document.getElementById('ai-structured-result');
  el.style.display = 'block';
  document.getElementById('ai-text-result').textContent = '';
  const paragraphs = String(text).split(/\n\n+/).map(p => `<p>${escapeHtml(p)}</p>`).join('');
  el.innerHTML = `
    <div class="ai-section">
      <div class="ai-section-title">📝 Official Results Summary</div>
      <div class="ai-text-output">${paragraphs}</div>
    </div>
  `;
}

function renderRawResult(raw) {
  const el = document.getElementById('ai-structured-result');
  el.style.display = 'block';
  document.getElementById('ai-text-result').textContent = '';
  const text = typeof raw === 'string' ? raw : JSON.stringify(raw, null, 2);
  el.innerHTML = `
    <div class="ai-section">
      <div class="ai-section-title">Raw AI Output</div>
      <pre class="ai-raw-output">${escapeHtml(text)}</pre>
    </div>
  `;
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function closeResult() {
  document.getElementById('ai-result-panel').style.display = 'none';
}

function copyResult() {
  const text = document.getElementById('ai-text-result').textContent +
               document.getElementById('ai-structured-result').textContent;
  navigator.clipboard.writeText(text).then(() => alert('Copied to clipboard!'));
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>