# Tables Storage Directory

This directory contains persisted table data for the Simple PHP RDBMS.

## File Format
Each table is stored as a JSON file with the structure:

```json
{
  "name": "users",
  "schema": {
    "id": { "type": "INT" },
    "email": { "type": "STRING" },
    "name": { "type": "STRING" }
  },
  "rows": [
    { "id": 1, "email": "a@b.com", "name": "Alice" }
  ]
}
