<?php
require_once __DIR__ . '/../config.php';

function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        
        mysqli_set_charset($conn, "utf8mb4");
    }
    
    return $conn;
}

// Helper function to execute queries safely
function executeQuery($sql, $params = [], $types = '') {
    $conn = getDBConnection();
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt === false) {
        die("Error preparing statement: " . mysqli_error($conn));
    }
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        die("Error executing statement: " . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

// Helper function to get a single row
function fetchOne($sql, $params = [], $types = '') {
    $result = executeQuery($sql, $params, $types);
    return mysqli_fetch_assoc($result);
}

// Helper function to get multiple rows
function fetchAll($sql, $params = [], $types = '') {
    $result = executeQuery($sql, $params, $types);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Helper function to get last inserted ID
function getLastInsertId() {
    return mysqli_insert_id(getDBConnection());
}

// Helper function to escape strings
function escapeString($string) {
    return mysqli_real_escape_string(getDBConnection(), $string);
} 