<?php

class SQLParser
{
    public function parse(string $sql): array
    {
        $sql = trim($sql);

        return match (true) {
            stripos($sql, 'CREATE TABLE') === 0 => ['type' => 'CREATE', 'sql' => $sql],
            stripos($sql, 'INSERT INTO') === 0 => ['type' => 'INSERT', 'sql' => $sql],
            stripos($sql, 'SELECT') === 0 && stripos($sql, 'JOIN') !== false => ['type' => 'JOIN', 'sql' => $sql],
            stripos($sql, 'SELECT') === 0 => ['type' => 'SELECT', 'sql' => $sql],
            stripos($sql, 'UPDATE') === 0 => ['type' => 'UPDATE', 'sql' => $sql],
            stripos($sql, 'DELETE FROM') === 0 => ['type' => 'DELETE', 'sql' => $sql],
            default => throw new Exception('Unsupported SQL command'),
        };
    }
}
