<?php
$host = "";
$user = "exadmin";
$pass = '';
$db   = "lbms";
$port = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error setting charset: " . $conn->error);
    }
    
    if (!$conn->ping()) {
        throw new Exception("Connection lost after charset setting");
    }
    
} catch (mysqli_sql_exception $e) {
    // Get detailed MySQL error
    $error_details = [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'sql_state' => $e->getCode(),
        'mysql_error' => $conn->error ?? 'No connection'
    ];
    
    error_log(json_encode($error_details));
    
    die("<pre>Exact Database Error:\n" . print_r($error_details, true) . "</pre>");
}
?>