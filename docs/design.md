# Design Document – Simple PHP RDBMS

## Overview
This project implements a minimal relational database system to demonstrate
core database concepts such as schemas, persistence, and query execution.

## Storage
Tables are stored as JSON files to ensure simplicity and transparency.

## Parsing
SQL parsing is intentionally lightweight to prioritize clarity over completeness.

## Trade-offs
- No advanced query optimizer
- Limited SQL grammar
- Designed for learning, not production

## Why This Design
The goal was to clearly demonstrate understanding of databases,
not to recreate MySQL or PostgreSQL.
