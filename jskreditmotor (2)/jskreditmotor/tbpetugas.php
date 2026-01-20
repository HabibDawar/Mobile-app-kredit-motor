<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$server = "localhost";
$username = "root";
$password = "";
$database = "jskreditmotor";

// Koneksi ke database
$koneksi = mysqli_connect($server, $username, $password, $database);

// Cek koneksi
if (mysqli_connect_errno()) {
    echo json_encode(["error" => "Koneksi database gagal: " . mysqli_connect_error()]);
    exit();
}

// Set charset
mysqli_set_charset($koneksi, "utf8");

// Ambil operasi dari parameter
$operasi = isset($_GET['operasi']) ? $_GET['operasi'] : 'view';

// Log untuk debugging
error_log("Operasi requested: " . $operasi);

switch ($operasi) {
    case "view":
        viewPetugas($koneksi);
        break;

    case "insert":
        insertPetugas($koneksi);
        break;

    case "get_petugas_by_kdpetugas":
        getPetugasById($koneksi);
        break;

    case "get_petugas_by_username":
        getPetugasByUsername($koneksi);
        break;

    case "update":
        updatePetugas($koneksi);
        break;

    case "update_profil":
        updateProfilPetugas($koneksi);
        break;

    case "update_password":
        updatePasswordPetugas($koneksi);
        break;

    case "delete":
        deletePetugas($koneksi);
        break;

    default:
        // Default tampilkan semua data
        viewPetugas($koneksi);
        break;
}

function viewPetugas($koneksi) {
    $query = mysqli_query($koneksi, "SELECT * FROM tbpetugas");
    if (!$query) {
        echo json_encode(["error" => "Query gagal: " . mysqli_error($koneksi)]);
        return;
    }

    $data_array = array();
    while ($data = mysqli_fetch_assoc($query)) {
        $data_array[] = $data;
    }
    echo json_encode($data_array);
}

function insertPetugas($koneksi) {
    $kdpetugas = isset($_GET['kdpetugas']) ? mysqli_real_escape_string($koneksi, $_GET['kdpetugas']) : '';
    $nama = isset($_GET['nama']) ? mysqli_real_escape_string($koneksi, $_GET['nama']) : '';
    $jabatan = isset($_GET['jabatan']) ? mysqli_real_escape_string($koneksi, $_GET['jabatan']) : '';

    if (empty($kdpetugas) || empty($nama) || empty($jabatan)) {
        echo json_encode(["error" => "Data tidak lengkap"]);
        return;
    }

    $query = mysqli_query($koneksi, "INSERT INTO tbpetugas (kdpetugas, nama, jabatan) VALUES ('$kdpetugas', '$nama', '$jabatan')");

    if ($query) {
        echo json_encode(["success" => "Data Berhasil Disimpan", "id" => mysqli_insert_id($koneksi)]);
    } else {
        echo json_encode(["error" => "Gagal menyimpan: " . mysqli_error($koneksi)]);
    }
}

function getPetugasById($koneksi) {
    $idpetugas = isset($_GET['idpetugas']) ? intval($_GET['idpetugas']) : 0;
    
    $query = mysqli_query($koneksi, "SELECT * FROM tbpetugas WHERE idpetugas = $idpetugas");
    if (!$query) {
        echo json_encode(["error" => "Query gagal: " . mysqli_error($koneksi)]);
        return;
    }

    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        echo json_encode([$data]); // Return sebagai array
    } else {
        echo json_encode(["error" => "Data tidak ditemukan"]);
    }
}

function getPetugasByUsername($koneksi) {
    $username = isset($_GET['username']) ? mysqli_real_escape_string($koneksi, $_GET['username']) : '';
    
    $query = mysqli_query($koneksi, "SELECT * FROM tbpetugas WHERE username = '$username'");
    if (!$query) {
        echo json_encode(["error" => "Query gagal: " . mysqli_error($koneksi)]);
        return;
    }

    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        echo json_encode([$data]); // Return sebagai array
    } else {
        echo json_encode(["error" => "Data tidak ditemukan"]);
    }
}

function updatePetugas($koneksi) {
    $idpetugas = isset($_GET['idpetugas']) ? intval($_GET['idpetugas']) : 0;
    $kdpetugas = isset($_GET['kdpetugas']) ? mysqli_real_escape_string($koneksi, $_GET['kdpetugas']) : '';
    $nama = isset($_GET['nama']) ? mysqli_real_escape_string($koneksi, $_GET['nama']) : '';
    $jabatan = isset($_GET['jabatan']) ? mysqli_real_escape_string($koneksi, $_GET['jabatan']) : '';

    if ($idpetugas == 0 || empty($kdpetugas) || empty($nama) || empty($jabatan)) {
        echo json_encode(["error" => "Data tidak lengkap"]);
        return;
    }

    $query = mysqli_query($koneksi, "UPDATE tbpetugas SET kdpetugas='$kdpetugas', nama='$nama', jabatan='$jabatan' WHERE idpetugas=$idpetugas");

    if ($query) {
        echo json_encode(["success" => "Data berhasil diupdate"]);
    } else {
        echo json_encode(["error" => "Gagal update: " . mysqli_error($koneksi)]);
    }
}

function updateProfilPetugas($koneksi) {
    $username = isset($_GET['username']) ? mysqli_real_escape_string($koneksi, $_GET['username']) : '';
    $nama = isset($_GET['nama']) ? mysqli_real_escape_string($koneksi, $_GET['nama']) : '';
    $email = isset($_GET['email']) ? mysqli_real_escape_string($koneksi, $_GET['email']) : '';

    if (empty($username) || empty($nama)) {
        echo json_encode(["error" => "Data tidak lengkap"]);
        return;
    }

    // Build update query
    $update_fields = "nama='$nama'";
    if (!empty($email)) {
        $update_fields .= ", email='$email'";
    }

    $query = mysqli_query($koneksi, "UPDATE tbpetugas SET $update_fields WHERE username='$username'");

    if ($query) {
        echo json_encode(["success" => "Profil berhasil diupdate"]);
    } else {
        echo json_encode(["error" => "Gagal update: " . mysqli_error($koneksi)]);
    }
}

function updatePasswordPetugas($koneksi) {
    $username = isset($_GET['username']) ? mysqli_real_escape_string($koneksi, $_GET['username']) : '';
    $password = isset($_GET['password']) ? mysqli_real_escape_string($koneksi, $_GET['password']) : '';

    if (empty($username) || empty($password)) {
        echo json_encode(["error" => "Data tidak lengkap"]);
        return;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $query = mysqli_query($koneksi, "UPDATE tbpetugas SET password='$hashed_password' WHERE username='$username'");

    if ($query) {
        echo json_encode(["success" => "Password berhasil diupdate"]);
    } else {
        echo json_encode(["error" => "Gagal update password: " . mysqli_error($koneksi)]);
    }
}

function deletePetugas($koneksi) {
    $idpetugas = isset($_GET['idpetugas']) ? intval($_GET['idpetugas']) : 0;

    if ($idpetugas == 0) {
        echo json_encode(["error" => "ID tidak valid"]);
        return;
    }

    $query = mysqli_query($koneksi, "DELETE FROM tbpetugas WHERE idpetugas = $idpetugas");

    if ($query) {
        echo json_encode(["success" => "Data berhasil dihapus"]);
    } else {
        echo json_encode(["error" => "Gagal menghapus: " . mysqli_error($koneksi)]);
    }
}

// Tutup koneksi
mysqli_close($koneksi);
?>