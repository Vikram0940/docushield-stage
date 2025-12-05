<?php namespace App\Validation;

use Config\Database;

class CustomRules
{
    /**
     * is_unique_soft[table.field,ignoreField,ignoreValue]
     *
     * Same as CI4’s is_unique but adds deleted_at IS NULL.
     */
    public function is_unique_soft(string $value, string $params, array $data, ?string &$error = null): bool
    {
        // Break parameters: table.field, ignoreField, ignoreValue
        [$tableField, $ignoreField, $ignoreValue] = array_pad(
            explode(',', $params, 3),
            3,
            null
        );

        // Split table and field
        [$table, $field] = explode('.', $tableField, 2);

        // Detect deleted column name by checking table schema
        $db       = Database::connect();
        $fields   = $db->getFieldNames($table);
        $delCol   = in_array('deleted_date', $fields) ? 'deleted_date' : 'deleted_at';

        // Build query
        $builder = $db->table($table)
            ->select('1')
            ->where($field, $value)
            ->where($delCol, null); // soft-delete filter

        // Ignore current record if provided
        if ($ignoreField !== null && $ignoreValue !== null) {
            $builder->where($ignoreField . ' !=', $ignoreValue);
        }

        // Return true if no match found
        return $builder->get()->getRow() === null;
    }
}
