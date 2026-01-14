# Index Storage Directory

This directory stores hash-based index files used to speed up lookups
and enforce unique constraints.

## Index Format
Indexes are stored as JSON maps:

```json
{
  "a@b.com": 0,
  "c@d.com": 2
}
