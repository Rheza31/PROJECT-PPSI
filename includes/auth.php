<?php
require_once 'config.php';

// Fungsi registrasi
function register_user($username, $password, $nama_lengkap, $role = 'admin') {
    global $db;
    
    // Cek username sudah ada
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        return ['status' => 'error', 'message' => 'Username sudah terdaftar'];
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user baru
    $stmt = $db->prepare("INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $hashed_password, $nama_lengkap, $role]);
    
    return ['status' => 'success', 'message' => 'Registrasi berhasil'];
}

// Fungsi login
function login_user($username, $password) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    
    return false;
}
?>