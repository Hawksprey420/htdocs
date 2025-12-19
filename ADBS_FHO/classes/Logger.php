<?php
class Logger {
    private $conn;
    private $table_name = "activity_logs";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function log($user_id, $action, $description = "") {
        $query = "INSERT INTO " . $this->table_name . " 
                (user_id, action, description, ip_address) 
                VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $stmt->bind_param("isss", $user_id, $action, $description, $ip);

        return $stmt->execute();
    }
}
?>
