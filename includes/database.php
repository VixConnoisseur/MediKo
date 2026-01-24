<?php
/**
 * Database Connection and Query Builder
 * 
 * Handles database connections and provides methods for common database operations.
 */
class Database {
    private $connection;
    private $query;
    private static $instance = null;
    private $pdo;

    /**
     * Constructor - creates a new database connection
     */
    private function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => true
                ]
            );
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed. Please try again later.");
            }
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function getPdo() {
        return $this->pdo;
    }

    /**
     * Execute a raw SQL query
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            
            // Check if we're using named parameters
            $isNamed = !empty($params) && is_string(key($params));
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $param = $isNamed ? (':' . ltrim($key, ':')) : ($key + 1);
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($param, $value, $paramType);
            }
            
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database error in query: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }

    /**
     * Fetch all results
     */
    public function fetchAll($sql, $params = []) {
        try {
            return $this->query($sql, $params)->fetchAll();
        } catch (PDOException $e) {
            error_log("Database fetchAll error: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }

    /**
     * Fetch a single row
     */
    public function fetch($sql, $params = []) {
        try {
            return $this->query($sql, $params)->fetch();
        } catch (PDOException $e) {
            error_log("Database fetch error: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }

    /**
     * Get row count
     */
    public function rowCount() {
        return $this->query->rowCount();
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollback() {
        return $this->connection->rollback();
    }

    /**
     * Insert data into a table
     */
    public function insert($table, $data) {
        $keys = array_keys($data);
        $fields = '`' . implode('`, `', $keys) . '`';
        $values = ':' . implode(', :', $keys);
        
        $sql = "INSERT INTO `$table` ($fields) VALUES ($values)";
        $this->query($sql, $data);
        return $this->pdo->lastInsertId();
    }

    /**
     * Update data in a table
     */
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        $setParams = [];
        
        // Handle SET clause with named parameters
        foreach ($data as $key => $value) {
            $paramName = 'set_' . $key;  // Prefix to avoid conflicts with WHERE params
            $set[] = "`$key` = :$paramName";
            $setParams[$paramName] = $value;
        }
        
        // Build the SQL query
        $sql = "UPDATE `$table` SET " . implode(', ', $set);
        
        // Handle WHERE clause - check if it uses named or positional parameters
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }
        
        // Combine parameters (SET and WHERE)
        $params = array_merge($setParams, $whereParams);
        
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Delete data from a table
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM `$table` WHERE $where";
        return $this->query($sql, $params)->rowCount();
    }


    /**
     * Get a single record by ID
     */
    public function find($table, $id, $idColumn = 'id') {
        $sql = "SELECT * FROM {$table} WHERE {$idColumn} = :id LIMIT 1";
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Find failed: " . $e->getMessage());
        }
    }

    /**
     * Get all records from a table
     */
    public function all($table, $orderBy = '', $limit = null, $offset = null) {
        $sql = "SELECT * FROM {$table}";
        
        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
            if ($offset !== null) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }
        
        try {
            $stmt = $this->connection->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Fetch all failed: " . $e->getMessage());
        }
    }

    /**
     * Close the database connection
     */
    public function close() {
        $this->pdo = null;
    }

    public function prepare($sql) {
        return $this->pdo->prepare($sql);
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}

    /**
     * Prevent unserializing of the instance
     */
    public function __wakeup() {
    // Prevent unserialization
}
}
