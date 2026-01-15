<?php

require_once __DIR__ . '/Index.php';

class Table
{
    public string $name;
    public array $schema;
    public array $rows;
    public array $indexes = [];

    public function __construct(string $name, array $schema, array $rows = [])
    {
        $this->name = $name;
        $this->schema = $schema;
        $this->rows = $rows;

        foreach ($schema as $col => $rules) {
            if (!empty($rules['primary']) || !empty($rules['unique'])) {
                $this->indexes[$col] = new Index();
                $this->indexes[$col]->rebuild($rows, $col);
            }
        }
    }

    public function insert(array $row): void
    {
        foreach ($this->schema as $column => $rules) {
            if (!array_key_exists($column, $row)) {
                throw new Exception("Missing column: $column");
            }

            if (!empty($rules['primary']) || !empty($rules['unique'])) {
                $this->indexes[$column]->add($row[$column], count($this->rows));
            }
        }

        $this->rows[] = $row;
    }

    public function selectWhere(string $column, $value): array
    {
        return array_values(array_filter(
            $this->rows,
            fn($row) => $row[$column] == $value
        ));
    }

public function updateWhere(
    string $whereColumn,
    string $whereValue,
    string $setColumn,
    string $setValue
): int {
    $updated = 0;

    foreach ($this->rows as &$row) {
        if (
            isset($row[$whereColumn]) &&
            (string)$row[$whereColumn] == (string)$whereValue
        ) {
            $row[$setColumn] = $setValue;
            $updated++;
        }
    }

    return $updated;
}


    public function deleteWhere(string $column, $value): int
    {
        $count = 0;
        foreach ($this->rows as $i => $row) {
            if ($row[$column] == $value) {
                foreach ($this->indexes as $col => $index) {
                    $index->remove($row[$col]);
                }
                unset($this->rows[$i]);
                $count++;
            }
        }
        $this->rows = array_values($this->rows);
        return $count;
    }
}
