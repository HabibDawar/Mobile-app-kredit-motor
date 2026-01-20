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
    echo json_encode(["error" => "Koneksi database gagal: " . mysqli_error($koneksi)]);
    exit();
}

mysqli_set_charset($koneksi, "utf8");

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : 'tampil_semua');

switch ($action) {
    case "tampil_semua":
        tampilSemuaAngsuran($koneksi);
        break;
        
    case "tampil_by_kreditor":
        $idkreditor = isset($_POST['idkreditor']) ? intval($_POST['idkreditor']) : (isset($_GET['idkreditor']) ? intval($_GET['idkreditor']) : 0);
        tampilAngsuranByKreditor($koneksi, $idkreditor);
        break;
        
    case "bayar":
        $invoice = isset($_POST['invoice']) ? mysqli_real_escape_string($koneksi, $_POST['invoice']) : (isset($_GET['invoice']) ? mysqli_real_escape_string($koneksi, $_GET['invoice']) : '');
        $username = isset($_POST['username']) ? mysqli_real_escape_string($koneksi, $_POST['username']) : (isset($_GET['username']) ? mysqli_real_escape_string($koneksi, $_GET['username']) : '');
        bayarAngsuran($koneksi, $invoice, $username);
        break;
        
    case "generate":
        $invoice = isset($_POST['invoice']) ? mysqli_real_escape_string($koneksi, $_POST['invoice']) : '';
        $total_kredit = isset($_POST['total_kredit']) ? floatval($_POST['total_kredit']) : 0;
        $lama_angsuran = isset($_POST['lama_angsuran']) ? intval($_POST['lama_angsuran']) : 0;
        $angsuran_per_bulan = isset($_POST['angsuran_per_bulan']) ? floatval($_POST['angsuran_per_bulan']) : 0;
        generateAngsuran($koneksi, $invoice, $total_kredit, $lama_angsuran, $angsuran_per_bulan);
        break;
        
    default:
        tampilSemuaAngsuran($koneksi);
        break;
}

function tampilSemuaAngsuran($koneksi) {
    $query = mysqli_query($koneksi, 
        "SELECT a.*, k.nama as nama_kreditor 
         FROM angsuran a 
         JOIN kredit kr ON a.invoice = kr.invoice 
         JOIN kreditor k ON kr.idkreditor = k.idkreditor 
         ORDER BY a.jatuh_tempo DESC");
    
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

function tampilAngsuranByKreditor($koneksi, $idkreditor) {
    if ($idkreditor == 0) {
        echo json_encode(["error" => "ID kreditor tidak valid"]);
        return;
    }

    $query = mysqli_query($koneksi, 
        "SELECT a.*, k.nama as nama_kreditor 
         FROM angsuran a 
         JOIN kredit kr ON a.invoice = kr.invoice 
         JOIN kreditor k ON kr.idkreditor = k.idkreditor 
         WHERE kr.idkreditor = $idkreditor 
         ORDER BY a.jatuh_tempo DESC");
    
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

function bayarAngsuran($koneksi, $invoice, $username) {
    if (empty($invoice)) {
        echo json_encode(["error" => "Invoice tidak valid"]);
        return;
    }

    $tanggal_bayar = date('Y-m-d');
    
    // Update angsuran yang belum dibayar
    $query = mysqli_query($koneksi, 
        "UPDATE angsuran 
         SET status = 'lunas', tanggal_bayar = '$tanggal_bayar' 
         WHERE invoice = '$invoice' AND status = 'belum bayar'
         ORDER BY angsuran_ke ASC 
         LIMIT 1");
    
    if ($query && mysqli_affected_rows($koneksi) > 0) {
        // Catat log pembayaran
        $log_query = "INSERT INTO tb_log (aktivitas, username, tanggal) 
                     VALUES ('Pembayaran angsuran invoice: $invoice', '$username', NOW())";
        mysqli_query($koneksi, $log_query);
        
        echo json_encode(["success" => 1, "message" => "Pembayaran angsuran berhasil"]);
    } else {
        echo json_encode(["success" => 0, "message" => "Pembayaran angsuran gagal atau sudah dibayar"]);
    }
}

function generateAngsuran($koneksi, $invoice, $total_kredit, $lama_angsuran, $angsuran_per_bulan) {
    if (empty($invoice) || $lama_angsuran == 0) {
        echo json_encode(["error" => "Data tidak lengkap untuk generate angsuran"]);
        return;
    }

    $tanggal_sekarang = date('Y-m-d');
    $success_count = 0;
    
    for($i = 1; $i <= $lama_angsuran; $i++) {
        $jatuh_tempo = date('Y-m-d', strtotime("+$i months", strtotime($tanggal_sekarang)));
        
        $query = "INSERT INTO angsuran (invoice, angsuran_ke, jatuh_tempo, jumlah, status) 
                  VALUES ('$invoice', $i, '$jatuh_tempo', $angsuran_per_bulan, 'belum bayar')";
        
        $result = mysqli_query($koneksi, $query);
        if ($result) {
            $success_count++;
        }
    }
    
    if ($success_count == $lama_angsuran) {
        echo json_encode(["success" => 1, "message" => "Berhasil generate $success_count angsuran"]);
    } else {
        echo json_encode(["success" => 0, "message" => "Hanya berhasil generate $success_count dari $lama_angsuran angsuran"]);
    }
}

mysqli_close($koneksi);
?>