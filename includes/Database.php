<?php
/**
 * Database Class
 * PDO wrapper for database operations with security features
 */

class Database {
    private static $instance = null;
    private $pdo;
    private $stmt;
    private $error;

    /**
     * Private constructor - singleton pattern
     */
    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database Connection Error: " . $this->error);
            
            if (APP_ENV === 'development') {
                die("Database Connection Error: " . $this->error);
            } else {
                die("A database error occurred. Please try again later.");
            }
        }
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
     * Get PDO connection
     */
    public function getConnection() {
        return $this->pdo;
    }

    /**
     * Prepare and execute query
     */
    public function query($sql, $params = []) {
        $this->stmt = $this->pdo->prepare($sql);
        
        try {
            $this->stmt->execute($params);
            return $this;
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }

    /**
     * Fetch single row
     */
    public function fetch() {
        return $this->stmt->fetch();
    }

    /**
     * Fetch all rows
     */
    public function fetchAll() {
        return $this->stmt->fetchAll();
    }

    /**
     * Fetch column
     */
    public function fetchColumn($columnNumber = 0) {
        return $this->stmt->fetchColumn($columnNumber);
    }

    /**
     * Get row count
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }

    /**
     * Check if in transaction
     */
    public function inTransaction() {
        return $this->pdo->inTransaction();
    }

    /**
     * Execute raw SQL
     */
    public function exec($sql) {
        return $this->pdo->exec($sql);
    }

    /**
     * Call stored procedure
     */
    public function callProcedure($procedureName, $params = []) {
        $placeholders = array_fill(0, count($params), '?');
        $sql = "CALL {$procedureName}(" . implode(',', $placeholders) . ")";
        
        $this->stmt = $this->pdo->prepare($sql);
        $this->stmt->execute($params);
        
        $results = [];
        do {
            $results[] = $this->stmt->fetchAll();
        } while ($this->stmt->nextRowset());
        
        return $results;
    }

    /**
     * Insert data into table
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->query($sql, $data);
        return $this->lastInsertId();
    }

    /**
     * Update data in table
     */
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        $params = array_merge($data, $whereParams);
        $this->query($sql, $params);
        
        return $this->rowCount();
    }

    /**
     * Delete from table
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $this->query($sql, $params);
        return $this->rowCount();
    }

    /**
     * Select from table
     */
    public function select($table, $columns = '*', $where = '', $params = [], $orderBy = '', $limit = '') {
        $sql = "SELECT {$columns} FROM {$table}";
        
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Select single row
     */
    public function selectOne($table, $columns = '*', $where = '', $params = []) {
        $result = $this->select($table, $columns, $where, $params, '', 1);
        return $result ? $result[0] : null;
    }

    /**
     * Count rows
     */
    public function count($table, $where = '', $params = []) {
        $sql = "SELECT COUNT(*) FROM {$table}";
        
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        return $this->query($sql, $params)->fetchColumn();
    }

    /**
     * Check if record exists
     */
    public function exists($table, $where, $params = []) {
        return $this->count($table, $where, $params) > 0;
    }

    /**
     * Get table columns
     */
    public function getColumns($table) {
        $sql = "SHOW COLUMNS FROM {$table}";
        return $this->query($sql)->fetchAll();
    }

    /**
     * Truncate table
     */
    public function truncate($table) {
        $sql = "TRUNCATE TABLE {$table}";
        return $this->exec($sql);
    }

    /**
     * Close connection
     */
    public function close() {
        $this->pdo = null;
        self::$instance = null;
    }
}