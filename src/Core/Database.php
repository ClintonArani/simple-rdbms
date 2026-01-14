<?php

require_once __DIR__ . '/Table.php';

class Database
{
    private string $tablePath;

    public function __construct()
    {
        $this->tablePath = __DIR__ . '/../../data/tables/';
    }

    public function saveTable(Table $table): void
    {
        $data = [
            'name' => $table->name,
            'schema' => $table->schema,
            'rows' => $table->rows
        ];

        file_put_contents(
            $this->tablePath . $table->name . '.json',
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }

    public function loadTable(string $name): Table
    {
        $file = $this->tablePath . $name . '.json';

        if (!file_exists($file)) {
            throw new Exception("Table '$name' does not exist");
        }

        $data = json_decode(file_get_contents($file), true);

        return new Table(
            $data['name'],
            $data['schema'],
            $data['rows']
        );
    }
}
