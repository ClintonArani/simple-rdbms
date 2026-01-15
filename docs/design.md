# Design Document – Simple PHP RDBMS

## 1. Overview

This project implements a **minimal relational database management system (RDBMS)** from scratch using PHP. The goal is not to compete with mature databases such as MySQL or PostgreSQL, but to **demonstrate understanding of core database concepts**, system design, and problem-solving ability, as required by the Pesapal Junior Developer Challenge.

The system supports:

* SQL-like query interface
* Interactive CLI (REPL)
* Web-based SQL editor
* Relational tables with schemas
* CRUD operations
* Primary and Unique constraints
* Basic JOIN operations
* Persistent storage using files

---

## 2. High-Level Architecture

The system is organized into clear layers:

```
User (CLI / Web UI)
        ↓
     SQLParser
        ↓
   QueryEngine
        ↓
     Database
        ↓
      Table
        ↓
   File Storage (JSON)
```

Each layer has a single responsibility, which makes the system easy to reason about, test, and extend.

---

## 3. Component Breakdown

### 3.1 CLI (REPL)

**Location:** `cli/rdbms.php`

* Provides an interactive command-line interface
* Accepts SQL queries until the user types `exit`
* Displays results in tabular format
* Acts as the primary interface for demonstrating the database engine

This mirrors how users interact with real databases such as `psql` or `mysql`.

---

### 3.2 Web Interface

**Location:** `web/index.php`

* Provides a browser-based SQL editor
* Allows execution of arbitrary SQL queries
* Displays success/error messages
* Renders SELECT and JOIN results in HTML tables

The web UI exists to satisfy the requirement of a **trivial web app that performs CRUD operations on the database**.

---

### 3.3 SQLParser

**Location:** `src/Parser/SQLParser.php`

Responsibilities:

* Identifies query type (CREATE, INSERT, SELECT, UPDATE, DELETE, JOIN)
* Normalizes SQL input
* Returns a structured command array

Example output:

```php
[
  'type' => 'UPDATE',
  'sql'  => 'UPDATE users SET name="Bob" WHERE id=1'
]
```

The parser is intentionally simple but extensible.

---

### 3.4 QueryEngine

**Location:** `src/Engine/QueryEngine.php`

Responsibilities:

* Dispatches parsed queries to appropriate handlers
* Cleans SQL input (e.g., removes trailing semicolons)
* Coordinates between parser and storage layer

Each SQL operation has a dedicated method:

* `createTable()`
* `insert()`
* `select()`
* `update()`
* `delete()`
* `join()`

This separation keeps logic readable and maintainable.

---

### 3.5 Database

**Location:** `src/Core/Database.php`

Responsibilities:

* Manages table lifecycle
* Loads and saves tables to disk
* Acts as a registry for all tables

Tables are persisted as JSON files under:

```
data/tables/
```

Indexes and metadata can be extended under:

```
data/indexes/
```

---

### 3.6 Table

**Location:** `src/Core/Table.php`

Responsibilities:

* Holds table schema and rows
* Enforces PRIMARY and UNIQUE constraints
* Executes row-level operations:

  * insert
  * select
  * update
  * delete

All values are stored internally as strings, mimicking text-based SQL input.

---

## 4. Data Storage Strategy

* Each table is stored as a JSON file
* Schema and data are stored together
* File-based persistence allows the database to survive restarts

This approach was chosen for:

* Simplicity
* Transparency
* Ease of debugging

---

## 5. Constraint Handling

The system supports:

* **PRIMARY KEY** (no duplicates, non-null)
* **UNIQUE** constraints

Constraints are checked during:

* INSERT
* UPDATE

Violations throw exceptions, which are surfaced to the CLI or web UI.

---

## 6. JOIN Implementation

The system supports a basic INNER JOIN:

```sql
SELECT * FROM users JOIN orders ON users.id=orders.user_id;
```

Implementation strategy:

* Nested loop join
* Compares join columns row by row
* Merges matching rows into a single result

This is simple but demonstrates relational behavior clearly.

---

## 7. Error Handling

* Invalid SQL results in parser or execution errors
* Constraint violations are clearly reported
* Errors never crash the REPL or web UI

This mirrors real-world database robustness.

---

## 8. Design Decisions & Trade-offs

| Decision           | Reason                      |
| ------------------ | --------------------------- |
| File-based storage | Simplicity and transparency |
| Simple SQL grammar | Focus on fundamentals       |
| Loose comparisons  | Match SQL semantics         |
| No query optimizer | Out of scope for challenge  |

---

## 9. Extensibility

The architecture allows easy addition of:

* More WHERE conditions (AND / OR)
* Additional data types
* Index-based lookup
* Aggregations (COUNT, SUM)
* Transactions

---

## 10. Conclusion

This project demonstrates:

* Understanding of relational databases
* Clean separation of concerns
* Practical software engineering decisions
* Ability to design and implement non-trivial systems

While simplified, the system captures the **core essence of an RDBMS**, fulfilling both the technical and evaluative goals of the Pesapal Junior Developer Challenge.
