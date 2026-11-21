<?php
require 'config.php';
$nb = [
    'Students'        => $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(),
    'Specializations' => $pdo->query("SELECT COUNT(*) FROM specializations")->fetchColumn(),
    'Criteria'        => $pdo->query("SELECT COUNT(*) FROM criteria")->fetchColumn(),
    'Evaluations'     => $pdo->query("SELECT COUNT(*) FROM evaluations")->fetchColumn(),
];
include 'header.php';
?>
<h1>Decision Support System</h1>
<p>Choose the university specialization that best matches a student's profile using the
<strong>Weighted Sum Model (WSM)</strong>.</p>

<div class="stats">
<?php foreach ($nb as $label => $n): ?>
  <div class="stat"><b><?= (int)$n ?></b><?= e($label) ?></div>
<?php endforeach; ?>
</div>

<div class="card" style="margin-top:20px">
  <h2 style="margin-top:0">How to use</h2>
  <ol>
    <li>Add <a href="students.php">students</a> and <a href="specializations.php">specializations</a>.</li>
    <li>Define <a href="criteria.php">criteria</a> (type max/min) and their weights (sum = 1).</li>
    <li>Fill the scores (1-10) in <a href="evaluations.php">Evaluations</a> for a student.</li>
    <li>Open <a href="results.php">Results</a> to see scores, ranking, chart and the recommendation.</li>
  </ol>
</div>
<?php include 'footer.php'; ?>
