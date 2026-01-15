<?php

class Index
{
    private array $map = [];

    public function add($value, int $rowId): void
    {
        if (isset($this->map[$value])) {
            throw new Exception("Unique constraint violated for value: $value");
        }
        $this->map[$value] = $rowId;
    }

    public function find($value): ?int
    {
        return $this->map[$value] ?? null;
    }

    public function rebuild(array $rows, string $column): void
    {
        $this->map = [];
        foreach ($rows as $i => $row) {
            $this->add($row[$column], $i);
        }
    }

    public function remove($value): void
    {
        unset($this->map[$value]);
    }
}
