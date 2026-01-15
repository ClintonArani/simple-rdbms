# Simple PHP RDBMS (Pesapal Junior Developer Challenge)

## 📌 Overview

This project is a **lightweight, file-based Relational Database Management System (RDBMS)** implemented entirely in **pure PHP** without external frameworks or database engines. It was built to demonstrate **core database concepts, system design skills, and low-level problem-solving ability**, as required by the **Pesapal Junior Developer Challenge**.

The system supports a **SQL-like interface**, persistent storage using files, indexing, constraints, and table relationships.

---

## 🎯 Challenge Objectives Addressed

The implementation intentionally focuses on *fundamentals*, not shortcuts:

* No MySQL / PostgreSQL / SQLite
* No ORM or frameworks
* Manual SQL parsing and execution
* File-based persistence
* Deterministic and explainable behavior

This mirrors how early database engines work internally.

---

## ✨ Features Implemented

### Core Database Features

* Create tables with schema definitions
* Insert records
* Select records
* Update records
* Delete records

### Query Capabilities

* `WHERE` clause filtering
* `UPDATE ... WHERE`
* `DELETE ... WHERE`
* `INNER JOIN` between tables

### Constraints & Performance

* `PRIMARY KEY` constraint
* `UNIQUE` constraint
* Hash-based indexing for fast lookups

### Architecture

* Modular design (Parser, Engine, Core)
* File-based persistence (`/data` directory)
* Interactive CLI (REPL-style)

---

## 🗂️ Project Structure

```
simple-rdbms/
│
├── cli/                # CLI entry point (REPL)
│   └── rdbms.php
│
├── src/
│   ├── Core/           # Database, Table, Index logic
│   ├── Parser/         # SQL parsing
│   └── Engine/         # Query execution engine
│
├── data/               # Persisted table data & indexes
│   └── tables/
│
├── docs/               # Documentation & screenshots
│   └── screenshots/
│
├── web/                # Optional web demo (future)
│
└── README.md
```

---

## ▶️ How to Run

### 1. Requirements

* PHP **8.0+** (CLI enabled)

Verify installation:

```bash
php -v
```

### 2. Start the RDBMS

From the project root:

```bash
php cli/rdbms.php
```

You should see:

```
Simple PHP RDBMS (type 'exit' to quit)
rdbms>
```

---

## 🧪 Example Usage

```sql
CREATE TABLE users (id INT PRIMARY, email STRING UNIQUE, name STRING)
INSERT INTO users VALUES (1, "a@b.com", "Alice")
SELECT * FROM users WHERE id=1
UPDATE users SET name="Bob" WHERE id=1
DELETE FROM users WHERE id=1
```

### JOIN Example

```sql
CREATE TABLE orders (id INT PRIMARY, user_id INT, item STRING)
INSERT INTO orders VALUES (1, 1, "Book")
SELECT * FROM users JOIN orders ON users.id=orders.user_id
```

---

## 📸 Screenshots

All screenshots demonstrating usage and outputs are located in:

```
docs/screenshots/
```

Below is a complete walkthrough of the implemented features with visual references:

### 🗄️ Table Creation

* **Create users table**
  `1- Create users table.png`
* **Create orders table**
  `2- Create orders table.png`

### ➕ Insert Operations

* **Insert users records**
  `3- Insert users.png`
* **Insert orders records**
  `4- Insert orders.png`

### 📖 Select Queries

* **Select all users**
  `5- Select all users.png`
* **Select with WHERE clause**
  `6- Select with WHERE clause.png`

### ✏️ Update Operations

* **Update user record**
  `9- Update user record.png`
* **Verify updated user**
  `10- Verify updated user.png`

### ❌ Delete Operations

* **Delete user record**
  `7- Delete user record.png`
* **Verify deletion**
  `8- Verify deletion.png`
* **Delete order by ID**
  `13- Delete order by ID.png`

### 🔐 Constraints Enforcement

* **Unique constraint violation**
  `11- Unique constraint violation.png`
* **Primary key constraint violation**
  `12- Primary key constraint violation.png`

### 🔗 JOIN Queries

* **JOIN users and orders**
  `15- JOIN users and orders.png`

### 🖥️ CLI & REPL

* **RDBMS CLI startup**
  `16- RDBMS CLI startup.png`
* **Interactive REPL session**
  `17- Interactive REPL session.png`

### 📦 Final State Validation

* **Final orders table**
  `14- Final orders table.png`

These screenshots collectively demonstrate:

* Interactive CLI (REPL)
* SQL parsing and execution
* CRUD operations
* WHERE filtering
* UPDATE and DELETE behavior
* PRIMARY & UNIQUE constraint enforcement
* Relational JOIN support

---

## 🧠 Design Notes

* Indexes are implemented using associative arrays (hash maps)
* Constraints are enforced at insertion/update time
* JOINs are implemented as nested loop joins for clarity
* Data is serialized and persisted on disk

---

## 🚀 Future Improvements

* LEFT / RIGHT JOIN support
* Multiple WHERE conditions
* Query optimizer
* Transaction support
* Web UI expansion

---

## 👤 Author

**Clinton Omari**
Junior Software Developer
Pesapal Junior Developer Challenge – 2026

---

## 📄 License

This project is for evaluation and educational purposes.
