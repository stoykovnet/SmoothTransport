<?php

require_once constant('ROOT') . 'model/DBConnection.php';

class ModelTemplate {

    private $className;
    private $classFields = array();
    // Ignore: it's just for debugging purposes. Used in to_dump() method.
    private $instanceId;
    private static $currentInstanceId = 0;

    /**
     * Create an empty model class instance that corresponds to a table in the 
     * database. For all columns there will be getters and setters (magic methods).
     * With the instance you will be able to fetch/change data from the database. 
     * @param string $className The model class you wish to create.
     */
    public function __construct($className) {
        $this->className = $className;

        $db = new DBConnection();
        $columns = $db->get_table_columns_names($this->convert_to_table_name($className));
        foreach ($columns as $c) {
            $this->classFields[$c] = null;
        }

        // Ignore: it's for debugging purposes. Used in to_dump() method.
        ModelTemplate::$currentInstanceId++;
        $this->instanceId = ModelTemplate::$currentInstanceId;
    }

    /**
     * Convert a class name to a table name. In PHP for naming classes we use the 
     * CamelCase, while for naming tables in MySQL we usually use the snake_case 
     * convention.
     * @param string $className The class name that you wish to have converted.
     * @return string Class name converted to snake_case convention.
     */
    private function convert_to_table_name($className) {
        $tableName = '';
        // SmsUser can be written also as SMSUser. Different preferences.
        if (strtolower($className) === 'smsuser') {
            $tableName = 'sms_user';
        } elseif (strtolower($className) === 'sms') {
            $tableName = 'sms';
        } else {

            $parts = preg_split('/(?=[A-Z])/', $className, -1, PREG_SPLIT_NO_EMPTY);

            for ($i = 0; $i < count($parts); $i++) {
                $parts[$i] = strtolower($parts[$i]);
                if ($i < 1) {
                    $tableName = $parts[$i];
                } else {
                    $tableName .= '_' . $parts[$i];
                }
            }
        }
        return $tableName;
    }

    /**
     * Change the value of a variable that belongs to this instance.
     * @param mixed $variable The variable value to change.
     * @param mixed $value The new value you want to set.
     */
    public function __set($variable, $value) {
        if (key_exists($variable, $this->classFields)) {
            $this->classFields[$variable] = $value;
        } else {
            $this->show_fatal_error('set', $variable);
        }
    }

    /**
     * Get the value of a variable that belongs to this instance.
     * @param mixed $variable The variable value that you want to get.
     * @return mixed Requested value.
     */
    public function __get($variable) {
        if (key_exists($variable, $this->classFields)) {
            return $this->classFields[$variable];
        } else {
            $this->show_fatal_error('get', $variable);
            return null;
        }
    }

    /**
     * Show fatal error and halt application execution if a method tries to alter
     * or retrieve a variable that does not belong to this class.
     * @param string $method
     * @param string $variable
     */
    private function show_fatal_error($method, $variable) {
        $trace = debug_backtrace();
        trigger_error(
                'Call to undefined ' . $method . 'ter '
                . ucfirst($this->get_class_name()) . '::' . $variable
                . ' in <strong>' . $trace[0]['file'] . '</strong>'
                . ' on line <strong>' . $trace[0]['line'] . '</strong>'
                . '<br>'
                . '<strong>Note:</strong> '
                . 'There is no ' . ucfirst($this->get_class_name()) . ' property called '
                . '"' . $variable . '". '
                . ucfirst($this->get_class_name()) . "'s properties are: "
                . implode(', ', array_keys($this->classFields)) . '. <br>'
                . 'Via __' . $method . '() ', E_USER_ERROR);
    }

    /**
     * Dump information about this instance. The output is similar to var_dump function,
     * but it shows the name of the class, instead of object(ModelTemplate).
     */
    public function to_dump() {
        // Object's class name, the number of the instance from this class.
        $dump = 'object(' . $this->get_class_name() . ')#'
                . $this->instanceId
                . ' (' . count($this->classFields) . ') { ';

        // This instance's fields with their type and visability.
        foreach ($this->classFields as $variable => $value) {
            $dump .= '["' . $variable . '":"' . $this->get_class_name() . '":private]=> ';

            // If this is not a primitive value, we have to traverse deeper.
            switch (gettype($value)) {
                default:
                    $dump .= gettype($value) . '(' . strlen($value) . ') ' . '"' . $value . '" ';
                    break;
                case 'array':
                    ob_start();
                    var_dump($value);
                    $dump .= ob_get_clean();
                    break;
                case 'object':
                    if (get_class($value) === 'ModelTemplate') {
                        $dump .= $value->to_dump();
                    } else {
                        ob_start();
                        var_dump($value);
                        $dump .= ob_get_clean();
                    }
                    break;
            }
        }

        $dump .= ' }';
        echo $dump;
    }

    /**
     * Get the class name of this model class instance.
     * @return string $this->className
     */
    public function get_class_name() {
        return ucfirst($this->className);
    }

    /**
     * Get a single element from this type specified by criteria of your choice.
     * If there is no element with such criteria, the method will return NULL.
     * More than one criteria can be used to get an element.
     * @param int $id The id of the instance that you want to get.
     * @param string|array(string) $where
     * @param string|array(string) $value
     * @return ModelTemplate|null
     */
    public function get_single($where, $value) {
        $db = new DBConnection();
        $data = $db->select($this->convert_to_table_name($this->get_class_name())
                , '*', $where, $value);

        if ($data) {
            return $this->build_instance($data[0]);
        } else {
            return null;
        }
    }

    /**
     * Get all elements from this type that are in the database. If there are no
     * elements, this method will return NULL. If where clause is specified, a set
     * of elements will be returned or NULL.
     * @param string|array(string) $where Optional
     * @param string|array(string) $value Optional
     * @return array(ModelTemplate)|null
     */
    public function get_all($where = null, $value = null) {
        $db = new DBConnection();
        $data = null;
        if ($where && $value) {
            $data = $db->select($this->convert_to_table_name($this->get_class_name())
                    , '*', $where, $value);
        } else {
            $data = $db->select($this->convert_to_table_name($this->get_class_name())
                    , '*');
        }

        $list = array();
        if ($data) {
            foreach ($data as $datum) {
                $list[] = $this->build_instance($datum);
            }
            return $list;
        } else {
            return null;
        }
    }

    /**
     * Insert selected instance of this type in the database. The instance will 
     * not be inserted if it does not contain any data.
     * Returns the ID of the inserted instance on success.
     * @return int|null
     */
    public function submit_new() {
        $db = new DBConnection();

        $dataToInsert = array();
        foreach ($this->classFields as $variable => $value) {
            if ($value) {
                $dataToInsert[$variable] = $value;
            }
        }

        if (count($dataToInsert)) {
            return $db->insert($this->convert_to_table_name($this->get_class_name())
                            , $dataToInsert);
        } else {
            return null;
        }
    }

    /**
     * Update selected instance data of this type in the database. The instance
     * data will not be changed, in case it remains with no data.
     * Returns the number of affected rows on success.
     * @return int|null
     */
    public function submit_changes() {
        if ($this->id) {
            $db = new DBConnection();

            $dataToUpdate = array();
            foreach ($this->classFields as $variable => $value) {
                if ($value && $variable !== 'id') {
                    $dataToUpdate[$variable] = $value;
                }
            }

            if (count($dataToUpdate)) {
                return $db->update($this->convert_to_table_name($this->className)
                                , $dataToUpdate, $this->id);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Delete selected instance of this type in the database.
     * Returns the number of affected rows on success. 
     * @return int|null
     */
    public function delete() {
        if ($this->id) {
            $db = new DBConnection();

            return $db->delete($this->convert_to_table_name($this->className)
                            , $this->id);
        } else {
            return null;
        }
    }

    /**
     * Count how many instances of this type reside in the database. If WHERE
     * condition is provided, only instances that correspond with the condition
     * will be counted.
     * @param string|array(string) $where Optional.
     * @param string|array(string) $value Optional.
     * @return int
     */
    public function count($where = null, $value = null) {
        $db = new DBConnection();
        $data = null;
        if ($where && $value) {
            $data = $db->select($this->convert_to_table_name($this->get_class_name())
                    , 'COUNT(id)', $where, $value);
        } else {
            $data = $db->select($this->convert_to_table_name($this->get_class_name())
                    , 'COUNT(id)');
        }

        return intval($data[0]['COUNT(id)']);
    }

    /**
     * Fill model class instance with data that is fetched from the database.
     * @param array $data
     * @return ModelTemplate
     */
    private function build_instance($data) {
        $instance = new ModelTemplate($this->get_class_name());

        foreach ($data as $variable => $value) {
            $instance->__set($variable, $value);
        }

        return $instance;
    }

    /**
     * Retrieve all fields of this instance in array.
     * @return array
     */
    public function get_all_fields() {
        return $this->classFields;
    }

    /**
     * Add new property to this class.
     * @param string $variable
     * @param mixed $value
     */
    public function add_field($variable, $value) {
        $this->classFields[$variable] = $value;
    }

}
