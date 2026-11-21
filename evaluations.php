<?php
require 'config.php';

$students = $pdo->query("SELECT * FROM students ORDER BY name")->fetchAll();
$specs    = $pdo->query("SELECT * FROM specializations ORDER BY id")->fetchAll();
$crit     = $pdo->query("SELECT * FROM criteria ORDER BY id")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = (int)($_POST['student_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($sid) {
        if ($action === 'save') {
            $sql = "INSERT INTO evaluations (student_id, specialization_id, criterion_id, score)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE score = VALUES(score)";
            $ins = $pdo->prepare($sql);
            foreach ($specs as $s) {
                foreach ($crit as $c) {
                    $v = $_POST['score'][$s['id']][$c['id']] ?? '';
                    if ($v === '') continue;
                    $v = max(1, min(10, (int)$v));   // force 1..10
                    $ins->execute([$sid, $s['id'], $c['id'], $v]);
                }
            }
            header("Location: evaluations.php?student_id=$sid&saved=1");
            exit;
        } elseif ($action === 'delete') {
            $pdo->prepare("DELETE FROM evaluations WHERE student_id=?")->execute([$sid]);
            header("Location: evaluations.php?student_id=$sid&deleted=1");
            exit;
        }
    }
    header('Location: evaluations.php');
    exit;
}

$sid = (int)($_GET['student_id'] ?? 0);
$ev = [];
if ($sid) {
    $st = $pdo->prepare("SELECT specialization_id, criterion_id, score FROM evaluations WHERE student_id=?");
    $st->execute([$sid]);
    foreach ($st->fetchAll() as $r) {
        $ev[$r['specialization_id']][$r['criterion_id']] = $r['score'];
    }
}
include 'header.php';
?>
<h1>Evaluations</h1>

<?php if (isset($_GET['saved'])): ?><div class="msg-ok">Evaluations saved.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="msg-ok">Evaluations deleted.</div><?php endif; ?>

<div class="card">
  <form method="get">
    Student:
    <select name="student_id" onchange="this.form.submit()">
      <option value="">-- choose a student --</option>
      <?php foreach ($students as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= $sid == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <noscript><button type="submit">Open</button></noscript>
  </form>
</div>

<?php if ($sid): ?>
<p>Enter a score from <strong>1 to 10</strong> for each specialization and criterion.
For <em>Difficulty</em>, a higher value means harder.</p>

<form method="post">
  <input type="hidden" name="action" value="save">
  <input type="hidden" name="student_id" value="<?= $sid ?>">
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
        <td class="c">
          <input class="score" type="number" min="1" max="10"
                 name="score[<?= (int)$s['id'] ?>][<?= (int)$c['id'] ?>]"
                 value="<?= e($ev[$s['id']][$c['id']] ?? '') ?>">
        </td>
      <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
  </table>
  <p><button type="submit">Save evaluations</button>
     <a class="btn grey" href="results.php?student_id=<?= $sid ?>">See results</a></p>
</form>

<form method="post" onsubmit="return confirm('Delete ALL evaluations of this student?')">
  <input type="hidden" name="action" value="delete">
  <input type="hidden" name="student_id" value="<?= $sid ?>">
  <button class="danger" type="submit">Delete all evaluations of this student</button>
</form>
<?php endif; ?>
<?php include 'footer.php'; ?>
