<?php

class Table
{
    public string $name;
    public array $schema;
    public array $rows;

    public function __construct(string $name, array $schema, array $rows = [])
    {
        $this->name = $name;
        $this->schema = $schema;
        $this->rows = $rows;
    }

    public function insert(array $row): void
    {
        foreach ($this->schema as $column => $rules) {
            if (!array_key_exists($column, $row)) {
                throw new Exception("Missing column: $column");
            }
        }
        $this->rows[] = $row;
    }

    public function selectAll(): array
    {
        return $this->rows;
    }

    public function deleteWhere(string $column, $value): int
    {
        $count = 0;
        foreach ($this->rows as $i => $row) {
            if ($row[$column] == $value) {
                unset($this->rows[$i]);
                $count++;
            }
        }
        $this->rows = array_values($this->rows);
        return $count;
    }
}
