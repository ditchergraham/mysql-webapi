<?php
/**
 * This class handles the requests to/from the database.
 *
 */
class DatabaseModel
{
    private $dbHost;
    private $dbUser;
    private $dbPassword;
    private $dbName;

    private $con;
    private $myConn;
    private $result;
    private $myQuery;
    private $numResults;

    /**
     * DatabaseModel constructor.
     */
    public function __construct()
    {
        $this->dbHost = file_get_contents('./env/host.txt');
        $this->dbUser = array(
            'reader' => file_get_contents('./env/user.txt'),
            'writer' => file_get_contents('./env/user.txt')
        );
        $this->dbPassword = array(
            'reader' => file_get_contents('./env/password.txt'),
            'writer' => file_get_contents('./env/password.txt')
        );
        $this->dbName = file_get_contents('./env/database.txt');
        $this->con = false;
        $this->myConn = null;
        $this->result = array();
        $this->myQuery = "";
        $this->numResults = "";
    }

    /**
     * This function allows the code to connect to the database.
     *
     * @param bool $onlyRead Indicates if user only wants to read data instead of writing data
     * @return bool Returns the result of this process
     */
    public function connect($onlyRead = true)
    {
        // Check if the connection has already been made
        if (!$this->con) {
            // Check if user only wants to read data
            if ($onlyRead) {
                $this->myConn = new mysqli(
                    $this->dbHost,
                    $this->dbUser['reader'],
                    $this->dbPassword['reader'],
                    $this->dbName
                );
            } else {
                $this->myConn = new mysqli(
                    $this->dbHost,
                    $this->dbUser['writer'],
                    $this->dbPassword['writer'],
                    $this->dbName
                );
            }
            // Check if the new mysqli object caused any errors
            if ($this->myConn->connect_errno > 0) {
                array_push($this->result, $this->myConn->connect_error);
                return false;
            } else {
                $this->con = true;
                return true; // Connection has been made
            }
        } else {
            return true; // Connect has been already made
        }
    }

    /**
     * This function allows the code to disconnect from the database.
     */
    public function disconnect()
    {
        // Check if there is a connection with the database
        if ($this->con) {
            // Check if the code can close the connection with the database
            if ($this->myConn->close()) {
                $this->con = false;
                return true;
            } else {
                return false; // Failed to close the connection with the database
            }
        } else {
            return false; // There's no connection with the database to even close the connection
        }
    }

    /**
     * This function allows the code to execute an sql query on the database.
     *
     * @param String $sql The sql query that needs to be executed
     * @return bool         The result of the process
     */
    public function sql($sql)
    {
        $query = $this->myConn->query($sql);
        $this->myQuery = $sql;

        // Check if query is set
        if ($query) {
            $this->numResults = $query->num_rows;

            // Loop through the returned rows
            for ($i = 0; $i < $this->numResults; $i++) {
                $r = $query->fetch_array();
                $key = array_keys($r);

                // Loop through again to sanitize keys to only allow alpha values
                for ($j = 0; $j < count($key); $j++) {
                    if (!is_int($key[$j])) {
                        if ($query->num_rows >= 1) {
                            $this->result[$i][$key[$j]] = $r[$key[$j]];
                        } else {
                            $this->result = null;
                        }
                    }
                }
            }

            return true; // Query was successful
        } else {
            array_push($this->result, $this->myConn->error);
            return false; // No rows where returned from the database
        }
    }

    /**
     * This function allows the code to execute a SELECT query on the database.
     *
     * @param String $table The table which its rows need to be selected
     * @param String $columns The columns that needs to be selected
     * @param String $join The tables that needs to be joined
     * @param String $where The filter that needs to be used on the returned rows
     * @param String $order The order which the returned rows needs to appear
     * @param String $limit The limit that needs to be used on the returned rows
     * @return bool             The result of the process
     */
    public function select($table, $columns = '*', $join = null, $where = null, $order = null, $limit = null)
    {
        // Build up an sql query from the passed variables
        $q = 'SELECT ' . $columns . ' FROM ' . $table;
        // Check if a table needs to be joined
        if ($join != null) {
            $q .= ' JOIN ' . $join;
        }

        // Check if the returned rows needs to be filtered
        if ($where != null) {
            $q .= ' WHERE ' . $where;
        }

        // Check if the returned rows needs to be ordered in a specific way
        if ($order != null) {
            $q .= ' ORDER BY ' . $order;
        }

        // Check if there needs to be a limit on the returned rows
        if ($limit != null) {
            $q .= ' LIMIT ' . $limit;
        }

        $this->myQuery = $q;

        // Check if the table exists
        if ($this->tableExists($table)) {
            $query = $this->myConn->query($q); // Execute query

            // Check if the sql query executed succesfully
            if ($query) {
                $this->numResults = $query->num_rows;

                for ($i = 0; $i < $this->numResults; $i++) {
                    $r = $query->fetch_array();
                    $key = array_keys($r);

                    // Loop through again to sanitize keys to only allow alpha values
                    for ($j = 0; $j < count($key); $j++) {
                        if (!is_int($key[$j])) {
                            if ($query->num_rows >= 1) {
                                $this->result[$i][$key[$j]] = $r[$key[$j]];
                            } else {
                                $this->result[$i][$key[$j]] = null;
                            }
                        }
                    }
                }

                return true; // Query was successful
            } else {
                array_push($this->result, $this->myConn->error);
                return false; // No rows where returned
            }
        } else {
            return false; // Table doesn't exists
        }
    }
    
    /**
     * This function inserts the given query in the database.
     *
     * @param String $table The table where the given sql query needs to be executed
     * @param array $params The table's column's values
     * @return bool             The result of the process
     */
    public function insert($table, $params = array())
    {
        // Check if the table exists
        if ($this->tableExists($table)) {
            $sql = 'INSERT INTO `' . $table . '` (`' . implode('`, `', array_keys($params)) . '`) VALUES 
            ("' . implode('", "', $params) . '")';
            $this->myQuery = $sql;

            // Insert the query into the database
            if ($ins = $this->myConn->query($sql)) {
                array_push($this->result, $this->myConn->insert_id);

                return true; // The data has been inserted
            } else {
                array_push($this->result, $this->myConn->error);

                return false; // The data hasn't been inserted
            }
        } else {
            return false; // Table doesn't exists
        }
    }

     /**
     * This function deletes the given table or rows from the database.
     *
     * @param String $table The table where the SQL server needs to delete rows or the table itself
     * @param String $where The rows that needs to be deleted
     * @return bool             The result of the process
     */
    public function delete($table, $where = null)
    {
        // Check if table exists
        if ($this->tableExists($table)) {

            // Check if the table itself needs to delete
            if ($where == null) {
                $delete = 'DROP TABLE ' . $table;
            } else {
                $delete = 'DELETE FROM ' . $table . ' WHERE ' . $where;
            }

            if ($del = $this->myConn->query($delete)) {
                array_push($this->result, $this->myConn->affected_rows);

                $this->myQuery = $delete;
                return true;
            } else {
                array_push($this->result, $this->myConn->error);

                return false;
            }

        } else {
            return false; // Table doesn't exists
        }
    }

    /**
     * This function updates the given table's rows in the database.
     *
     * @param String $table The table where the SQL server needs to update its rows
     * @param array $params The rows' new values
     * @param String $where Indicates what rows needs to be matched on
     * @return bool             The result of the process
     */
    public function update($table, $params = array(), $where)
    {
        // Check if table exists
        if ($this->tableExists($table)) {

            // Create an array to keep all the columns that need to be updated
            $args = array();

            // Loop through given params
            foreach ($params as $field => $value) {
                // Make sure string meets the SQL syntax
                $args[] = $field . '="' . $value . '"';
            }

            // Create the query
            $sql = 'UPDATE ' . $table . ' SET ' . implode(',', $args) . ' WHERE ' . $where;

            // Execute query in the database
            $this->myQuery = $sql;

            if ($query = $this->myConn->query($sql)) {
                array_push($this->result, $this->myConn->affected_rows);

                return true; // Rows have been updated succesfully
            } else {
                array_push($this->result, $this->myConn->error);

                return false; // Rows have not been updated succesfully
            }
        } else {
            return false; // Table doesn't exists
        }
    }
    /**
     * This function lets the know whether the given table exists in the database.
     *
     * @param String $table The table which needs to be checked on its appearence in the database
     * @return bool             The result of the process
     */
    private function tableExists($table)
    {
        $tableInDb = $this->myConn->query('SHOW TABLES FROM ' . $this->dbName . ' LIKE "' . $table . '"');

        // Check if the sql query was ran succesfully
        if ($tableInDb) {
            // Check if the table exists
            if ($tableInDb->num_rows == 1) {
                return true; // Table exists
            } else {
                array_push($this->result, $table . " does not exist in this database");

                return false; // Table doesn't exists
            }
        }
    }

    /**
     * This function returns the data to the application
     *
     * @return array The returned data
     */
    public function getResult()
    {
        $val = $this->result;
        $this->result = array();

        return $val;
    }

    /**
     * This function returns current SQL query.
     *
     * @return string SQL query
     */
    public function getSql()
    {
        $val = $this->myQuery;
        $this->myQuery = array();

        return $val;
    }

    /**
     * This function returns the amount of rows returned from the database.
     *
     * @return string The amount of rows returned
     */
    public function numRows()
    {
        $val = $this->numResults;
        $this->numResults = array();

        return $val;
    }

    /**
     * This function escapes given data's string.
     *
     * @param $data The data which needs to be escaped
     * @return mixed Returns the escaped version of the given data
     */
    public function escapeString($data)
    {
        return $this->myConn->real_escape_string($data);
    }

    /**
     * This function returns the auto generated id used in the latest query
     *
     * @return int Last auto generated id
     */
    public function insertId()
    {
        $val = $this->myConn->insert_id;

        return $val;
    }
}