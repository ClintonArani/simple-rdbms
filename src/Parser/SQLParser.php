<?php

class SQLParser
{
    public function parse(string $sql): array
    {
        $sql = trim($sql);

        if (stripos($sql, 'CREATE TABLE') === 0) {
            return ['type' => 'CREATE', 'sql' => $sql];
        }

        if (stripos($sql, 'INSERT INTO') === 0) {
            return ['type' => 'INSERT', 'sql' => $sql];
        }

        if (stripos($sql, 'SELECT') === 0) {
            return ['type' => 'SELECT', 'sql' => $sql];
        }

        if (stripos($sql, 'DELETE FROM') === 0) {
            return ['type' => 'DELETE', 'sql' => $sql];
        }

        throw new Exception('Unsupported SQL command');
    }
}
