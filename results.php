<?php
require 'config.php';

$students = $pdo->query("SELECT * FROM students ORDER BY name")->fetchAll();
$specs    = $pdo->query("SELECT * FROM specializations ORDER BY id")->fetchAll();
$crit     = $pdo->query("SELECT * FROM criteria ORDER BY id")->fetchAll();

$sid = (int)($_GET['student_id'] ?? 0);
$results = [];      // complete specializations with their score
$incomplete = [];   // specializations with missing scores
$ev = [];
$tie = false;
$totalW = 0;
foreach ($crit as $c) { $totalW += (float)$c['weight']; }

if ($sid) {
    $st = $pdo->prepare("SELECT specialization_id, criterion_id, score FROM evaluations WHERE student_id=?");
    $st->execute([$sid]);
    foreach ($st->fetchAll() as $r) {
        $ev[$r['specialization_id']][$r['criterion_id']] = (int)$r['score'];
    }

    // ---------- WEIGHTED SUM MODEL ----------
    foreach ($specs as $s) {
        $score = 0;
        $norm = [];
        $ok = true;
        foreach ($crit as $c) {
            $x = $ev[$s['id']][$c['id']] ?? null;
            if ($x === null) { $ok = false; break; }
            // Step 1: normalization (0..1, higher is always better)
            //   maximize: x / 10          minimize: (11 - x) / 10
            $r = ($c['type'] === 'max') ? $x / 10 : (11 - $x) / 10;
            $norm[$c['id']] = $r;
            // Step 2: weighted sum
            $score += (float)$c['weight'] * $r;
        }
        if ($ok && $totalW > 0) {
            $results[] = ['spec' => $s, 'score' => $score / $totalW, 'norm' => $norm];
        } else {
            $incomplete[] = $s['name'];
        }
    }

    // Step 3: ranking (descending)
    usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

    // Ranks with tie detection (scores equal at 4 decimals = tie)
    $rank = 0; $prev = null; $i = 0;
    foreach ($results as &$r) {
        $i++;
        $key = round($r['score'], 4);
        if ($prev === null || $key !== $prev) { $rank = $i; }
        $r['rank'] = $rank;
        $prev = $key;
    }
    unset($r);

    $winners = array_values(array_filter($results, fn($r) => $r['rank'] === 1));
    $tie = count($winners) > 1;
}

$studentName = '';
foreach ($students as $s) { if ($s['id'] == $sid) $studentName = $s['name']; }
include 'header.php';
?>
<h1>Results</h1>

<div class="card">
  <form method="get">
    Student:
    <select name="student_id" onchange="this.form.submit()">
      <option value="">-- choose a student --</option>
      <?php foreach ($students as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= $sid == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <noscript><button type="submit">Calculate</button></noscript>
  </form>
</div>

<?php if ($sid): ?>

<?php if (abs($totalW - 1) > 0.0001): ?>
  <div class="msg-warn">The weights sum to <?= number_format($totalW, 2) ?> (should be 1.00). Scores were divided by this total.</div>
<?php endif; ?>

<?php if ($incomplete): ?>
  <div class="msg-warn">Not ranked (missing evaluations): <strong><?= e(implode(', ', $incomplete)) ?></strong>.
    <a href="evaluations.php?student_id=<?= $sid ?>">Complete them</a>.</div>
<?php endif; ?>

<?php if (!$results): ?>
  <div class="msg-err">No complete evaluation available for <?= e($studentName) ?>.
    <a href="evaluations.php?student_id=<?= $sid ?>">Fill the evaluations</a>.</div>
<?php else: ?>

  <!-- Recommendation -->
  <div class="reco">
    <?php if ($tie): ?>
      <strong>Tie detected!</strong> For <?= e($studentName) ?>, these specializations have the same highest score
      (<?= number_format($winners[0]['score'], 4) ?>):
      <strong><?= e(implode(' / ', array_map(fn($w) => $w['spec']['name'], $winners))) ?></strong>.
    <?php else: ?>
      Recommended specialization for <strong><?= e($studentName) ?></strong>:
      <strong><?= e($results[0]['spec']['name']) ?></strong>
      (score <?= number_format($results[0]['score'], 4) ?>)
    <?php endif; ?>
  </div>

  <!-- Ranking table -->
  <h2>Ranking</h2>
  <table>
    <tr><th class="c">Rank</th><th>Specialization</th><th class="c">Overall score</th></tr>
    <?php foreach ($results as $r): ?>
    <tr>
      <td class="c"><?= $r['rank'] ?></td>
      <td><?= e($r['spec']['name']) ?></td>
      <td class="c"><?= number_format($r['score'], 4) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <!-- Bar chart -->
  <h2>Chart</h2>
  <div class="card">
    <?php foreach ($results as $r): $pct = round($r['score'] * 100, 1); ?>
    <div class="bar-row">
      <div class="bar-label"><?= e($r['spec']['name']) ?></div>
      <div class="bar-track">
        <div class="bar-fill <?= $r['rank'] === 1 ? 'first' : '' ?>" style="width: <?= $pct ?>%"><?= $pct ?>%</div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Raw evaluation matrix -->
  <h2>Evaluation matrix (scores 1-10)</h2>
  <table>
    <tr>
      <th>Specialization</th>
      <?php foreach ($crit as $c): ?>
        <th class="c"><?= e($c['name']) ?><br><small>(<?= $c['type'] ?>, w=<?= number_format((float)$c['weight'], 2) ?>)</small></th>
      <?php endforeach; ?>
    </tr>
    <?php foreach ($specs as $s): ?>
    <tr>
      <td><?= e($s['name']) ?></td>
      <?php foreach ($crit as $c): ?>
        <td class="c"><?= e($ev[$s['id']][$c['id']] ?? '-') ?></td>
      <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
  </table>

  <!-- Normalized matrix -->
  <h2>Normalized matrix (0-1)</h2>
  <p><small>Maximize: x / 10 &nbsp;|&nbsp; Minimize: (11 - x) / 10. Overall score = &Sigma; weight &times; normalized value.</small></p>
  <table>
    <tr>
      <th>Specialization</th>
      <?php foreach ($crit as $c): ?><th class="c"><?= e($c['name']) ?></th><?php endforeach; ?>
      <th class="c">Score</th>
    </tr>
    <?php foreach ($results as $r): ?>
    <tr>
      <td><?= e($r['spec']['name']) ?></td>
      <?php foreach ($crit as $c): ?>
        <td class="c"><?= number_format($r['norm'][$c['id']], 2) ?></td>
      <?php endforeach; ?>
      <td class="c"><strong><?= number_format($r['score'], 4) ?></strong></td>
    </tr>
    <?php endforeach; ?>
  </table>

<?php endif; ?>
<?php endif; ?>
<?php include 'footer.php'; ?>
