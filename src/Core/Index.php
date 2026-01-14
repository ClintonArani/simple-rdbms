<?php

class Index
{
    private array $map = [];

    public function add($value, int $rowId): void
    {
        if (isset($this->map[$value])) {
            throw new Exception("Unique constraint violation on value: $value");
        }
        $this->map[$value] = $rowId;
    }

    public function find($value): ?int
    {
        return $this->map[$value] ?? null;
    }

    public function remove($value): void
    {
        unset($this->map[$value]);
    }

    public function all(): array
    {
        return $this->map;
    }
}
