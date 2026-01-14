<?php

require_once __DIR__ . '/../src/Parser/SQLParser.php';
require_once __DIR__ . '/../src/Engine/QueryEngine.php';

$parser = new SQLParser();
$engine = new QueryEngine();

echo "Simple PHP RDBMS (type 'exit' to quit)\n";

while (true) {
    echo "rdbms> ";
    $input = trim(fgets(STDIN));

    if ($input === 'exit') {
        break;
    }

    try {
        $cmd = $parser->parse($input);
        $result = $engine->execute($cmd);
        print_r($result);
        echo PHP_EOL;
    } catch (Exception $e) {
        echo "Error: {$e->getMessage()}\n";
    }
}
