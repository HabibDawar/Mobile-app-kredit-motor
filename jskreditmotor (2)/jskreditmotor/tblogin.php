<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

// Include koneksi database
include 'koneksi.php';

$response = array();

// Check if connection is successful
if (!$konek) {
    $response['success'] = false;
    $response['message'] = "Database connection failed";
    echo json_encode($response);
    exit;
}

if (isset($_GET['operasi']) && $_GET['operasi'] == 'login') {
    // Get parameters
    $username = mysqli_real_escape_string($konek, $_GET['username']);
    $password = md5($_GET['password']); // MD5 hash
    
    // Debug log
    error_log("Login attempt: username=$username, password=" . $_GET['password']);
    
    $sql = "SELECT u.iduser, u.username, u.level, u.idkreditor, k.nama 
            FROM tbuser u 
            LEFT JOIN kreditor k ON u.idkreditor = k.idkreditor 
            WHERE u.username = '$username' AND u.password = '$password'";
    
    error_log("SQL Query: " . $sql);
    
    $result = mysqli_query($konek, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $response['success'] = true;
        $response['message'] = "Login berhasil";
        $response['data'] = array(
            'iduser' => $row['iduser'],
            'username' => $row['username'],
            'level' => $row['level'],
            'idkreditor' => $row['idkreditor'],
            'nama_kreditor' => $row['nama']
        );
    } else {
        $response['success'] = false;
        $response['message'] = "Username atau password salah";
        error_log("Login failed for user: $username");
    }
} else {
    $response['success'] = false;
    $response['message'] = "Operasi tidak valid";
}

echo json_encode($response);

// Close connection
if ($konek) {
    mysqli_close($konek);
}
?>