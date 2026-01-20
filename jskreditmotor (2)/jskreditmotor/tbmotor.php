<?php
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

if (mysqli_connect_errno()) {
    echo json_encode(["error" => "Koneksi database gagal: " . mysqli_connect_error()]);
    exit();
}

mysqli_set_charset($koneksi, "utf8");

$operasi = isset($_GET['operasi']) ? $_GET['operasi'] : 'view';

// Authorization check
$user_level = isset($_GET['level']) ? $_GET['level'] : 'admin';

switch ($operasi) {
    case "view":
        viewMotor($koneksi);
        break;

    case "select_by_idnama":
        selectByIdNama($koneksi);
        break;

    case "insert":
        insertMotor($koneksi, $user_level);
        break;

    case "get_motor_by_kdmotor":
        getMotorByKdmotor($koneksi);
        break;

    case "select_by_kdmotorkredit":
        selectByKdmotorKredit($koneksi);
        break;

    case "update":
        updateMotor($koneksi, $user_level);
        break;

    case "delete":
        deleteMotor($koneksi, $user_level);
        break;

    default:
        viewMotor($koneksi);
        break;
}

function viewMotor($koneksi) {
    $query = mysqli_query($koneksi, "SELECT * FROM motor");
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

function selectByIdNama($koneksi) {
    $query = mysqli_query($koneksi, "SELECT kdmotor, nama, harga FROM motor ORDER BY kdmotor, nama");
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

function insertMotor($koneksi, $user_level) {
    // Hanya admin yang bisa insert
    if ($user_level != 'admin') {
        echo json_encode(["error" => "Akses ditolak - Hanya admin yang bisa menambah data motor"]);
        return;
    }
    
    $kdmotor = isset($_GET['kdmotor']) ? mysqli_real_escape_string($koneksi, $_GET['kdmotor']) : '';
    $nama = isset($_GET['nama']) ? mysqli_real_escape_string($koneksi, $_GET['nama']) : '';
    $harga = isset($_GET['harga']) ? intval($_GET['harga']) : 0;

    if (empty($kdmotor) || empty($nama) || $harga == 0) {
        echo json_encode(["error" => "Data tidak lengkap"]);
        return;
    }

    $query = mysqli_query($koneksi, "INSERT INTO motor (kdmotor, nama, harga) VALUES ('$kdmotor', '$nama', $harga)");

    if ($query) {
        echo json_encode(["success" => "Data Berhasil Disimpan", "id" => mysqli_insert_id($koneksi)]);
    } else {
        echo json_encode(["error" => "Error Insert motor: " . mysqli_error($koneksi)]);
    }
}

function getMotorByKdmotor($koneksi) {
    $idmotor = isset($_GET['idmotor']) ? intval($_GET['idmotor']) : 0;
    
    $query = mysqli_query($koneksi, "SELECT * FROM motor WHERE idmotor = $idmotor");
    if (!$query) {
        echo json_encode(["error" => "Query gagal: " . mysqli_error($koneksi)]);
        return;
    }

    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        echo json_encode([$data]);
    } else {
        echo json_encode(["error" => "Data tidak ditemukan"]);
    }
}

function selectByKdmotorKredit($koneksi) {
    $kdmotor = isset($_GET['kdmotor']) ? mysqli_real_escape_string($koneksi, $_GET['kdmotor']) : '';
    
    $query = mysqli_query($koneksi, "SELECT * FROM motor WHERE kdmotor = '$kdmotor'");
    if (!$query) {
        echo json_encode(["error" => "Query gagal: " . mysqli_error($koneksi)]);
        return;
    }

    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        echo json_encode([$data]);
    } else {
        echo json_encode(["error" => "Data tidak ditemukan"]);
    }
}

function updateMotor($koneksi, $user_level) {
    // Hanya admin yang bisa update
    if ($user_level != 'admin') {
        echo json_encode(["error" => "Akses ditolak - Hanya admin yang bisa mengupdate data motor"]);
        return;
    }
    
    $idmotor = isset($_GET['idmotor']) ? intval($_GET['idmotor']) : 0;
    $kdmotor = isset($_GET['kdmotor']) ? mysqli_real_escape_string($koneksi, $_GET['kdmotor']) : '';
    $nama = isset($_GET['nama']) ? mysqli_real_escape_string($koneksi, $_GET['nama']) : '';
    $harga = isset($_GET['harga']) ? intval($_GET['harga']) : 0;

    if ($idmotor == 0 || empty($kdmotor) || empty($nama) || $harga == 0) {
        echo json_encode(["error" => "Data tidak lengkap"]);
        return;
    }

    $query = mysqli_query($koneksi, "UPDATE motor SET kdmotor='$kdmotor', nama='$nama', harga=$harga WHERE idmotor=$idmotor");

    if ($query) {
        echo json_encode(["success" => "Update Data Berhasil"]);
    } else {
        echo json_encode(["error" => "Error Update: " . mysqli_error($koneksi)]);
    }
}

function deleteMotor($koneksi, $user_level) {
    // Hanya admin yang bisa delete
    if ($user_level != 'admin') {
        echo json_encode(["error" => "Akses ditolak - Hanya admin yang bisa menghapus data motor"]);
        return;
    }
    
    $idmotor = isset($_GET['idmotor']) ? intval($_GET['idmotor']) : 0;

    if ($idmotor == 0) {
        echo json_encode(["error" => "ID tidak valid"]);
        return;
    }

    $query = mysqli_query($koneksi, "DELETE FROM motor WHERE idmotor = $idmotor");

    if ($query) {
        echo json_encode(["success" => "Delete Data Berhasil"]);
    } else {
        echo json_encode(["error" => "Error Delete: " . mysqli_error($koneksi)]);
    }
}

mysqli_close($koneksi);
?>