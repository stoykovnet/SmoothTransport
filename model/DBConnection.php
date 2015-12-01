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
                $host = '';
                $db = '';
                $user = '';
                $pass = '';

                if (constant('ROOT') === 'C:/wamp/www/smoothTransport/') {
                    $host = 'localhost';
                    $db = 'smooth_transport';
                    $user = 'root';
                    $pass = '';
                } else {
                    $host = 'localhost';
                    $db = 'cosyclim_smooth';
                    $user = 'cosyclim_smooth';
                    $pass = 'novagodina2016';
                }

                $this->dbConn = new PDO("mysql:host=$host;dbname=$db;"
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
        $logPath = '../_bin/debug/db_error_log.txt';
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

    public function select($table, $columns, $where = null, $value = null) {
        $results = null;
        $query = $this->build_select_statement($table, $columns);

        $db = $this->get_db_connection();
        try {
            $stmt = null;
            if ($where && $value) {
                $stmt = $this->build_select_with_where_clause($db, $query, $where, $value);
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

    private function build_select_with_where_clause($db, $query, $where, $value) {
        $stmt = null;
        if (is_array($where) && is_array($value)) {
            $clause = ' WHERE ';
            for ($i = 0; $i < count($where) && $i < count($value); $i++) {
                $clause .= $where[$i] . '=:' . $where[$i];
                if ($i < count($where) - 1 && $i < count($value) - 1) {
                    $clause .= ' AND ';
                }
            }

            $stmt = $db->prepare($query . $clause);
            for ($i = 0; $i < count($where) && $i < count($value); $i++) {
                $stmt->bindValue(':' . $where[$i], $value[$i]);
            }
        } else {
            $stmt = $db->prepare($query . ' WHERE ' . $where . '=:' . $where);
            $stmt->bindValue(':' . $where, $value);
        }

        return $stmt;
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
