<?php
require_once 'config.php';

class Database
{
    private $connection;
    private static $instance = null;

    private function __construct()
    {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("Lỗi kết nối database: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Lỗi query: " . $e->getMessage());
        }
    }

    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function fetchOne($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->query($sql, $data);

        return $this->connection->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = [])
    {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "$column = :$column";
        }
        $setClause = implode(', ', $setClause);

        // Thay thế ? bằng named parameters trong WHERE clause
        $whereWithNamedParams = $where;
        if (!empty($whereParams)) {
            $paramIndex = 0;
            foreach ($whereParams as $value) {
                $paramName = "where_param_" . $paramIndex;
                $whereWithNamedParams = preg_replace('/\?/', ":$paramName", $whereWithNamedParams, 1);
                $data[$paramName] = $value;
                $paramIndex++;
            }
        }

        $sql = "UPDATE $table SET $setClause WHERE $whereWithNamedParams";
        $stmt = $this->query($sql, $data);
        return $stmt->rowCount();
    }

    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    public function commit()
    {
        return $this->connection->commit();
    }

    public function rollback()
    {
        return $this->connection->rollback();
    }
}

// Hàm helper để lấy instance database
function getDB()
{
    return Database::getInstance();
}

// Hàm kiểm tra kết nối database
function testDatabaseConnection()
{
    try {
        $db = getDB();
        $result = $db->fetchOne("SELECT 1 as test");
        return $result && isset($result['test']);
    } catch (Exception $e) {
        return false;
    }
}
