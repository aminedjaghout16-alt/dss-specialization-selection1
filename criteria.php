<?php
require 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'save') {
        $name   = trim($_POST['name'] ?? '');
        $type   = ($_POST['type'] ?? 'max') === 'min' ? 'min' : 'max';
        $weight = (float)($_POST['weight'] ?? 0);
        if ($name === '' || $weight < 0 || $weight > 1) {
            $error = 'Name is required and the weight must be between 0 and 1.';
        } else {
            if ($id) {
                $pdo->prepare("UPDATE criteria SET name=?, type=?, weight=? WHERE id=?")->execute([$name, $type, $weight, $id]);
            } else {
                $pdo->prepare("INSERT INTO criteria (name, type, weight) VALUES (?, ?, ?)")->execute([$name, $type, $weight]);
            }
        }
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM criteria WHERE id=?")->execute([$id]);
    }
    if ($error === '') {
        header('Location: criteria.php');
        exit;
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare("SELECT * FROM criteria WHERE id=?");
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch();
}
$rows = $pdo->query("SELECT * FROM criteria ORDER BY id")->fetchAll();
$sum = 0;
foreach ($rows as $r) { $sum += (float)$r['weight']; }
include 'header.php';
?>
<h1>Criteria and weights</h1>

<?php if ($error): ?><div class="msg-err"><?= e($error) ?></div><?php endif; ?>

<div class="card">
  <h2 style="margin-top:0"><?= $edit ? 'Edit criterion' : 'Add criterion' ?></h2>
  <form method="post">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= e($edit['id'] ?? 0) ?>">
    <input type="text" name="name" placeholder="Criterion name" required value="<?= e($edit['name'] ?? '') ?>">
    <select name="type">
      <option value="max" <?= ($edit['type'] ?? '') === 'max' ? 'selected' : '' ?>>Maximize (higher is better)</option>
      <option value="min" <?= ($edit['type'] ?? '') === 'min' ? 'selected' : '' ?>>Minimize (lower is better)</option>
    </select>
    <input type="number" name="weight" step="0.01" min="0" max="1" placeholder="Weight (0-1)" required value="<?= e($edit['weight'] ?? '') ?>">
    <button type="submit"><?= $edit ? 'Update' : 'Add' ?></button>
    <?php if ($edit): ?><a class="btn grey" href="criteria.php">Cancel</a><?php endif; ?>
  </form>
</div>

<?php if (abs($sum - 1) < 0.0001): ?>
  <div class="msg-ok">Total of weights = <?= number_format($sum, 2) ?> (OK)</div>
<?php else: ?>
  <div class="msg-warn">Total of weights = <?= number_format($sum, 2) ?>. It should be 1.00. Results will be rescaled by the total, but please fix the weights.</div>
<?php endif; ?>

<table>
  <tr><th>ID</th><th>Criterion</th><th>Type</th><th>Weight</th><th>Actions</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= (int)$r['id'] ?></td>
    <td><?= e($r['name']) ?></td>
    <td><?= $r['type'] === 'max' ? 'Maximize' : 'Minimize' ?></td>
    <td><?= number_format((float)$r['weight'], 2) ?></td>
    <td>
      <a class="btn" href="criteria.php?edit=<?= (int)$r['id'] ?>">Edit</a>
      <form class="inline" method="post" onsubmit="return confirm('Delete this criterion and its evaluations?')">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
        <button class="danger" type="submit">Delete</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php include 'footer.php'; ?>
