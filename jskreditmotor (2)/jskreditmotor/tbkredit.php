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
$idkreditor_user = isset($_GET['idkreditor']) ? intval($_GET['idkreditor']) : 0;

switch ($operasi) {
    case "view":
        viewKredit($koneksi, $user_level, $idkreditor_user);
        break;

    case "view_by_kreditor":
        viewKreditByKreditor($koneksi);
        break;

    case "query_kredit":
        queryKredit($koneksi, $user_level, $idkreditor_user);
        break;

    case "select_by_idnama":
        selectByIdNama($koneksi);
        break;

    case "simpan_kredit":
        simpanKredit($koneksi, $user_level, $idkreditor_user);
        break;

    case "hapus_kredit":
        hapusKredit($koneksi, $user_level, $idkreditor_user);
        break;

    case "approve_kredit":
        approveKredit($koneksi, $user_level);
        break;

    case "reject_kredit":
        rejectKredit($koneksi, $user_level);
        break;

    case "get_by_invoice":
        getKreditByInvoice($koneksi);
        break;

    case "update_kredit":
        updateKredit($koneksi, $user_level, $idkreditor_user);
        break;

    case "laporan_kredit":
        laporanKredit($koneksi);
        break;

    default:
        viewKredit($koneksi, $user_level, $idkreditor_user);
        break;
}

function viewKredit($koneksi, $user_level, $idkreditor_user) {
    // Untuk pelanggan, hanya tampilkan data sendiri
    if ($user_level == 'pelanggan') {
        $query = mysqli_query($koneksi, 
            "SELECT k.invoice, k.tanggal, k.idkreditor, kr.nama, kr.alamat,
                    k.kdmotor, m.nama as nmotor, k.hrgtunai, k.dp, 
                    k.hrgkredit, k.bunga, k.lama, k.totalkredit, 
                    k.angsuran, k.status, k.approved_by, k.approved_at
             FROM kredit k
             JOIN kreditor kr ON k.idkreditor = kr.idkreditor
             JOIN motor m ON k.kdmotor = m.kdmotor
             WHERE k.idkreditor = $idkreditor_user
             ORDER BY k.invoice DESC");
    } else {
        // Untuk admin, tampilkan semua data
        $query = mysqli_query($koneksi, 
            "SELECT k.invoice, k.tanggal, k.idkreditor, kr.nama, kr.alamat,
                    k.kdmotor, m.nama as nmotor, k.hrgtunai, k.dp, 
                    k.hrgkredit, k.bunga, k.lama, k.totalkredit, 
                    k.angsuran, k.status, k.approved_by, k.approved_at
             FROM kredit k
             JOIN kreditor kr ON k.idkreditor = kr.idkreditor
             JOIN motor m ON k.kdmotor = m.kdmotor
             ORDER BY k.invoice DESC");
    }
    
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

function viewKreditByKreditor($koneksi) {
    $idkreditor = isset($_GET['idkreditor']) ? intval($_GET['idkreditor']) : 0;
    
    if ($idkreditor == 0) {
        echo json_encode(["error" => "ID kreditor tidak valid"]);
        return;
    }

    $query = mysqli_query($koneksi, 
        "SELECT k.invoice, k.tanggal, k.idkreditor, kr.nama, kr.alamat,
                k.kdmotor, m.nama as nmotor, k.hrgtunai, k.dp, 
                k.hrgkredit, k.bunga, k.lama, k.totalkredit, 
                k.angsuran, k.status, k.approved_by, k.approved_at
         FROM kredit k
         JOIN kreditor kr ON k.idkreditor = kr.idkreditor
         JOIN motor m ON k.kdmotor = m.kdmotor
         WHERE k.idkreditor = $idkreditor
         ORDER BY k.invoice DESC");
    
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

function queryKredit($koneksi, $user_level, $idkreditor_user) {
    // Untuk pelanggan, hanya tampilkan data sendiri
    if ($user_level == 'pelanggan') {
        $query = mysqli_query($koneksi, 
            "SELECT k.invoice, k.tanggal, k.idkreditor, kr.nama, kr.alamat,
                    k.kdmotor, m.nama as nmotor, k.hrgtunai, k.dp, 
                    k.hrgkredit, k.bunga, k.lama, k.totalkredit, 
                    k.angsuran, k.status
             FROM motor m
             INNER JOIN (kreditor kr INNER JOIN kredit k ON kr.idkreditor = k.idkreditor) 
             ON m.kdmotor = k.kdmotor
             WHERE k.idkreditor = $idkreditor_user
             ORDER BY k.invoice DESC");
    } else {
        // Untuk admin, tampilkan semua data
        $query = mysqli_query($koneksi, 
            "SELECT k.invoice, k.tanggal, k.idkreditor, kr.nama, kr.alamat,
                    k.kdmotor, m.nama as nmotor, k.hrgtunai, k.dp, 
                    k.hrgkredit, k.bunga, k.lama, k.totalkredit, 
                    k.angsuran, k.status
             FROM motor m
             INNER JOIN (kreditor kr INNER JOIN kredit k ON kr.idkreditor = k.idkreditor) 
             ON m.kdmotor = k.kdmotor
             ORDER BY k.invoice DESC");
    }
    
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
    $query = mysqli_query($koneksi, "SELECT idkreditor, nama FROM kreditor ORDER BY nama");
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

function simpanKredit($koneksi, $user_level, $idkreditor_user) {
    $idkreditor = isset($_POST['idkreditor']) ? intval($_POST['idkreditor']) : 
                  (isset($_GET['idkreditor']) ? intval($_GET['idkreditor']) : 0);
    
    // Untuk pelanggan, validasi bahwa mereka hanya bisa input untuk diri sendiri
    if ($user_level == 'pelanggan') {
        if ($idkreditor != $idkreditor_user) {
            echo json_encode(["error" => "Akses ditolak - Anda hanya bisa mengajukan kredit untuk diri sendiri"]);
            return;
        }
    }
    
    $kdmotor = isset($_POST['kdmotor']) ? mysqli_real_escape_string($koneksi, $_POST['kdmotor']) : 
               (isset($_GET['kdmotor']) ? mysqli_real_escape_string($koneksi, $_GET['kdmotor']) : '');
    
    $hrgtunai = isset($_POST['hrgtunai']) ? doubleval($_POST['hrgtunai']) : 
                (isset($_GET['hrgtunai']) ? doubleval($_GET['hrgtunai']) : 0);
    
    $dp = isset($_POST['dp']) ? doubleval($_POST['dp']) : 
          (isset($_GET['dp']) ? doubleval($_GET['dp']) : 0);
    
    $hrgkredit = isset($_POST['hrgkredit']) ? doubleval($_POST['hrgkredit']) : 
                 (isset($_GET['hrgkredit']) ? doubleval($_GET['hrgkredit']) : 0);
    
    $bunga = isset($_POST['bunga']) ? doubleval($_POST['bunga']) : 
             (isset($_GET['bunga']) ? doubleval($_GET['bunga']) : 0);
    
    $lama = isset($_POST['lama']) ? intval($_POST['lama']) : 
            (isset($_GET['lama']) ? intval($_GET['lama']) : 0);
    
    $totalkredit = isset($_POST['totalkredit']) ? doubleval($_POST['totalkredit']) : 
                   (isset($_GET['totalkredit']) ? doubleval($_GET['totalkredit']) : 0);
    
    $angsuran = isset($_POST['angsuran']) ? doubleval($_POST['angsuran']) : 
                (isset($_GET['angsuran']) ? doubleval($_GET['angsuran']) : 0);

    // Validasi input
    if ($idkreditor == 0 || empty($kdmotor) || $hrgtunai == 0 || $lama == 0) {
        echo json_encode(["error" => "Data tidak lengkap. Pastikan semua field terisi dengan benar."]);
        return;
    }

    if ($dp >= $hrgtunai) {
        echo json_encode(["error" => "Uang muka tidak boleh lebih dari atau sama dengan harga motor"]);
        return;
    }

    // Generate invoice number
    $invoice_prefix = "INV";
    $invoice_number = $invoice_prefix . date('YmdHis') . rand(100, 999);

    // Mulai transaction
    mysqli_begin_transaction($koneksi);

    try {
        // Simpan data kredit
        $query = mysqli_query($koneksi, 
            "INSERT INTO kredit (invoice, idkreditor, kdmotor, hrgtunai, dp, hrgkredit, bunga, lama, totalkredit, angsuran, status, tanggal) 
             VALUES ('$invoice_number', $idkreditor, '$kdmotor', $hrgtunai, $dp, $hrgkredit, $bunga, $lama, $totalkredit, $angsuran, 'pending', NOW())");

        if (!$query) {
            throw new Exception("Error Insert kredit: " . mysqli_error($koneksi));
        }

        // Commit transaction
        mysqli_commit($koneksi);
        
        echo json_encode([
            "success" => "Data Pengajuan Kredit Berhasil Disimpan", 
            "invoice" => $invoice_number,
            "message" => "Pengajuan kredit Anda telah berhasil disimpan dan menunggu approval admin."
        ]);

    } catch (Exception $e) {
        // Rollback transaction jika ada error
        mysqli_rollback($koneksi);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function hapusKredit($koneksi, $user_level, $idkreditor_user) {
    $invoice = isset($_GET['invoice']) ? mysqli_real_escape_string($koneksi, $_GET['invoice']) : '';

    if (empty($invoice)) {
        echo json_encode(["error" => "Invoice tidak valid"]);
        return;
    }

    // Untuk pelanggan, cek apakah kredit miliknya
    if ($user_level == 'pelanggan') {
        $check_query = mysqli_query($koneksi, "SELECT idkreditor FROM kredit WHERE invoice = '$invoice'");
        if ($check_query && mysqli_num_rows($check_query) > 0) {
            $kredit_data = mysqli_fetch_assoc($check_query);
            if ($kredit_data['idkreditor'] != $idkreditor_user) {
                echo json_encode(["error" => "Akses ditolak - Anda hanya bisa menghapus kredit milik sendiri"]);
                return;
            }
        }
    }

    // Mulai transaction
    mysqli_begin_transaction($koneksi);

    try {
        // Hapus angsuran terlebih dahulu (jika ada)
        $query_angsuran = mysqli_query($koneksi, "DELETE FROM angsuran WHERE invoice = '$invoice'");
        if (!$query_angsuran) {
            throw new Exception("Error Delete angsuran: " . mysqli_error($koneksi));
        }

        // Hapus kredit
        $query = mysqli_query($koneksi, "DELETE FROM kredit WHERE invoice = '$invoice'");
        if (!$query) {
            throw new Exception("Error Delete kredit: " . mysqli_error($koneksi));
        }

        // Commit transaction
        mysqli_commit($koneksi);
        
        echo json_encode(["success" => "Data Pengajuan Kredit Berhasil Dihapus"]);

    } catch (Exception $e) {
        // Rollback transaction jika ada error
        mysqli_rollback($koneksi);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function approveKredit($koneksi, $user_level) {
    // Hanya admin yang bisa approve
    if ($user_level != 'admin') {
        echo json_encode(["error" => "Akses ditolak - Hanya admin yang bisa approve kredit"]);
        return;
    }
    
    $invoice = isset($_GET['invoice']) ? mysqli_real_escape_string($koneksi, $_GET['invoice']) : '';
    $approved_by = isset($_GET['approved_by']) ? mysqli_real_escape_string($koneksi, $_GET['approved_by']) : '';

    if (empty($invoice) || empty($approved_by)) {
        echo json_encode(["error" => "Data tidak lengkap"]);
        return;
    }

    // Mulai transaction
    mysqli_begin_transaction($koneksi);

    try {
        // 1. Update status kredit
        $query = mysqli_query($koneksi, 
            "UPDATE kredit SET status='approved', approved_by='$approved_by', approved_at=NOW() 
             WHERE invoice='$invoice'");

        if (!$query) {
            throw new Exception("Error approve kredit: " . mysqli_error($koneksi));
        }

        // 2. Ambil data kredit untuk generate angsuran
        $query_kredit = mysqli_query($koneksi, 
            "SELECT totalkredit, lama, angsuran FROM kredit WHERE invoice = '$invoice'");
        
        if (!$query_kredit || mysqli_num_rows($query_kredit) == 0) {
            throw new Exception("Data kredit tidak ditemukan");
        }

        $data_kredit = mysqli_fetch_assoc($query_kredit);
        $total_kredit = $data_kredit['totalkredit'];
        $lama_angsuran = $data_kredit['lama'];
        $angsuran_per_bulan = $data_kredit['angsuran'];

        // 3. Generate angsuran
        $tanggal_sekarang = date('Y-m-d');
        
        for($i = 1; $i <= $lama_angsuran; $i++) {
            $jatuh_tempo = date('Y-m-d', strtotime("+$i months", strtotime($tanggal_sekarang)));
            
            $query_angsuran = "INSERT INTO angsuran (invoice, angsuran_ke, jatuh_tempo, jumlah, status) 
                              VALUES ('$invoice', $i, '$jatuh_tempo', $angsuran_per_bulan, 'belum bayar')";
            
            $result_angsuran = mysqli_query($koneksi, $query_angsuran);
            if (!$result_angsuran) {
                throw new Exception("Error generate angsuran: " . mysqli_error($koneksi));
            }
        }

        // 4. Commit transaction
        mysqli_commit($koneksi);
        
        echo json_encode(["success" => "Kredit berhasil diapprove dan angsuran digenerate"]);

    } catch (Exception $e) {
        // Rollback transaction jika ada error
        mysqli_rollback($koneksi);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function rejectKredit($koneksi, $user_level) {
    // Hanya admin yang bisa reject
    if ($user_level != 'admin') {
        echo json_encode(["error" => "Akses ditolak - Hanya admin yang bisa reject kredit"]);
        return;
    }
    
    $invoice = isset($_GET['invoice']) ? mysqli_real_escape_string($koneksi, $_GET['invoice']) : '';
    $approved_by = isset($_GET['approved_by']) ? mysqli_real_escape_string($koneksi, $_GET['approved_by']) : '';

    if (empty($invoice) || empty($approved_by)) {
        echo json_encode(["error" => "Data tidak lengkap"]);
        return;
    }

    $query = mysqli_query($koneksi, 
        "UPDATE kredit SET status='rejected', approved_by='$approved_by', approved_at=NOW() 
         WHERE invoice='$invoice'");

    if ($query) {
        echo json_encode(["success" => "Kredit berhasil direject"]);
    } else {
        echo json_encode(["error" => "Error reject kredit: " . mysqli_error($koneksi)]);
    }
}

function getKreditByInvoice($koneksi) {
    $invoice = isset($_GET['invoice']) ? mysqli_real_escape_string($koneksi, $_GET['invoice']) : '';

    if (empty($invoice)) {
        echo json_encode(["error" => "Invoice tidak valid"]);
        return;
    }

    $query = mysqli_query($koneksi, 
        "SELECT k.*, kr.nama as nama_kreditor, kr.alamat, m.nama as nama_motor
         FROM kredit k
         JOIN kreditor kr ON k.idkreditor = kr.idkreditor
         JOIN motor m ON k.kdmotor = m.kdmotor
         WHERE k.invoice = '$invoice'");

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

function updateKredit($koneksi, $user_level, $idkreditor_user) {
    $invoice = isset($_GET['invoice']) ? mysqli_real_escape_string($koneksi, $_GET['invoice']) : '';
    $idkreditor = isset($_GET['idkreditor']) ? intval($_GET['idkreditor']) : 0;
    $kdmotor = isset($_GET['kdmotor']) ? mysqli_real_escape_string($koneksi, $_GET['kdmotor']) : '';
    $hrgtunai = isset($_GET['hrgtunai']) ? doubleval($_GET['hrgtunai']) : 0;
    $dp = isset($_GET['dp']) ? doubleval($_GET['dp']) : 0;
    $hrgkredit = isset($_GET['hrgkredit']) ? doubleval($_GET['hrgkredit']) : 0;
    $bunga = isset($_GET['bunga']) ? doubleval($_GET['bunga']) : 0;
    $lama = isset($_GET['lama']) ? intval($_GET['lama']) : 0;
    $totalkredit = isset($_GET['totalkredit']) ? doubleval($_GET['totalkredit']) : 0;
    $angsuran = isset($_GET['angsuran']) ? doubleval($_GET['angsuran']) : 0;

    if (empty($invoice) || $idkreditor == 0 || empty($kdmotor) || $hrgtunai == 0) {
        echo json_encode(["error" => "Data tidak lengkap"]);
        return;
    }

    // Untuk pelanggan, cek apakah kredit miliknya
    if ($user_level == 'pelanggan') {
        $check_query = mysqli_query($koneksi, "SELECT idkreditor FROM kredit WHERE invoice = '$invoice'");
        if ($check_query && mysqli_num_rows($check_query) > 0) {
            $kredit_data = mysqli_fetch_assoc($check_query);
            if ($kredit_data['idkreditor'] != $idkreditor_user) {
                echo json_encode(["error" => "Akses ditolak - Anda hanya bisa mengupdate kredit milik sendiri"]);
                return;
            }
        }
    }

    $query = mysqli_query($koneksi, 
        "UPDATE kredit SET idkreditor=$idkreditor, kdmotor='$kdmotor', hrgtunai=$hrgtunai, 
                          dp=$dp, hrgkredit=$hrgkredit, bunga=$bunga, lama=$lama, 
                          totalkredit=$totalkredit, angsuran=$angsuran, status='pending'
         WHERE invoice='$invoice'");

    if ($query) {
        echo json_encode(["success" => "Data kredit berhasil diupdate"]);
    } else {
        echo json_encode(["error" => "Error update kredit: " . mysqli_error($koneksi)]);
    }
}

function laporanKredit($koneksi) {
    $bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : 0;
    $tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : 0;

    $where_clause = "";
    if ($bulan > 0 && $tahun > 0) {
        $where_clause = "WHERE MONTH(k.tanggal) = $bulan AND YEAR(k.tanggal) = $tahun";
    } elseif ($tahun > 0) {
        $where_clause = "WHERE YEAR(k.tanggal) = $tahun";
    }

    $query = mysqli_query($koneksi, 
        "SELECT k.invoice, k.tanggal, kr.nama as nama_kreditor, m.nama as nama_motor,
                k.hrgtunai, k.dp, k.totalkredit, k.angsuran, k.status,
                k.approved_by, k.approved_at
         FROM kredit k
         JOIN kreditor kr ON k.idkreditor = kr.idkreditor
         JOIN motor m ON k.kdmotor = m.kdmotor
         $where_clause
         ORDER BY k.tanggal DESC");

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

mysqli_close($koneksi);
?>