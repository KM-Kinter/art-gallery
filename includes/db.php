<?php
require_once __DIR__ . '/../config.php';

function getConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }
            
            $conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            error_log($e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }
    
    return $conn;
}

function executeQuery($sql, $params = [], $types = '') {
    $conn = getConnection();
    
    try {
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $result = $stmt->execute();
        
        if ($result === false) {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
        
        return $stmt;
    } catch (Exception $e) {
        error_log($e->getMessage());
        throw $e;
    }
}

function fetchOne($sql, $params = [], $types = '') {
    try {
        $stmt = executeQuery($sql, $params, $types);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log($e->getMessage());
        return null;
    }
}

function fetchAll($sql, $params = [], $types = '') {
    try {
        $stmt = executeQuery($sql, $params, $types);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [];
    }
}

function getLastInsertId() {
    return getConnection()->insert_id;
}

function beginTransaction() {
    getConnection()->begin_transaction();
}

function commitTransaction() {
    getConnection()->commit();
}

function rollbackTransaction() {
    getConnection()->rollback();
}

function escapeString($str) {
    return getConnection()->real_escape_string($str);
}

function closeConnection() {
    $conn = getConnection();
    if ($conn) {
        $conn->close();
    }
} 