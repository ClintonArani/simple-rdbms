<?php

require_once __DIR__ . '/../Core/Database.php';

class QueryEngine
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function execute(array $command)
    {
        return match ($command['type']) {
            'CREATE' => $this->createTable($command['sql']),
            'INSERT' => $this->insert($command['sql']),
            'SELECT' => $this->select($command['sql']),
            'DELETE' => $this->delete($command['sql']),
            default => throw new Exception('Unknown command'),
        };
    }

    private function createTable(string $sql): string
    {
        preg_match('/CREATE TABLE (\w+)\s*\((.+)\)/i', $sql, $m);

        $tableName = $m[1];
        $columns = explode(',', $m[2]);
        $schema = [];

        foreach ($columns as $column) {
            [$name, $type] = explode(' ', trim($column));
            $schema[$name] = ['type' => strtoupper($type)];
        }

        $table = new Table($tableName, $schema);
        $this->db->saveTable($table);

        return "Table '$tableName' created successfully";
    }

    private function insert(string $sql): string
    {
        preg_match('/INSERT INTO (\w+) VALUES \((.+)\)/i', $sql, $m);

        $table = $this->db->loadTable($m[1]);
        $values = array_map(fn($v) => trim($v, " \"'"), explode(',', $m[2]));

        $row = [];
        $i = 0;
        foreach ($table->schema as $col => $_) {
            $row[$col] = $values[$i++] ?? null;
        }

        $table->insert($row);
        $this->db->saveTable($table);

        return "1 row inserted";
    }

    private function select(string $sql): array
    {
        preg_match('/SELECT \* FROM (\w+)/i', $sql, $m);
        return $this->db->loadTable($m[1])->selectAll();
    }

    private function delete(string $sql): string
    {
        preg_match('/DELETE FROM (\w+) WHERE (\w+)=(.+)/i', $sql, $m);

        $table = $this->db->loadTable($m[1]);
        $count = $table->deleteWhere($m[2], trim($m[3], " \"'"));
        $this->db->saveTable($table);

        return "$count row(s) deleted";
    }
}
