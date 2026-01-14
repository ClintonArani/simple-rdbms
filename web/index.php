<?php

require_once __DIR__ . '/../src/Engine/QueryEngine.php';

$engine = new QueryEngine();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $engine->execute([
        'type' => 'INSERT',
        'sql' => sprintf(
            'INSERT INTO users VALUES (%s,"%s","%s")',
            $_POST['id'],
            $_POST['email'],
            $_POST['name']
        )
    ]);
}

$users = [];
try {
    $users = $engine->execute([
        'type' => 'SELECT',
        'sql' => 'SELECT * FROM users'
    ]);
} catch (Exception $e) {}
?>

<form method="post">
    <input name="id" placeholder="ID" required>
    <input name="email" placeholder="Email" required>
    <input name="name" placeholder="Name" required>
    <button>Add User</button>
</form>

<pre><?php print_r($users); ?></pre>
