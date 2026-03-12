<?php

class User {
    private $conn;
    private $table = "users";

    public function __construct($db) 
    {
        $this->conn = $db;
    }

    // user in db maken
    public function create($username, $email, $password, $role = 'user') 
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (username, email, password, role) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$username, $email, $passwordHash, $role]);
    }

    // info ophalen bij id
    public function getById($id) 
    {
        $stmt = $this->conn->prepare("SELECT id, username, email, role, api_key FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // info ophaen bij username
    public function getByUsername($username) 
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // info ophalen bij email
    public function getByEmail($email) 
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    // password verifieren bij email of username
    public function verifyPassword($usernameOrEmail, $password) 
    {
        $user = filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL) ? $this->getByEmail($usernameOrEmail) : $this->getByUsername($usernameOrEmail);
        if ($user && isset($user['password']) && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        return null;
    }

    // password updaten
    public function updatePassword($id, $newPassword) 
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET password = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }

    // profile updaten (username, email)
    public function updateProfile($id, $username, $email) 
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET username = ?, email = ? WHERE id = ?");
        return $stmt->execute([$username, $email, $id]);
    }

    // user verwijderen
    public function delete($id) 
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // api key genereren
    public function generateApiKey($length = 40) 
    {
        return bin2hex(random_bytes((int)ceil($length / 2)));
    }

    // api key instellen
    public function setApiKey($id, $apiKey) 
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET api_key = ? WHERE id = ?");
        return $stmt->execute([$apiKey, $id]);
    }
}