<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'save') {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($name !== '') {
            if ($id) {
                $pdo->prepare("UPDATE specializations SET name=?, description=? WHERE id=?")->execute([$name, $desc, $id]);
            } else {
                $pdo->prepare("INSERT INTO specializations (name, description) VALUES (?, ?)")->execute([$name, $desc]);
            }
        }
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM specializations WHERE id=?")->execute([$id]);
    }
    header('Location: specializations.php');
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare("SELECT * FROM specializations WHERE id=?");
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch();
}
$rows = $pdo->query("SELECT * FROM specializations ORDER BY id")->fetchAll();
include 'header.php';
?>
<h1>Specializations</h1>

<div class="card">
  <h2 style="margin-top:0"><?= $edit ? 'Edit specialization' : 'Add specialization' ?></h2>
  <form method="post">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= e($edit['id'] ?? 0) ?>">
    <input type="text" name="name" placeholder="Name" required value="<?= e($edit['name'] ?? '') ?>">
    <input type="text" name="description" size="45" placeholder="Description" value="<?= e($edit['description'] ?? '') ?>">
    <button type="submit"><?= $edit ? 'Update' : 'Add' ?></button>
    <?php if ($edit): ?><a class="btn grey" href="specializations.php">Cancel</a><?php endif; ?>
  </form>
</div>

<table>
  <tr><th>ID</th><th>Name</th><th>Description</th><th>Actions</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= (int)$r['id'] ?></td>
    <td><?= e($r['name']) ?></td>
    <td><?= e($r['description']) ?></td>
    <td>
      <a class="btn" href="specializations.php?edit=<?= (int)$r['id'] ?>">Edit</a>
      <form class="inline" method="post" onsubmit="return confirm('Delete this specialization and its evaluations?')">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
        <button class="danger" type="submit">Delete</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php include 'footer.php'; ?>
