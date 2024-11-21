<?php
namespace common\components\db;

use Yii;

class Schema extends \tigrov\pgsql\Schema
{
    /**
     * @return ColumnSchema
     * @throws \yii\base\InvalidConfigException
     */
    protected function createColumnSchema()
    {
        return Yii::createObject(ColumnSchema::className());
    }

    protected function findColumns($table)
    {
        $tableName = $this->db->quoteValue($table->name);
        $schemaName = $this->db->quoteValue($table->schemaName);

        $orIdentity = '';
        if (version_compare($this->db->serverVersion, '12.0', '>=')) {
            $orIdentity = 'OR a.attidentity != \'\'';
        }

        $sql = <<<SQL
SELECT
    d.nspname AS table_schema,
    c.relname AS table_name,
    a.attname AS column_name,
    COALESCE(td.typname, tb.typname, t.typname) AS data_type,
    COALESCE(td.typtype, tb.typtype, t.typtype) AS type_type,
    (SELECT nspname FROM pg_namespace WHERE oid = COALESCE(td.typnamespace, tb.typnamespace, t.typnamespace)) AS type_scheme,
    a.attlen AS character_maximum_length,
    pg_catalog.col_description(c.oid, a.attnum) AS column_comment,
    COALESCE(NULLIF(a.atttypmod, -1), t.typtypmod) AS modifier,
    NOT (a.attnotnull OR t.typnotnull) AS is_nullable,
    COALESCE(t.typdefault, pg_get_expr(ad.adbin, ad.adrelid)::varchar) AS column_default,
    COALESCE(pg_get_expr(ad.adbin, ad.adrelid) ~ 'nextval', false) {$orIdentity} AS is_autoinc,
    pg_get_serial_sequence(quote_ident(d.nspname) || '.' || quote_ident(c.relname), a.attname) AS sequence_name,
    CASE WHEN COALESCE(td.typtype, tb.typtype, t.typtype) = 'e'::char
        THEN array_to_string((SELECT array_agg(enumlabel) FROM pg_enum WHERE enumtypid = COALESCE(td.oid, tb.oid, a.atttypid))::varchar[], ',')
        ELSE NULL
    END AS enum_values,
    information_schema._pg_char_max_length(information_schema._pg_truetypid(a, t), information_schema._pg_truetypmod(a, t))::numeric AS size,
    a.attnum = ANY (ct.conkey) AS is_pkey,
    COALESCE(NULLIF(a.attndims, 0), NULLIF(t.typndims, 0), (t.typcategory='A')::int) AS dimension,
    CASE WHEN t.typndims > 0 THEN tb.typdelim ELSE t.typdelim END AS delimiter,
    COALESCE(td.oid, tb.oid, a.atttypid) AS type_id,
    t.typname AS attr_type
FROM
    pg_class c
    LEFT JOIN pg_attribute a ON a.attrelid = c.oid
    LEFT JOIN pg_attrdef ad ON a.attrelid = ad.adrelid AND a.attnum = ad.adnum
    LEFT JOIN pg_type t ON a.atttypid = t.oid
    LEFT JOIN pg_type tb ON (a.attndims > 0 OR t.typcategory='A') AND t.typelem > 0 AND t.typelem = tb.oid OR t.typbasetype > 0 AND t.typbasetype = tb.oid
    LEFT JOIN pg_type td ON t.typndims > 0 AND t.typbasetype > 0 AND tb.typelem = td.oid
    LEFT JOIN pg_namespace d ON d.oid = c.relnamespace
    LEFT JOIN pg_constraint ct ON ct.conrelid = c.oid AND ct.contype = 'p'
WHERE
    a.attnum > 0 AND t.typname != '' AND NOT a.attisdropped
    AND c.relname = {$tableName}
    AND d.nspname = {$schemaName}
ORDER BY
    a.attnum;
SQL;

        $columns = $this->db->createCommand($sql)->queryAll();
        if (empty($columns)) {
            return false;
        }
        foreach ($columns as $column) {
            if ($this->db->slavePdo->getAttribute(\PDO::ATTR_CASE) === \PDO::CASE_UPPER) {
                $column = array_change_key_case($column, CASE_LOWER);
            }
            $column = $this->loadColumnSchema($column);
            $table->columns[$column->name] = $column;
            if ($column->isPrimaryKey) {
                $table->primaryKey[] = $column->name;
                if ($table->sequenceName === null) {
                    $table->sequenceName = $column->sequenceName;
                }
                $column->defaultValue = null;
            } elseif ($column->defaultValue) {
                if (in_array($column->type, static::DATE_TYPES) && in_array($column->defaultValue, static::CURRENT_TIME_DEFAULTS)) {
                    $column->defaultValue = new \DateTime();
                } elseif (preg_match("/^B?'(.*?)'::/", $column->defaultValue, $matches)) {
                    $column->defaultValue = $column->phpTypecast($matches[1]);
                } elseif (preg_match('/^(\()?(.*?)(?(1)\))(?:::.+)?$/', $column->defaultValue, $matches)) {
                    if ($matches[2] === 'NULL') {
                        $column->defaultValue = null;
                    } else {
                        $column->defaultValue = $column->phpTypecast($matches[2]);
                    }
                } else {
                    $column->defaultValue = $column->phpTypecast($column->defaultValue);
                }
            }
        }

        return true;
    }
}