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
    // Match: UPDATE table SET col1=val1, col2=val2 WHERE col=val
    preg_match(
        '/UPDATE\s+(\w+)\s+SET\s+(.+?)\s+WHERE\s+(\w+)\s*=\s*(.+)$/i',
        $sql,
        $m
    );

    if (!isset($m[4])) {
        throw new Exception("Invalid UPDATE syntax: $sql");
    }

    $table = $this->db->loadTable($m[1]);
    $whereColumn = $m[3];
    $whereValue = trim($m[4], " \"'");

    // Parse SET clauses - split by commas but respect quoted values
    $setClauses = $m[2];
    $updates = [];
    
    // Simple parsing for "col1=val1, col2=val2"
    $clauses = explode(',', $setClauses);
    foreach ($clauses as $clause) {
        $clause = trim($clause);
        if (preg_match('/^(\w+)\s*=\s*(.+)$/', $clause, $match)) {
            $column = $match[1];
            $value = trim($match[2], " \"'");
            $updates[$column] = $value;
        }
    }

    // Apply updates
    $count = 0;
    foreach ($table->rows as &$row) {
        if ($row[$whereColumn] == $whereValue) {
            foreach ($updates as $column => $value) {
                $row[$column] = $value;
            }
            $count++;
        }
    }

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
