<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'save') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($name !== '') {
            if ($id) {
                $pdo->prepare("UPDATE students SET name=?, email=? WHERE id=?")->execute([$name, $email, $id]);
            } else {
                $pdo->prepare("INSERT INTO students (name, email) VALUES (?, ?)")->execute([$name, $email]);
            }
        }
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM students WHERE id=?")->execute([$id]);
    }
    header('Location: students.php');
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare("SELECT * FROM students WHERE id=?");
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch();
}
$rows = $pdo->query("SELECT * FROM students ORDER BY id")->fetchAll();
include 'header.php';
?>
<h1>Students</h1>

<div class="card">
  <h2 style="margin-top:0"><?= $edit ? 'Edit student' : 'Add student' ?></h2>
  <form method="post">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= e($edit['id'] ?? 0) ?>">
    <input type="text" name="name" placeholder="Full name" required value="<?= e($edit['name'] ?? '') ?>">
    <input type="email" name="email" placeholder="Email" value="<?= e($edit['email'] ?? '') ?>">
    <button type="submit"><?= $edit ? 'Update' : 'Add' ?></button>
    <?php if ($edit): ?><a class="btn grey" href="students.php">Cancel</a><?php endif; ?>
  </form>
</div>

<table>
  <tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= (int)$r['id'] ?></td>
    <td><?= e($r['name']) ?></td>
    <td><?= e($r['email']) ?></td>
    <td>
      <a class="btn" href="students.php?edit=<?= (int)$r['id'] ?>">Edit</a>
      <form class="inline" method="post" onsubmit="return confirm('Delete this student and their evaluations?')">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
        <button class="danger" type="submit">Delete</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php include 'footer.php'; ?>
