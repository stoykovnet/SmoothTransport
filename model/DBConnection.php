<?php

class DBConnection {

    private $dbConn = null; // To store PDO instance.

    /*
     * Initializes PDO instance immediately.
     */

    public function __construct() {
        $this->get_db_connection();
    }

    /*
     * Create a new PDO instance or retrieve it, if it has already been created.
     */

    private function get_db_connection() {
        if ($this->dbConn === null) {
            try {
                $host = 'localhost';
                $db = 'smooth_transport';
                $user = 'root';
                $pass = '';
                $set = 'utf8mb4_unicode_ci';

                $this->dbConn = new PDO("mysql:host=$host;dbname=$db;charset=$set"
                        , $user, $pass);

                return $this->dbConn;
            } catch (PDOException $ex) {
                $this->write_to_log($ex->getMessage());
            }
        } else {
            return $this->dbConn;
        }
    }

    /*
     * Save a log entry, in case of database-related problems.
     * The log entries can be found in debug folder.
     */

    private function write_to_log($entry) {
        $logPath = '../debug/db_error_log.txt';
        $dbg = debug_backtrace();
        if (file_exists($logPath)) {
            $file = file_get_contents($logPath);
            file_put_contents($logPath, '[' . date('Y-m-d H:i:s') . ']: '
                    . $entry
                    . ' @' . $dbg[1]['line']
                    . ' in ' . $dbg[1]['file']
                    . ' ' . $dbg[1]['function']
                    . "\r\n"
                    . $file);
        } else {
            file_put_contents($logPath, '[' . date('Y-m-d H:i:s') . ']: '
                    . $entry
                    . ' @' . $dbg[1]['line']
                    . ' in ' . $dbg[1]['file']
                    . ' ' . $dbg[1]['function']
                    . "\r\n");
        }
    }

    /*
     * Get the prefix that is used before all tables.
     */

    private function get_table_prefix() {
        return 'ccst16_';
    }

    /*
     * Get a single row by id or get all rows from a specified table.
     * All columns can be selected by '*'.
     * Certain columns can be selected, if they're specified in an array.
     */

    public function select($table, $columns, $id = null) {
        $results = null;
        $query = $this->build_select_statement($table, $columns);

        $db = $this->get_db_connection();
        try {
            $stmt = null;
            if ($id) {
                $stmt = $db->prepare($query . ' WHERE id=:id');
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $stmt = $db->query($query);
            }

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[] = $row;
            }
        } catch (Exception $ex) {
            $db->write_to_log($ex->getMessage());
        }

        return $results;
    }

    /*
     * Get a select query for any given table and any given number of columns.
     */

    private function build_select_statement($table, $columns) {
        $query = 'SELECT ';

        if (is_array($columns)) {
            for ($i = 0; $i < count($columns); $i++) {
                $query .= $columns[$i];
                if ($i < count($columns) - 1) {
                    $query .= ', ';
                }
            }
        } else {
            $query .= $columns;
        }

        return $query .= ' FROM ' . $this->get_table_prefix() . $table;
    }

    /*
     * Insert data in any given table into specified columns by an array.
     * Use array('columnName' => 'value') to specify the columns' values.
     */

    public function insert($table, $data) {
        $db = $this->get_db_connection();
        try {
            $binds = array();
            while (key($data)) {
                $datum = current($data);
                $binds[':' . key($data)] = $datum;
                next($data);
            }

            $stmt = $db->prepare($this->build_insert_statement($table, $data));
            $stmt->execute($binds);

            return $db->lastInsertId();
        } catch (Exception $ex) {
            $db->write_to_log($ex->getMessage());
            return null;
        }
    }

    /*
     * Get an insert query for any given table and any given number of columns.
     */

    private function build_insert_statement($table, $data) {
        $columns = ' (';
        $values = ' VALUES(';

        $i = 0;
        while (key($data)) {
            $columns .= key($data);
            $values .= ':' . key($data);

            if ($i < count($data) - 1) {
                $columns .= ', ';
                $values .= ', ';
            }

            $i++;
            next($data);
        }

        $columns .= ')';
        $values .= ')';

        return 'INSERT INTO ' . $this->get_table_prefix() . $table . $columns . $values;
    }

    /*
     * Update a set of columns in any given table specified by id.
     * Use array('columnName' => 'value') to specify the new columns' values.
     */

    public function update($table, $data, $id) {
        $db = $this->get_db_connection();
        try {
            $binds = array();
            while (key($data)) {
                $datum = current($data);
                $binds[':' . key($data)] = $datum;
                next($data);
            }
            $binds[':id'] = $id;

            $stmt = $db->prepare($this->build_update_statement($table, $data));
            $stmt->execute($binds);

            return $stmt->rowCount();
        } catch (Exception $ex) {
            $db->write_to_log($ex->getMessage());
            return false;
        }
    }

    /*
     * Get an update query for any given table and any given number of columns.
     */

    private function build_update_statement($table, $data) {
        $set = ' SET ';

        $i = 0;
        while (key($data)) {
            $set .= key($data) . '=:' . key($data);

            if ($i < count($data) - 1) {
                $set .= ', ';
            }

            $i++;
            next($data);
        }

        return 'UPDATE ' . $this->get_table_prefix() . $table . $set . ' WHERE id=:id';
    }

    /*
     * Delete a row by id in a specified table.
     */

    public function delete($table, $id) {
        $db = $this->get_db_connection();
        try {
            $stmt = $db->prepare('DELETE FROM ' . $this->get_table_prefix() . $table . ' WHERE id=:id');
            $stmt->execute(array(':id' => $id));

            return $stmt->rowCount();
        } catch (Exception $ex) {
            $db->write_to_log($ex->getMessage());
            return false;
        }
    }

    public function get_table_columns_names($table) {
        $db = $this->get_db_connection();
        try {
            $stmt = $db->prepare('DESCRIBE ' . $this->get_table_prefix() . $table);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $ex) {
            $db->write_to_log($ex->getMessage());
            return false;
        }
    }

}
