<?php
// Menghubungkan ke database MySQL melalui file koneksi.
require_once __DIR__ . "/koneksi.php";

// Fungsi untuk mengamankan output HTML 
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}


 //Menentukan Nilai Huruf dan Predikat kelulusan berdasarkan angka Nilai Akhir.

function konversiNilai($nilai) {
    if ($nilai >= 80.51) {
        return ["A", "Sangat Memuaskan"];
    } elseif ($nilai >= 65.51) {
        return ["B", "Memuaskan"];
    } elseif ($nilai >= 50.51) {
        return ["C", "Cukup Memuaskan"];
    } elseif ($nilai >= 34.51) {
        return ["D", "Kurang Memuaskan"];
    }
    return ["E", "Tidak Lulus"];
}

// Fungsi untuk mengarahkan pengguna ke halaman tertentu dengan parameter opsional.
function redirectKe($halaman = "data", $parameter = "") {
    $url = "adminlte.php?page=" . urlencode($halaman);
    if ($parameter !== "") {
        $url .= "&" . $parameter;
    }
    header("Location: " . $url);
    exit;
}

// Fungsi untuk menyusun URL dengan parameter GET baru, mempertahankan parameter yang ada.
function buatUrl($paramsBaru = []) {
    $params = $_GET;
    $params['page'] = 'data'; // Default ke halaman data
    foreach ($paramsBaru as $key => $val) {
        $params[$key] = $val;
    }
    return "adminlte.php?" . http_build_query($params);
}

// Menyusun URL khusus untuk pengurutan (sorting) kolom header tabel (toggle ASC/DESC).
function buatUrlSort($kolomTarget, $sortAktif, $orderAktif) {
    // Balik urutan jika kolom yang sama diklik kembali
    $orderBaru = ($sortAktif === $kolomTarget && $orderAktif === 'asc') ? 'desc' : 'asc';
    return buatUrl(['sort' => $kolomTarget, 'order' => $orderBaru, 'p' => 1]);
}

// Inisialisasi variabel navigasi dan pesan status
$page = $_GET["page"] ?? "data";
$pesan = "";
$error = "";


// 1. TANGKAP DAN VALIDASI PARAMETER URL (GET)

// Parameter Pencarian Nama & Filter Predikat
$searchKeyword  = trim($_GET["search"] ?? "");
$filterPredikat = trim($_GET["predikat"] ?? "all");

// Validasi Parameter Sorting (Default: reg_date DESC)
$sort  = $_GET["sort"] ?? "reg_date";
$order = strtolower($_GET["order"] ?? "desc");
$allowedSort = ["nim", "nama_mhsw", "nilai_akhir", "reg_date"];
if (!in_array($sort, $allowedSort)) { $sort = "reg_date"; }
if ($order !== "asc" && $order !== "desc") { $order = "desc"; }

// Validasi Parameter Limit Data per Halaman (Kelipatan 5: 5, 10, 15)
$limit = (int)($_GET["limit"] ?? 5);
$allowedLimit = [5, 10, 15];
if (!in_array($limit, $allowedLimit)) { $limit = 5; }

// Validasi Nomor Halaman Aktif (Pagination)
$halamanAktif = (int)($_GET["p"] ?? 1);
if ($halamanAktif < 1) { $halamanAktif = 1; 
// 2. PROSES FORM (POST REQUEST: TAMBAH, EDIT, HAPUS DATA
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $aksi = $_POST["aksi"] ?? "";

    // A. Tambah Data Baru atau Edit Data Lama
    if ($aksi === "tambah" || $aksi === "edit") {
        $nim = trim($_POST["nim"] ?? "");
        $namalengkap = trim($_POST["nama_mhsw"] ?? "");
        $nilaiInput = $_POST["nilai_akhir"] ?? "";

        // Validasi input server-side
        if ($nim === "" || strlen($nim) > 10 || $namalengkap === "" ||
            !is_numeric($nilaiInput) || $nilaiInput < 0 || $nilaiInput > 100) {
            $error = "Data tidak valid. Periksa NIM, nama, dan nilai (0–100).";
            $page = ($aksi === "edit") ? "edit" : "tambah";
        } else {
            $nilaiakhir = (float)$nilaiInput;
            [$nilaihuruf, $predikat] = konversiNilai($nilaiakhir);

            // Logika Upload Foto Mahasiswa ke Folder uploads/
            $namaFotoBaru = null;
            if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
                $ekstensiDiizinkan = ["jpg", "jpeg", "png", "gif", "webp"];

                if (in_array($ext, $ekstensiDiizinkan)) {
                    // Membuat direktori uploads jika belum tersedia
                    $folderUpload = __DIR__ . "/uploads";
                    if (!is_dir($folderUpload)) {
                        mkdir($folderUpload, 0755, true);
                    }

                    // Format nama foto unik: NIM_timestamp.ekstensi
                    $namaFotoBaru = $nim . "_" . time() . "." . $ext;
                    $tujuanUpload = $folderUpload . "/" . $namaFotoBaru;
                    move_uploaded_file($_FILES["foto"]["tmp_name"], $tujuanUpload);
                }
            }

            // Query Simpan Data Baru (INSERT)
            if ($aksi === "tambah") {
                $sql = "INSERT INTO nilai_mhsw
                        (nim, nama_mhsw, nilai_akhir, nilai_huruf, predikat, foto)
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param(
                    $stmt, "ssdsss",
                    $nim, $namalengkap, $nilaiakhir, $nilaihuruf, $predikat, $namaFotoBaru
                );
            } else {
                // Query Perbarui Data (UPDATE) - dengan ganti foto
                if ($namaFotoBaru !== null) {
                    $sql = "UPDATE nilai_mhsw
                            SET nama_mhsw = ?, nilai_akhir = ?, nilai_huruf = ?, predikat = ?, foto = ?
                            WHERE nim = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param(
                        $stmt, "sdssss",
                        $namalengkap, $nilaiakhir, $nilaihuruf, $predikat, $namaFotoBaru, $nim
                    );
                } else {
                    // Query Perbarui Data (UPDATE) - tanpa ganti foto
                    $sql = "UPDATE nilai_mhsw
                            SET nama_mhsw = ?, nilai_akhir = ?, nilai_huruf = ?, predikat = ?
                            WHERE nim = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param(
                        $stmt, "sdsss",
                        $namalengkap, $nilaiakhir, $nilaihuruf, $predikat, $nim
                    );
                }
            }

            // Eksekusi Prepared Statement
            if ($stmt && mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                redirectKe("data");
            } else {
                $error = "Data gagal disimpan. " . mysqli_error($conn);
                if ($stmt) {
                    mysqli_stmt_close($stmt);
                }
                $page = ($aksi === "edit") ? "edit" : "tambah";
            }
        }
    } 
    // B. Hapus Data Mahasiswa
    elseif ($aksi === "hapus") {
        $nim = trim($_POST["nim"] ?? "");
        if ($nim !== "") {
            $sql = "DELETE FROM nilai_mhsw WHERE nim = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $nim);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                redirectKe("data");
            }
            $error = "Data gagal dihapus.";
            mysqli_stmt_close($stmt);
        } else {
            $error = "NIM tidak valid.";
        }
        $page = "data";
    }
}

// 3. AMBIL DATA UNTUK FORM EDIT
$dataEdit = null;
if ($page === "edit" && $_SERVER["REQUEST_METHOD"] !== "POST") {
    $nimEdit = trim($_GET["nim"] ?? "");
    if ($nimEdit !== "") {
        $sql = "SELECT nim, nama_mhsw, nilai_akhir, foto FROM nilai_mhsw WHERE nim = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $nimEdit);
        mysqli_stmt_execute($stmt);
        $resultEdit = mysqli_stmt_get_result($stmt);
        $dataEdit = mysqli_fetch_assoc($resultEdit);
        mysqli_stmt_close($stmt);
        if (!$dataEdit) {
            $error = "Data mahasiswa tidak ditemukan.";
            $page = "data";
        }
    } else {
        $error = "NIM tidak ditemukan.";
        $page = "data";
    }
}


// 4. PENYUSUNAN QUERY SQL (WHERE, ORDER BY, LIMIT & OFFSET)

$whereConditions = [];
$params = [];
$types = "";

// Filter berdasarkan Nama Mahasiswa (LIKE)
if ($searchKeyword !== "") {
    $whereConditions[] = "nama_mhsw LIKE ?";
    $params[] = "%" . $searchKeyword . "%";
    $types .= "s";
}

// Filter berdasarkan Predikat
if ($filterPredikat !== "" && $filterPredikat !== "all") {
    $whereConditions[] = "predikat = ?";
    $params[] = $filterPredikat;
    $types .= "s";
}

// Penggabungan kondisi WHERE
$whereClause = "";
if (count($whereConditions) > 0) {
    $whereClause = " WHERE " . implode(" AND ", $whereConditions);
}

// A. Hitung Total Data (Untuk Perhitungan Pagination)
$sqlCount = "SELECT COUNT(*) AS total FROM nilai_mhsw" . $whereClause;
$stmtCount = mysqli_prepare($conn, $sqlCount);
if (count($params) > 0) {
    mysqli_stmt_bind_param($stmtCount, $types, ...$params);
}
mysqli_stmt_execute($stmtCount);
$resCount = mysqli_stmt_get_result($stmtCount);
$totalData = mysqli_fetch_assoc($resCount)['total'] ?? 0;
mysqli_stmt_close($stmtCount);

// Hitung total halaman dan nilai OFFSET query SQL
$totalHalaman = max(1, (int)ceil($totalData / $limit));
if ($halamanAktif > $totalHalaman) { $halamanAktif = $totalHalaman; }
$offset = ($halamanAktif - 1) * $limit;

// B. Query Utama Mengambil Data Tabel (Termasuk Sorting Tie-breaker & Limit Offset)
$sqlQuery = "SELECT nim, nama_mhsw, nilai_akhir, nilai_huruf, predikat, foto, reg_date 
             FROM nilai_mhsw" . $whereClause;

// Menambahkan nim ASC sebagai tie-breaker agar urutan data stabil di setiap halaman
if ($sort !== 'nim') {
  $sqlQuery .= " ORDER BY {$sort} " . strtoupper($order) . ", nim ASC";
} else {
  $sqlQuery .= " ORDER BY nim " . strtoupper($order);
}
$sqlQuery .= " LIMIT {$limit} OFFSET {$offset}";

$stmtData = mysqli_prepare($conn, $sqlQuery);
if (count($params) > 0) {
    mysqli_stmt_bind_param($stmtData, $types, ...$params);
}
mysqli_stmt_execute($stmtData);
$resultData = mysqli_stmt_get_result($stmtData);

// Penentuan judul halaman dinamis
$judulHalaman = "Data Nilai Mahasiswa";
if ($page === "tambah") {
    $judulHalaman = "Tambah Data Mahasiswa";
} elseif ($page === "edit") {
    $judulHalaman = "Edit Data Mahasiswa";
}
?><!doctype html>
<html lang="id">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo h($judulHalaman); ?> | Sistem Nilai Mahasiswa</title>

    <!-- Script Pendeteksi & Pengatur Tema Dark/Light AdminLTE -->
    <script>
      (() => {
        'use strict';
        const root = document.documentElement;
        if (root.getAttribute('data-lte-color-mode') === 'off') return;
        const STORAGE_KEY = 'lte-theme';
        let stored = null;
        try { stored = localStorage.getItem(STORAGE_KEY); } catch {}
        const authored = root.getAttribute('data-bs-theme');
        let resolved = 'light';
        if (stored === 'dark' || stored === 'light') {
          resolved = stored;
        } else if (authored === 'dark' || authored === 'light') {
          resolved = authored;
        } else if (globalThis.matchMedia('(prefers-color-scheme: dark)').matches) {
          resolved = 'dark';
        }
        root.setAttribute('data-bs-theme', resolved);
        root.style.colorScheme = resolved;
        if (resolved !== authored) root.setAttribute('data-lte-theme-resolved', '');
      })();
    </script>

    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />

    <!-- Dependency CSS: Fonts, Scrollbars, Icons, dan AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" crossorigin="anonymous" media="print" onload="this.media='all'" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    <link rel="preload" href="./css/adminlte.css" as="style" />
    <link rel="stylesheet" href="./css/adminlte.css" />
  </head>

  <body class="bg-body-tertiary">
    <div class="app-wrapper">
      <main class="app-main">
        
        <!-- Header Konten & Breadcrumb Navigation -->
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6">
                <h1 class="mb-0 fs-3"><?php echo h($judulHalaman); ?></h1>
              </div>
              <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="adminlte.php?page=data">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo h($judulHalaman); ?></li>
                  </ol>
                </nav>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">

            <!-- Notifikasi Pesan Eror -->
            <?php if ($error !== "") { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              <?php echo h($error); ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php } ?>

            <!-- TAMPILAN FORM EDIT DATA -->
            <?php if ($page === "edit") {
                $formNim   = $dataEdit["nim"]         ?? "";
                $formNama  = $dataEdit["nama_mhsw"] ?? "";
                $formNilai = $dataEdit["nilai_akhir"]  ?? "";
                $formFoto  = $dataEdit["foto"]         ?? "";
            ?>
            <div class="row">
              <div class="col-12 col-md-7 col-lg-6">
                <div class="card card-primary card-outline mb-4">
                  <div class="card-header">
                    <h3 class="card-title">Form Edit Nilai</h3>
                  </div>
                  <form action="adminlte.php?page=edit" method="post" enctype="multipart/form-data">
                    <div class="card-body">
                      <input type="hidden" name="aksi" value="edit">
                      <div class="mb-3">
                        <label for="nim" class="form-label">NIM</label>
                        <input class="form-control" type="text" id="nim" name="nim" maxlength="10" value="<?php echo h($formNim); ?>" readonly />
                        <small class="text-muted">Maksimal 10 karakter.</small>
                      </div>
                      <div class="mb-3">
                        <label for="namalengkap" class="form-label">Nama Lengkap</label>
                        <input class="form-control" type="text" id="namalengkap" name="nama_mhsw" value="<?php echo h($formNama); ?>" required />
                      </div>
                      <div class="mb-3">
                        <label for="nilaiakhir" class="form-label">Nilai Akhir</label>
                        <input class="form-control" type="number" id="nilaiakhir" name="nilai_akhir" min="0" max="100" step="0.01" value="<?php echo h($formNilai); ?>" required />
                      </div>
                      <div class="mb-3">
                        <label for="foto" class="form-label">Foto Mahasiswa (Upload Baru)</label>
                        <?php if (!empty($formFoto) && file_exists(__DIR__ . '/uploads/' . $formFoto)) { ?>
                          <div class="mb-2 d-flex align-items-center gap-2">
                            <img src="uploads/<?php echo h($formFoto); ?>" alt="Foto" class="rounded-circle" style="width:40px;height:40px;object-fit:cover;">
                            <span class="text-muted fs-7">Foto saat ini: <?php echo h($formFoto); ?></span>
                          </div>
                        <?php } ?>
                        <input class="form-control" type="file" id="foto" name="foto" accept="image/*" />
                        <small class="text-muted">Biarkan kosong jika tidak ingin mengubah foto.</small>
                      </div>
                    </div>
                    <div class="card-footer">
                      <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                      <a href="adminlte.php?page=data" class="btn btn-secondary ms-1"><i class="bi bi-x-circle me-1"></i>Batal</a>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- TAMPILAN UTAMA (TABEL DATA MAHASISWA) -->
            <?php } else { ?>
            <div class="row">
              <div class="col-12">
                <div class="card mb-4">
                  <div class="card-header">
                    <div class="row g-2 align-items-center">
                      <div class="col-12 col-md-3">
                        <h3 class="card-title">Data Nilai Mahasiswa</h3>
                      </div>
                      <div class="col-12 col-md-9">
                        <!-- Form Filter, Pencarian, & Opsi Limit -->
                        <form action="adminlte.php" method="get" class="d-flex flex-wrap justify-content-md-end gap-2 m-0">
                          <input type="hidden" name="page" value="data">
                          <input type="hidden" name="sort" value="<?php echo h($sort); ?>">
                          <input type="hidden" name="order" value="<?php echo h($order); ?>">
                          <input type="hidden" name="p" value="1">
                          
                          <!-- Opsi Limit Kelipatan 5 -->
                          <select name="limit" class="form-select form-select-sm w-auto" onchange="this.form.submit()" title="Jumlah data per halaman">
                            <option value="5" <?php echo $limit === 5 ? 'selected' : ''; ?>>5 data</option>
                            <option value="10" <?php echo $limit === 10 ? 'selected' : ''; ?>>10 data</option>
                            <option value="15" <?php echo $limit === 15 ? 'selected' : ''; ?>>15 data</option>
                          </select>

                          <!-- Input Pencarian Nama -->
                          <div class="input-group input-group-sm w-auto">
                            <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                            <input 
                              type="search" 
                              name="search" 
                              class="form-control" 
                              placeholder="Cari mahasiswa…" 
                              value="<?php echo h($searchKeyword); ?>" 
                              style="width:160px" 
                            />
                          </div>

                          <!-- Dropdown Filter Predikat -->
                          <select name="predikat" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            <option value="all" <?php echo $filterPredikat === 'all' ? 'selected' : ''; ?>>Semua Predikat</option>
                            <option value="Sangat Memuaskan" <?php echo $filterPredikat === 'Sangat Memuaskan' ? 'selected' : ''; ?>>Sangat Memuaskan</option>
                            <option value="Memuaskan" <?php echo $filterPredikat === 'Memuaskan' ? 'selected' : ''; ?>>Memuaskan</option>
                            <option value="Cukup Memuaskan" <?php echo $filterPredikat === 'Cukup Memuaskan' ? 'selected' : ''; ?>>Cukup Memuaskan</option>
                            <option value="Kurang Memuaskan" <?php echo $filterPredikat === 'Kurang Memuaskan' ? 'selected' : ''; ?>>Kurang Memuaskan</option>
                            <option value="Tidak Lulus" <?php echo $filterPredikat === 'Tidak Lulus' ? 'selected' : ''; ?>>Tidak Lulus</option>
                          </select>
                          <button type="submit" class="btn btn-sm btn-outline-secondary">Cari</button>
                          
                          <!-- Tombol Reset Filter -->
                          <?php if ($searchKeyword !== "" || $filterPredikat !== "all") { ?>
                            <a href="adminlte.php?page=data" class="btn btn-sm btn-outline-danger" title="Reset Filter"><i class="bi bi-x-lg"></i></a>
                          <?php } ?>

                          <!-- Tombol Buka Modal Tambah Data -->
                          <button type="button" class="btn btn-sm btn-primary ms-1" data-bs-toggle="modal" data-bs-target="#modalTambahData">
                            <i class="bi bi-person-plus-fill me-1" aria-hidden="true"></i>Tambah Data
                          </button>
                        </form>
                      </div>
                    </div>
                  </div>

                  <!-- Table Body -->
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-hover align-middle m-0" id="nilai-table">
                        <thead>
                          <tr>
                            <th>NIM</th>
                            <th>Nama Lengkap</th>
                            <!-- Header Sorting Interaktif Kolom Nilai Akhir -->
                            <th>
                              <a href="<?php echo buatUrlSort('nilai_akhir', $sort, $order); ?>" class="text-body text-decoration-none d-inline-flex align-items-center gap-1">
                                Nilai Akhir
                                <?php if ($sort === 'nilai_akhir') { ?>
                                  <i class="bi bi-arrow-<?php echo $order === 'asc' ? 'up' : 'down'; ?> text-primary fw-bold"></i>
                                <?php } else { ?>
                                  <i class="bi bi-arrow-down-up text-muted opacity-50 fs-7"></i>
                                <?php } ?>
                              </a>
                            </th>
                            <th>Nilai Huruf</th>
                            <th>Predikat</th>
                            <th>Tanggal</th>
                            <th class="text-end">Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                        <?php if ($resultData && mysqli_num_rows($resultData) > 0) { ?>
                          <?php while ($row = mysqli_fetch_assoc($resultData)) {
                              $regDate = !empty($row["reg_date"]) ? date("d M Y", strtotime($row["reg_date"])) : "-";
                              
                              // Verifikasi keberadaan file foto di folder uploads/
                              $fileFoto = $row["foto"] ?? "";
                              $srcFoto = (!empty($fileFoto) && file_exists(__DIR__ . '/uploads/' . $fileFoto))
                                          ? 'uploads/' . $fileFoto
                                          : null;
                          ?>
                          <tr>
                            <td><?php echo h($row["nim"]); ?></td>
                            <td>
                              <!-- Tampilan Gabung Foto & Nama Mahasiswa -->
                              <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-2">
                                  <?php if ($srcFoto) { ?>
                                    <img src="<?php echo h($srcFoto); ?>" alt="Foto <?php echo h($row["nama_mhsw"]); ?>" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                                  <?php } else { ?>
                                    <!-- Fallback Avatar Inisial Nama -->
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:.75rem;color:#fff;font-weight:600;">
                                      <?php echo strtoupper(mb_substr($row["nama_mhsw"], 0, 1)); ?>
                                    </div>
                                  <?php } ?>
                                </div>
                                <div>
                                  <span class="fw-medium d-block"><?php echo h($row["nama_mhsw"]); ?></span>
                                </div>
                              </div>
                            </td>
                            <td><strong class="text-primary"><?php echo h($row["nilai_akhir"]); ?></strong></td>
                            <td><?php echo h($row["nilai_huruf"]); ?></td>
                            <td>
                              <!-- Pewarnaan Badge Predikat Kelulusan (dengan Oranye untuk Kurang Memuaskan) -->
                              <?php
                                $badgeStyle = match($row["predikat"]) {
                                  'Sangat Memuaskan' => 'class="badge text-bg-success"',
                                  'Memuaskan'        => 'class="badge text-bg-info"',
                                  'Cukup Memuaskan'  => 'class="badge text-bg-warning"',
                                  'Kurang Memuaskan' => 'class="badge text-white" style="background-color: #fd7e14;"',
                                  'Tidak Lulus'      => 'class="badge text-bg-danger"',
                                  default            => 'class="badge text-bg-light"'
                                };
                              ?>
                              <span <?php echo $badgeStyle; ?>><?php echo h($row["predikat"]); ?></span>
                            </td>
                            <td><?php echo h($regDate); ?></td>
                            <td class="text-end">
                                <!-- Tombol Aksi Edit & Hapus -->
                                <div class="d-inline-flex align-items-center gap-1">
                                    <a href="adminlte.php?page=edit&amp;nim=<?php echo urlencode($row['nim']); ?>" class="btn btn-sm btn-outline-secondary" title="Edit Data">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="adminlte.php?page=data" method="post" class="m-0" onsubmit="return confirm('Yakin ingin menghapus data <?php echo h(addslashes($row['nama_mhsw'])); ?>?');">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="nim" value="<?php echo h($row['nim']); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Data">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                          </tr>
                          <?php } ?>
                        <?php } else { ?>
                          <!-- Pesan Jika Data Kosong / Tidak Ditemukan -->
                          <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                              <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                              Tidak ada data mahasiswa yang sesuai.
                            </td>
                          </tr>
                        <?php } ?>
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <!-- Footer Navigasi Halaman (Pagination) -->
                  <div class="card-footer clearfix">
                    <div class="float-start pt-1 fs-7 text-body-secondary">
                      Menampilkan 
                      <strong><?php echo $totalData > 0 ? $offset + 1 : 0; ?></strong> – 
                      <strong><?php echo min($offset + $limit, $totalData); ?></strong> dari 
                      <strong><?php echo $totalData; ?></strong> data
                    </div>
                    
                    <?php if ($totalHalaman > 1) { ?>
                    <ul class="pagination pagination-sm m-0 float-end">
                      <!-- Tombol First Page (Ke Halaman Pertama) -->
                      <li class="page-item <?php echo ($halamanAktif <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo buatUrl(['p' => 1]); ?>" title="Halaman Pertama">&laquo;&laquo;</a>
                      </li>

                      <!-- Tombol Previous (Ke Halaman Sebelumnya) -->
                      <li class="page-item <?php echo ($halamanAktif <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo buatUrl(['p' => $halamanAktif - 1]); ?>" title="Sebelumnya">&laquo;</a>
                      </li>

                      <!-- Daftar Angka Halaman -->
                      <?php for ($i = 1; $i <= $totalHalaman; $i++) { ?>
                        <li class="page-item <?php echo ($i === $halamanAktif) ? 'active' : ''; ?>">
                          <a class="page-link" href="<?php echo buatUrl(['p' => $i]); ?>"><?php echo $i; ?></a>
                        </li>
                      <?php } ?>

                      <!-- Tombol Next (Ke Halaman Berikutnya) -->
                      <li class="page-item <?php echo ($halamanAktif >= $totalHalaman) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo buatUrl(['p' => $halamanAktif + 1]); ?>" title="Berikutnya">&raquo;</a>
                      </li>

                      <!-- Tombol Last Page (Ke Halaman Terakhir) -->
                      <li class="page-item <?php echo ($halamanAktif >= $totalHalaman) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo buatUrl(['p' => $totalHalaman]); ?>" title="Halaman Terakhir">&raquo;&raquo;</a>
                      </li>
                    </ul>
                    <?php } ?>
                  </div>
                </div>
              </div>
            </div>

            <!-- MODAL TAMBAH DATA MAHASISWA -->
            <div class="modal fade" id="modalTambahData" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold">
                      <i class="bi bi-person-plus-fill me-2 text-primary"></i>Tambah Data Mahasiswa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form action="adminlte.php?page=data" method="post" enctype="multipart/form-data">
                    <div class="modal-body pt-3">
                      <input type="hidden" name="aksi" value="tambah">
                      <div class="mb-3">
                        <label for="modal-nim" class="form-label fw-medium">NIM</label>
                        <input class="form-control" type="text" id="modal-nim" name="nim" maxlength="10" placeholder="Contoh: 3202416113" required />
                      </div>
                      <div class="mb-3">
                        <label for="modal-namalengkap" class="form-label fw-medium">Nama Lengkap</label>
                        <input class="form-control" type="text" id="modal-namalengkap" name="nama_mhsw" placeholder="Contoh: Nazriel Putra Febrinanda" required />
                      </div>
                      <div class="mb-3">
                        <label for="modal-nilaiakhir" class="form-label fw-medium">Nilai Akhir</label>
                        <input class="form-control" type="number" id="modal-nilaiakhir" name="nilai_akhir" min="0" max="100" step="0.01" placeholder="0 – 100" required />
                      </div>
                      <div class="mb-3">
                        <label for="modal-foto" class="form-label fw-medium">Foto Mahasiswa (Opsional)</label>
                        <input class="form-control" type="file" id="modal-foto" name="foto" accept="image/*" />
                        <div class="form-text">Pilih file foto (JPG, PNG, WebP).</div>
                      </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                      <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Data</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <?php } ?>

          </div>
        </div>
      </main>

      <!-- Footer Hak Cipta -->
      <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">Anything you want</div>
        <strong>Hak Cipta &copy; Prodi TI 2026 <a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>.</strong> All rights reserved.
      </footer>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="./js/adminlte.js"></script>

    <!-- Inisialisasi OverlayScrollbars bawaan AdminLTE 4 -->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        const isMobile = window.innerWidth <= 992;
        if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined && !isMobile) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
  </body>
</html>
<?php
// Bebaskan memori hasil query dan tutup koneksi database MySQL
if (isset($stmtData) && $stmtData instanceof mysqli_stmt) {
    mysqli_stmt_close($stmtData);
}
mysqli_close($conn);
?>