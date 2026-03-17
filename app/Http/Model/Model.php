<?php

namespace SearchTracker\Rus\Http\Model;

class Model
{
    protected $_wpdb;
    protected $tableName;
    private static $instances = [];

    public function __construct()
    {
        global $wpdb;
        $this->_wpdb = $wpdb;
        $this->tableName = $this->_wpdb->prefix . $this->tableName;
    }

    public static function getInstance()
    {
        $cls = static::class;
        if (!isset(static::$instances[$cls])) {
            static::$instances[$cls] = new static();
        }

        return static::$instances[$cls];
    }

    public static function find($id)
    {
        $model = static::getInstance();
        return $model->_wpdb->get_row("SELECT * FROM $model->tableName WHERE id = $id");
    }

    public function store($data)
    {
        $model = static::getInstance();

        // Insert data into the table
        $inserted = $model->_wpdb->insert($model->tableName, $data);

        // Check if the insertion was successful
        if ($inserted) {
            // Return the inserted ID
            return $model->_wpdb->insert_id;
        }

        // If insertion failed, return false or handle the error
        return false;
    }


    public function update($data, $id)
    {
        $model = static::getInstance();
        return $model->_wpdb->update($model->tableName, $data, array('id' => $id));
    }

    public function destroy($id)
    {
        $model = static::getInstance();
        return $model->_wpdb->delete($model->tableName, array('id' => $id));
    }

    /**
     * Retrieve records from the table.
     *
     * @param int|null $limit The number of records to retrieve. If null, retrieves all records.
     * @param int $offset The offset for pagination.
     * @return array An array of records.
     */
    public static function all($limit = null, $offset = 0, $condition = '')
    {
        $model = static::getInstance();

        // Base query
        $query = "SELECT * FROM $model->tableName";

        // Add condition if provided
        if (!empty($condition)) {
            $query .= " WHERE $condition";
        }

        // Add ORDER BY clause
        $query .= " ORDER BY ID DESC";

        // Add LIMIT and OFFSET if $limit is not null
        if ( ! is_null( $limit ) ) {
            $query = $model->_wpdb->prepare(
                "$query LIMIT %d OFFSET %d",
                $limit,
                $offset
            );
        }
        
        return $model->_wpdb->get_results($query);
    }
}
