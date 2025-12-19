<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $employee_id;
    public $username;
    public $password;
    public $role_id;
    public $is_active;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new user
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                (employee_id, username, password, role_id, is_active) 
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        // Sanitize and hash password
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        
        $stmt->bind_param("issii", 
            $this->employee_id, 
            $this->username, 
            $this->password, 
            $this->role_id, 
            $this->is_active
        );

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Login
    public function login($username, $password) {
        $query = "SELECT id, employee_id, username, password, role_id, is_active 
                  FROM " . $this->table_name . " 
                  WHERE username = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if(password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->employee_id = $row['employee_id'];
                $this->username = $row['username'];
                $this->role_id = $row['role_id'];
                $this->is_active = $row['is_active'];
                return true;
            }
        }
        return false;
    }
    
    // Check if username exists
    public function usernameExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->username);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
}
?>
