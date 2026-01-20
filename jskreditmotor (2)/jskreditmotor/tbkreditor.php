<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include 'koneksi.php';

$response = array();

// Debug log
error_log("tbkreditor.php accessed with operasi: " . ($_GET['operasi'] ?? 'view'));

$operasi = isset($_GET['operasi']) ? $_GET['operasi'] : 'view';

// Authorization check - tambahkan parameter level dan idkreditor untuk security
$user_level = isset($_GET['level']) ? $_GET['level'] : 'admin';
$idkreditor_user = isset($_GET['idkreditor']) ? intval($_GET['idkreditor']) : 0;

switch ($operasi) {
    case 'get_kreditor_by_id':
        if (!isset($_GET['idkreditor'])) {
            $response['success'] = false;
            $response['message'] = "Parameter 'idkreditor' diperlukan";
            echo json_encode($response);
            exit;
        }
        
        $idkreditor = mysqli_real_escape_string($konek, $_GET['idkreditor']);
        
        // Jika pelanggan, hanya bisa akses data sendiri
        if ($user_level == 'pelanggan' && $idkreditor != $idkreditor_user) {
            $response['success'] = false;
            $response['message'] = "Akses ditolak";
            echo json_encode($response);
            exit;
        }
        
        $sql = "SELECT * FROM kreditor WHERE idkreditor = '$idkreditor'";
        error_log("SQL Query: " . $sql);
        
        $result = mysqli_query($konek, $sql);
        
        if (!$result) {
            $response['success'] = false;
            $response['message'] = "Query error: " . mysqli_error($konek);
            echo json_encode($response);
            exit;
        }
        
        if (mysqli_num_rows($result) > 0) {
            $data = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            $response['success'] = true;
            $response['data'] = $data;
        } else {
            $response['success'] = false;
            $response['message'] = "Data kreditor tidak ditemukan";
        }
        
        echo json_encode($response);
        break;

    case 'select_by_idnama':
        // Untuk pelanggan, hanya tampilkan data sendiri
        if ($user_level == 'pelanggan') {
            $sql = "SELECT idkreditor, nama FROM kreditor WHERE idkreditor = $idkreditor_user";
        } else {
            // Untuk admin, tampilkan semua data
            $sql = "SELECT idkreditor, nama FROM kreditor ORDER BY nama";
        }
        
        $result = mysqli_query($konek, $sql);
        
        if (!$result) {
            $response['success'] = false;
            $response['message'] = "Query error: " . mysqli_error($konek);
            echo json_encode($response);
            exit;
        }
        
        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        $response['success'] = true;
        $response['data'] = $data;
        echo json_encode($response);
        break;

    case 'view':
        // Untuk pelanggan, hanya tampilkan data sendiri
        if ($user_level == 'pelanggan') {
            $sql = "SELECT * FROM kreditor WHERE idkreditor = $idkreditor_user";
        } else {
            // Untuk admin, tampilkan semua data
            $sql = "SELECT * FROM kreditor ORDER BY idkreditor DESC";
        }
        
        $result = mysqli_query($konek, $sql);
        
        if (!$result) {
            $response['success'] = false;
            $response['message'] = "Query error: " . mysqli_error($konek);
            echo json_encode($response);
            exit;
        }
        
        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        $response['success'] = true;
        $response['data'] = $data;
        echo json_encode($response);
        break;

    case 'insert':
        // Hanya admin yang bisa insert
        if ($user_level != 'admin') {
            $response['success'] = false;
            $response['message'] = "Akses ditolak - Hanya admin yang bisa menambah data";
            echo json_encode($response);
            exit;
        }
        
        $nama = isset($_GET['nama']) ? mysqli_real_escape_string($konek, $_GET['nama']) : '';
        $pekerjaan = isset($_GET['pekerjaan']) ? mysqli_real_escape_string($konek, $_GET['pekerjaan']) : '';
        $telp = isset($_GET['telp']) ? mysqli_real_escape_string($konek, $_GET['telp']) : '';
        $alamat = isset($_GET['alamat']) ? mysqli_real_escape_string($konek, $_GET['alamat']) : '';
        
        if (empty($nama) || empty($pekerjaan) || empty($telp) || empty($alamat)) {
            $response['success'] = false;
            $response['message'] = "Data tidak lengkap";
            echo json_encode($response);
            exit;
        }
        
        $sql = "INSERT INTO kreditor (nama, pekerjaan, telp, alamat) 
                VALUES ('$nama', '$pekerjaan', '$telp', '$alamat')";
        
        if (mysqli_query($konek, $sql)) {
            $response['success'] = true;
            $response['message'] = "Data kreditor berhasil ditambahkan";
            $response['idkreditor'] = mysqli_insert_id($konek);
        } else {
            $response['success'] = false;
            $response['message'] = "Error: " . mysqli_error($konek);
        }
        
        echo json_encode($response);
        break;

    case 'update':
        // Hanya admin yang bisa update
        if ($user_level != 'admin') {
            $response['success'] = false;
            $response['message'] = "Akses ditolak - Hanya admin yang bisa mengupdate data";
            echo json_encode($response);
            exit;
        }
        
        $idkreditor = isset($_GET['idkreditor']) ? intval($_GET['idkreditor']) : 0;
        $nama = isset($_GET['nama']) ? mysqli_real_escape_string($konek, $_GET['nama']) : '';
        $pekerjaan = isset($_GET['pekerjaan']) ? mysqli_real_escape_string($konek, $_GET['pekerjaan']) : '';
        $telp = isset($_GET['telp']) ? mysqli_real_escape_string($konek, $_GET['telp']) : '';
        $alamat = isset($_GET['alamat']) ? mysqli_real_escape_string($konek, $_GET['alamat']) : '';
        
        if ($idkreditor == 0 || empty($nama) || empty($pekerjaan) || empty($telp) || empty($alamat)) {
            $response['success'] = false;
            $response['message'] = "Data tidak lengkap";
            echo json_encode($response);
            exit;
        }
        
        $sql = "UPDATE kreditor SET nama='$nama', pekerjaan='$pekerjaan', telp='$telp', alamat='$alamat' 
                WHERE idkreditor=$idkreditor";
        
        if (mysqli_query($konek, $sql)) {
            $response['success'] = true;
            $response['message'] = "Data kreditor berhasil diupdate";
        } else {
            $response['success'] = false;
            $response['message'] = "Error: " . mysqli_error($konek);
        }
        
        echo json_encode($response);
        break;

    case 'update_profil':
        $idkreditor = isset($_GET['idkreditor']) ? mysqli_real_escape_string($konek, $_GET['idkreditor']) : '';
        $nama = isset($_GET['nama']) ? mysqli_real_escape_string($konek, $_GET['nama']) : '';
        $alamat = isset($_GET['alamat']) ? mysqli_real_escape_string($konek, $_GET['alamat']) : '';
        $telepon = isset($_GET['telepon']) ? mysqli_real_escape_string($konek, $_GET['telepon']) : '';
        $email = isset($_GET['email']) ? mysqli_real_escape_string($konek, $_GET['email']) : '';

        if (empty($idkreditor) || empty($nama) || empty($alamat) || empty($telepon)) {
            $response['success'] = false;
            $response['message'] = "Data tidak lengkap";
            echo json_encode($response);
            exit;
        }

        // Build update query
        $update_fields = "nama='$nama', alamat='$alamat', telp='$telepon'";
        if (!empty($email)) {
            $update_fields .= ", email='$email'";
        }

        $sql = "UPDATE kreditor SET $update_fields WHERE idkreditor='$idkreditor'";
        
        if (mysqli_query($konek, $sql)) {
            $response['success'] = true;
            $response['message'] = "Profil berhasil diupdate";
        } else {
            $response['success'] = false;
            $response['message'] = "Error: " . mysqli_error($konek);
        }
        
        echo json_encode($response);
        break;

    case 'update_password':
        $idkreditor = isset($_GET['idkreditor']) ? mysqli_real_escape_string($konek, $_GET['idkreditor']) : '';
        $password = isset($_GET['password']) ? mysqli_real_escape_string($konek, $_GET['password']) : '';

        if (empty($idkreditor) || empty($password)) {
            $response['success'] = false;
            $response['message'] = "Data tidak lengkap";
            echo json_encode($response);
            exit;
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE kreditor SET password='$hashed_password' WHERE idkreditor='$idkreditor'";
        
        if (mysqli_query($konek, $sql)) {
            $response['success'] = true;
            $response['message'] = "Password berhasil diupdate";
        } else {
            $response['success'] = false;
            $response['message'] = "Error: " . mysqli_error($konek);
        }
        
        echo json_encode($response);
        break;

    case 'delete':
        // Hanya admin yang bisa delete
        if ($user_level != 'admin') {
            $response['success'] = false;
            $response['message'] = "Akses ditolak - Hanya admin yang bisa menghapus data";
            echo json_encode($response);
            exit;
        }
        
        $idkreditor = isset($_GET['idkreditor']) ? intval($_GET['idkreditor']) : 0;
        
        if ($idkreditor == 0) {
            $response['success'] = false;
            $response['message'] = "ID kreditor tidak valid";
            echo json_encode($response);
            exit;
        }
        
        $sql = "DELETE FROM kreditor WHERE idkreditor = $idkreditor";
        
        if (mysqli_query($konek, $sql)) {
            $response['success'] = true;
            $response['message'] = "Data kreditor berhasil dihapus";
        } else {
            $response['success'] = false;
            $response['message'] = "Error: " . mysqli_error($konek);
        }
        
        echo json_encode($response);
        break;

    default:
        $response['success'] = false;
        $response['message'] = "Operasi tidak dikenali: " . $operasi;
        echo json_encode($response);
        break;
}

mysqli_close($konek);
?>