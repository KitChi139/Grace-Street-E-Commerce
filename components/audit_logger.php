<?php
include_once __DIR__ . '/connect.php';

function ensure_audit_table_exists() {
    global $con;
    $create_table = "CREATE TABLE IF NOT EXISTS audit_trail (
        auditID INT AUTO_INCREMENT PRIMARY KEY,
        action VARCHAR(255) NOT NULL,
        performed_by INT NOT NULL,
        target VARCHAR(255) DEFAULT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(50) DEFAULT 'Info',
        FOREIGN KEY (performed_by) REFERENCES grace_user(userID)
    )";
    mysqli_query($con, $create_table);
}

function log_audit($action, $performed_by, $target = null, $status = 'Info') {
    global $con;
    ensure_audit_table_exists();

    $action = mysqli_real_escape_string($con, $action);
    $target = $target ? "'" . mysqli_real_escape_string($con, $target) . "'" : "NULL";
    $status = mysqli_real_escape_string($con, $status);
    
    $query = "INSERT INTO audit_trail (action, performed_by, target, status) VALUES ('$action', '$performed_by', $target, '$status')";
    return mysqli_query($con, $query);
}
?>
