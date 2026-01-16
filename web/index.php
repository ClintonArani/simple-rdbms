<?php
require_once __DIR__ . '/../src/Engine/QueryEngine.php';
require_once __DIR__ . '/../src/Parser/SQLParser.php'; 

$engine = new QueryEngine();
$message = '';
$result = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sql'])) {
    $sqlQuery = trim($_POST['sql']);

    try {
        // Use SQLParser to parse the query
        $parser = new SQLParser();
        $parsed = $parser->parse($sqlQuery);

        $execResult = $engine->execute($parsed);

        if (is_array($execResult)) {
            $result = $execResult; // SELECT or JOIN results
            $message = "Query executed successfully!";
        } else {
            $message = $execResult; // CREATE / INSERT / UPDATE / DELETE messages
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $result = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simple PHP RDBMS - SQL Editor</title>
    <style>
        body {
            font-family: monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            margin: 20px;
        }

        h1 { color: #00ff99; }
        textarea {
            width: 100%;
            max-width: 900px;
            height: 150px;
            background: #2d2d2d;
            color: #d4d4d4;
            padding: 12px;
            font-family: monospace;
            font-size: 14px;
            border: 1px solid #555;
            border-radius: 4px;
            resize: vertical;
        }

        button {
            padding: 10px 20px;
            margin-top: 10px;
            background: #00ff99;
            color: #000;
            border: none;
            font-weight: bold;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover { background: #00cc77; }

        .message {
            margin: 15px 0;
            padding: 10px;
            border-radius: 4px;
            max-width: 900px;
        }

        .success { background: #144d14; color: #00ff99; }
        .error { background: #5a1a1a; color: #ff5555; }

        table {
            border-collapse: collapse;
            margin-top: 20px;
            width: 100%;
            max-width: 900px;
            background: #2d2d2d;
        }

        th, td {
            padding: 8px 12px;
            border: 1px solid #555;
            text-align: left;
        }

        th { background: #00ff99; color: #000; }
        tr:nth-child(even) { background: #1f1f1f; }
        tr:hover { background: #3a3a3a; }
    </style>
</head>
<body>
    <h1>Simple PHP RDBMS - SQL Editor</h1>

    <?php if($message): ?>
        <div id="message" class="message <?= strpos($message, 'Error') === false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <textarea name="sql" placeholder="Write your SQL query here..."><?= isset($_POST['sql']) ? htmlspecialchars($_POST['sql']) : '' ?></textarea>
        <button type="submit">Execute</button>
    </form>

    <?php if(!empty($result)): ?>
        <table>
            <thead>
                <tr>
                    <?php foreach(array_keys($result[0]) as $col): ?>
                        <th><?= htmlspecialchars($col) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach($result as $row): ?>
                    <tr>
                        <?php foreach($row as $val): ?>
                            <td><?= htmlspecialchars($val) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <script>
        // Hide success messages after 5 seconds
        const msg = document.getElementById('message');
        if(msg && msg.classList.contains('success')) {
            setTimeout(() => { msg.style.display = 'none'; }, 5000);
        }
        
    </script>

</body>
</html>
