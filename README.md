# Dbmover\PgsqlIndexes
PostgreSQL-specific index (re)creation plugin

## Installation
```sh
$ composer require dbmover/pgsql-indexes
```

    This package is part of the `dbmover/pgsql` meta-package.

## Usage
See `dbmover/core` for general DbMover usage.

The plugin scans your schemas for `CREATE [UNIQUE] INDEX` statements, compares
them to existing indexes and if necessary (re)creates them. It also drops any
indexes no longer mentioned in your schemas.

## Caveats
PostgreSQL indexes are named implicitly, but using named indexes makes it easier
for DbMover to determine if an index is new (or removed). You can use the
`dbmover/force-named-indexes` plugin if you're too lazy to explicitly name your
indexes (like us).

Indexes containing a `WHERE` clause - and particularly complex indexes, due to
naming length restrictions - should always be explicitly named for best results.

## Contributing
See `dbmover/core`.

