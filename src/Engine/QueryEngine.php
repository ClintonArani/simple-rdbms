<?php

require_once __DIR__ . '/../Core/Database.php';

class QueryEngine
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function execute(array $cmd)
    {
        return match ($cmd['type']) {
            'CREATE' => $this->createTable($cmd['sql']),
            'INSERT' => $this->insert($cmd['sql']),
            'SELECT' => $this->select($cmd['sql']),
            'UPDATE' => $this->update($cmd['sql']),
            'DELETE' => $this->delete($cmd['sql']),
            'JOIN'   => $this->join($cmd['sql']),
        };
    }

    private function createTable(string $sql): string
    {
        preg_match('/CREATE TABLE (\w+)\s*\((.+)\)/i', $sql, $m);
        $schema = [];

        foreach (explode(',', $m[2]) as $col) {
            $parts = explode(' ', trim($col));
            $schema[$parts[0]] = [
                'type' => strtoupper($parts[1]),
                'primary' => in_array('PRIMARY', $parts),
                'unique' => in_array('UNIQUE', $parts)
            ];
        }

        $this->db->saveTable(new Table($m[1], $schema));
        return "Table '{$m[1]}' created";
    }

    private function insert(string $sql): string
    {
        preg_match('/INSERT INTO (\w+) VALUES \((.+)\)/i', $sql, $m);
        $table = $this->db->loadTable($m[1]);

        $values = array_map(fn($v) => trim($v, " \"'"), explode(',', $m[2]));
        $row = array_combine(array_keys($table->schema), $values);

        $table->insert($row);
        $this->db->saveTable($table);
        return "1 row inserted";
    }

    private function select(string $sql): array
    {
        if (preg_match('/WHERE/', $sql)) {
            preg_match('/SELECT \* FROM (\w+) WHERE (\w+)=(.+)/i', $sql, $m);
            return $this->db->loadTable($m[1])->selectWhere($m[2], trim($m[3], " \"'"));
        }

        preg_match('/SELECT \* FROM (\w+)/i', $sql, $m);
        return $this->db->loadTable($m[1])->rows;
    }

private function update(string $sql): string
{
    preg_match(
        '/UPDATE\s+(\w+)\s+SET\s+(\w+)\s*=\s*(.+)\s+WHERE\s+(\w+)\s*=\s*(.+)/i',
        $sql,
        $m
    );

    $table = $this->db->loadTable($m[1]);

    $setColumn   = $m[2];
    $setValue    = trim($m[3], " \"'");
    $whereColumn = $m[4];
    $whereValue  = trim($m[5], " \"'");

    $count = $table->updateWhere(
        $whereColumn,
        $whereValue,
        $setColumn,
        $setValue
    );

    $this->db->saveTable($table);

    return "$count row(s) updated";
}


    private function delete(string $sql): string
    {
        preg_match('/DELETE FROM (\w+) WHERE (\w+)=(.+)/i', $sql, $m);
        $table = $this->db->loadTable($m[1]);

        $count = $table->deleteWhere($m[2], trim($m[3], " \"'"));
        $this->db->saveTable($table);

        return "$count row(s) deleted";
    }

    private function join(string $sql): array
    {
        preg_match(
            '/SELECT \* FROM (\w+) JOIN (\w+) ON (\w+)\.(\w+)=(\w+)\.(\w+)/i',
            $sql, $m
        );

        $t1 = $this->db->loadTable($m[1]);
        $t2 = $this->db->loadTable($m[2]);

        $result = [];
        foreach ($t1->rows as $r1) {
            foreach ($t2->rows as $r2) {
                if ($r1[$m[4]] == $r2[$m[6]]) {
                    $result[] = array_merge($r1, $r2);
                }
            }
        }
        return $result;
    }

    
}
