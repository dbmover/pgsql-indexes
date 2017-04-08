<?php

/**
 * @package Dbmover
 * @subpackage Pgsql
 * @subpackage Indexes
 */

namespace Dbmover\Pgsql\Indexes;

use Dbmover\Indexes;
use PDO;

class Plugin extends Indexes\Plugin
{
    public function __invoke(string $sql) : string
    {
        $sql = preg_replace_callback(
            static::REGEX,
            function ($matches) {
                if (!strpos($matches[0], ' USING ')) {
                    $matches[0] = str_replace(" ON {$matches[3]}", " ON {$matches[3]} USING btree ", $matches[0]);
                }
                return $matches[0];
            },
            $sql
        );
        return parent::__invoke($sql);
    }

    protected function existingIndexes() : array
    {
        $stmt = $this->loader->getPdo()->prepare(
            "SELECT t.relname table_name, c.relname index_name, pg_get_indexdef(indexrelid) AS definition
            FROM pg_catalog.pg_class c
                JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
                JOIN pg_catalog.pg_index i ON i.indexrelid = c.oid
                JOIN pg_catalog.pg_class t ON i.indrelid   = t.oid
            WHERE c.relkind = 'i'
                AND n.nspname = 'public'
                AND pg_catalog.pg_table_is_visible(c.oid)
            ORDER BY n.nspname, t.relname, c.relname");
        $stmt->execute([]);
        $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($indexes as &$index) {
            preg_match('@\((.*?)\)$@', $index['definition'], $columns);
            $columns = preg_split('@,\s*@', $columns[1]);
            $index['column_name'] = join(',', $columns);
            $index['non_unique'] = !strpos($index['definition'], 'UNIQUE INDEX');
            preg_match('@USING (\w+) \(@', $index['definition'], $type);
            $index['type'] = "USING ".trim($type[1]);
        }
        return $indexes;
    }

    protected function dropIndex(string $index, string $table) : string
    {
        return "DROP INDEX $index;";
    }

    protected function dropPrimaryKey(string $table) : string
    {
        return "ALTER TABLE $table DROP PRIMARY KEY;";
    }
}

