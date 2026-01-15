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

    /**
     * Constructor - creates a new database connection
     */
    public function __construct() {
        $this->connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }

    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Connect to the database
     */
    public function connect($host, $username, $password, $database) {
        try {
            $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true
            ];
            
            $this->connection = new PDO($dsn, $username, $password, $options);
            return $this;
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Execute a raw SQL query
     */
    public function query($sql, $params = []) {
        try {
            $this->query = $this->connection->prepare($sql);
            $this->query->execute($params);
            return $this;
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    /**
     * Fetch all results
     */
    public function fetchAll() {
        return $this->query->fetchAll();
    }

    /**
     * Fetch a single row
     */
    public function fetch() {
        return $this->query->fetch();
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
        return $this->connection->lastInsertId();
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
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($data);
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Insert failed: " . $e->getMessage());
        }
    }

    /**
     * Update data in a table
     */
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(array_merge($data, $whereParams));
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Update failed: " . $e->getMessage());
        }
    }

    /**
     * Delete data from a table
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Delete failed: " . $e->getMessage());
        }
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
        $this->connection = null;
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}

    /**
     * Prevent unserializing of the instance
     */
    private function __wakeup() {}
}
