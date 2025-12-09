<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "operator";
include "../auth.php";
include "../../conn.php";

$page_title = "Anggota Lab";
$current_page = "anggota.php";

// Pagination settings
$items_per_page = 10;
$current_page_num = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page_num - 1) * $items_per_page;

// Handle Delete - operator hanya bisa hapus data miliknya
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt_check = $pdo->prepare("SELECT id_user, foto FROM anggota_lab WHERE id_anggota = ?");
        $stmt_check->execute([$id]);
        $data_owner = $stmt_check->fetch();
        
        if ($data_owner && $data_owner['id_user'] == $_SESSION['id_user']) {
            $stmt_old = $pdo->prepare("SELECT nama, status FROM anggota_lab WHERE id_anggota = ?");
            $stmt_old->execute([$id]);
            $old_data = $stmt_old->fetch();
            
            $stmt = $pdo->prepare("DELETE FROM anggota_lab WHERE id_anggota = ?");
            $stmt->execute([$id]);
            
            if ($data_owner['foto']) {
                $foto_path = "../../uploads/anggota/" . $data_owner['foto'];
                if (file_exists($foto_path)) {
                    unlink($foto_path);
                }
            }
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['anggota_lab', $id, $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus anggota: ' . $old_data['nama']]);
            
            $success = "Anggota berhasil dihapus!";
        } else {
            $error = "Anda tidak memiliki akses untuk menghapus data ini!";
        }
    } catch (PDOException $e) {
        $error = "Gagal menghapus: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $nip = $_POST['nip'];
    $email = $_POST['email'];
    $kontak = $_POST['kontak'];
    $biodata_teks = $_POST['biodata_teks'];
    $tanggal_bergabung = $_POST['tanggal_bergabung'];
    
    // Process pendidikan array
    $pendidikan_array = [];
    if (isset($_POST['pendidikan_jenjang']) && is_array($_POST['pendidikan_jenjang'])) {
        foreach ($_POST['pendidikan_jenjang'] as $index => $jenjang) {
            if (!empty($jenjang) && !empty($_POST['pendidikan_institusi'][$index])) {
                $pendidikan_array[] = [
                    'jenjang' => $jenjang,
                    'institusi' => $_POST['pendidikan_institusi'][$index],
                    'tahun' => $_POST['pendidikan_tahun'][$index] ?? '',
                    'jurusan' => $_POST['pendidikan_jurusan'][$index] ?? ''
                ];
            }
        }
    }
    $pendidikan = json_encode($pendidikan_array);
    
    // Process bidang keahlian array (mata kuliah)
    $bidang_keahlian_array = [];
    if (isset($_POST['matakuliah_nama']) && is_array($_POST['matakuliah_nama'])) {
        foreach ($_POST['matakuliah_nama'] as $index => $nama_mk) {
            if (!empty($nama_mk)) {
                $bidang_keahlian_array[] = [
                    'nama' => $nama_mk
                ];
            }
        }
    }
    $bidang_keahlian = json_encode($bidang_keahlian_array);
    
    // AUTO PENDING untuk operator
    $status = 'pending';
    
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "../../uploads/anggota/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = 'anggota_' . time() . '.' . $file_extension;
        move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $foto);
    }
    
    try {
        if (isset($_POST['id_anggota']) && !empty($_POST['id_anggota'])) {
            $id = $_POST['id_anggota'];
            
            // Cek kepemilikan data
            $stmt_check = $pdo->prepare("SELECT id_user, status, foto FROM anggota_lab WHERE id_anggota = ?");
            $stmt_check->execute([$id]);
            $data_owner = $stmt_check->fetch();
            
            if ($data_owner && $data_owner['id_user'] == $_SESSION['id_user']) {
                $status_lama = $data_owner['status'];
                $old_foto = $data_owner['foto'];
                
                if ($foto) {
                    if ($old_foto) {
                        $old_foto_path = "../../uploads/anggota/" . $old_foto;
                        if (file_exists($old_foto_path)) {
                            unlink($old_foto_path);
                        }
                    }
                    
                    $stmt = $pdo->prepare("UPDATE anggota_lab SET nama=?, nip=?, email=?, kontak=?, biodata_teks=?, pendidikan=?, bidang_keahlian=?, tanggal_bergabung=?, foto=?, status=? WHERE id_anggota=?");
                    $stmt->execute([$nama, $nip, $email, $kontak, $biodata_teks, $pendidikan, $bidang_keahlian, $tanggal_bergabung, $foto, $status, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE anggota_lab SET nama=?, nip=?, email=?, kontak=?, biodata_teks=?, pendidikan=?, bidang_keahlian=?, tanggal_bergabung=?, status=? WHERE id_anggota=?");
                    $stmt->execute([$nama, $nip, $email, $kontak, $biodata_teks, $pendidikan, $bidang_keahlian, $tanggal_bergabung, $status, $id]);
                }
                
                if ($status_lama != $status) {
                    $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_riwayat->execute(['anggota_lab', $id, $_SESSION['id_user'], $status_lama, $status, 'Update anggota: ' . $nama]);
                }
                
                $success = "Anggota berhasil diupdate! Menunggu persetujuan admin.";
            } else {
                $error = "Anda tidak memiliki akses untuk mengubah data ini!";
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO anggota_lab (nama, nip, email, kontak, biodata_teks, pendidikan, bidang_keahlian, tanggal_bergabung, foto, status, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nama, $nip, $email, $kontak, $biodata_teks, $pendidikan, $bidang_keahlian, $tanggal_bergabung, $foto, $status, $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['anggota_lab', $new_id, $_SESSION['id_user'], null, $status, 'Tambah anggota: ' . $nama]);
            
            $success = "Anggota berhasil ditambahkan! Menunggu persetujuan admin.";
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Get total count - operator hanya lihat data miliknya sendiri
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM anggota_lab WHERE id_user = ?");
$count_stmt->execute([$_SESSION['id_user']]);
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Operator hanya bisa lihat data miliknya sendiri
$stmt = $pdo->prepare("SELECT * FROM anggota_lab WHERE id_user = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$_SESSION['id_user'], $items_per_page, $offset]);
$anggota_list = $stmt->fetchAll();

// HAPUS 2 query yang duplikat dan ganti dengan ini (setelah Handle Add/Edit):

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';

$where_clauses = ["id_user = ?"];
$params = [$_SESSION['id_user']];

if ($search) {
    $where_clauses[] = "(nama ILIKE ? OR nip ILIKE ? OR email ILIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($status_filter) {
    $where_clauses[] = "status = ?";
    $params[] = $status_filter;
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

// Get total count with filters
$count_query = "SELECT COUNT(*) FROM anggota_lab $where_sql";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get data with pagination and filters
$query = "SELECT * FROM anggota_lab $where_sql ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params_with_limit = array_merge($params, [$items_per_page, $offset]);
$stmt = $pdo->prepare($query);
$stmt->execute($params_with_limit);
$anggota_list = $stmt->fetchAll();

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manajemen Anggota Lab</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#anggotaModal" onclick="resetForm()">
        <i class="bi bi-plus-circle"></i> Tambah Anggota
    </button>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> <strong>Info:</strong> Semua data yang Anda tambahkan akan berstatus <span class="badge bg-warning">Pending</span> dan menunggu persetujuan admin.
</div>

<!-- Search & Filter Card -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <label class="form-label"><i class="bi bi-search"></i> Cari Anggota</label>
                <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan nama, NIP, atau email..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label"><i class="bi bi-flag"></i> Status</label>
                <select class="form-select" name="status_filter">
                    <option value="">Semua</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Cari
                </button>
                <a href="anggota.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<?php if ($search || $status_filter): ?>
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> 
    Menampilkan <?php echo count($anggota_list); ?> hasil
    <?php if ($search): ?>
        untuk pencarian "<strong><?php echo htmlspecialchars($search); ?></strong>"
    <?php endif; ?>
    <?php if ($status_filter): ?>
        dengan status <strong><?php echo $status_filter; ?></strong>
    <?php endif; ?>
    <a href="anggota.php" class="alert-link ms-2">Reset pencarian</a>
</div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            Total: <?php echo $total_items; ?> anggota
            <?php if ($search || $status_filter): ?>
                <span class="badge bg-info">Filtered</span>
            <?php endif; ?>
        </h6>
        <span class="text-muted">Halaman <?php echo $current_page_num; ?> dari <?php echo max(1, $total_pages); ?></span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (count($anggota_list) > 0) {
                        $no = $offset + 1;
                        foreach ($anggota_list as $anggota): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <?php if ($anggota['foto']): ?>
                                <img src="../../uploads/anggota/<?php echo $anggota['foto']; ?>" width="50" height="50" class="rounded-circle">
                            <?php else: ?>
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($anggota['nama']); ?>" width="50" height="50" class="rounded-circle">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($anggota['nama']); ?></td>
                        <td><?php echo htmlspecialchars($anggota['nip'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($anggota['email'] ?? '-'); ?></td>
                        <td>
                            <?php if ($anggota['status'] == 'pending'): ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php elseif ($anggota['status'] == 'active'): ?>
                                <span class="badge bg-success">Approved</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Rejected</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($anggota['status'] == 'pending' || $anggota['status'] == 'rejected'): ?>
                                <button class="btn btn-sm btn-warning" onclick='editAnggota(<?php echo htmlspecialchars(json_encode($anggota), ENT_QUOTES, 'UTF-8'); ?>)'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="?delete=<?php echo $anggota['id_anggota']; ?>&page=<?php echo $current_page_num; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">Sudah disetujui</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                        endforeach;
                    } else {
                    ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-3 text-muted">
                                <?php if ($search || $status_filter): ?>
                                    Tidak ada anggota yang sesuai dengan pencarian
                                <?php else: ?>
                                    Belum ada anggota. Klik tombol "Tambah Anggota" untuk memulai.
                                <?php endif; ?>
                            </p>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($total_pages > 1): ?>
    <div class="card-footer bg-white">
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?php echo $current_page_num <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $current_page_num - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status_filter=' . urlencode($status_filter) : ''; ?>">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                </li>
                
                <?php
                $start_page = max(1, $current_page_num - 2);
                $end_page = min($total_pages, $current_page_num + 2);
                
                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=1' . ($search ? '&search=' . urlencode($search) : '') . ($status_filter ? '&status_filter=' . urlencode($status_filter) : '') . '">1</a></li>';
                    if ($start_page > 2) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $active = $i == $current_page_num ? 'active' : '';
                    echo "<li class='page-item $active'><a class='page-link' href='?page=$i" . ($search ? '&search=' . urlencode($search) : '') . ($status_filter ? '&status_filter=' . urlencode($status_filter) : '') . "'>$i</a></li>";
                }
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    echo "<li class='page-item'><a class='page-link' href='?page=$total_pages" . ($search ? '&search=' . urlencode($search) : '') . ($status_filter ? '&status_filter=' . urlencode($status_filter) : '') . "'>$total_pages</a></li>";
                }
                ?>
                
                <li class="page-item <?php echo $current_page_num >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $current_page_num + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status_filter=' . urlencode($status_filter) : ''; ?>">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Form (sama seperti sebelumnya dengan form lengkap) -->
<div class="modal fade" id="anggotaModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Anggota Lab</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_anggota" id="id_anggota">
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> Data akan berstatus <strong>Pending</strong> dan menunggu persetujuan admin
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" name="nama" id="nama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIP</label>
                            <input type="text" class="form-control" name="nip" id="nip">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="email">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kontak</label>
                            <input type="text" class="form-control" name="kontak" id="kontak">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Bergabung</label>
                            <input type="date" class="form-control" name="tanggal_bergabung" id="tanggal_bergabung">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Foto</label>
                        <div id="currentFotoPreview" style="display: none;" class="mb-2">
                            <img id="currentFotoImg" src="" width="100" height="100" class="rounded-circle">
                            <p class="small text-muted mb-0">Foto saat ini (pilih file baru untuk menggantinya)</p>
                        </div>
                        <input type="file" class="form-control" name="foto" accept="image/*">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Biodata</label>
                        <textarea class="form-control" name="biodata_teks" id="biodata_teks" rows="3"></textarea>
                    </div>
                    
                    <hr class="my-4">
                    <h6 class="mb-3">Riwayat Pendidikan</h6>
                    
                    <div id="pendidikanContainer">
                        <!-- Will be filled by JS -->
                    </div>
                    
                    <button type="button" class="btn btn-sm btn-outline-primary mb-3" onclick="addPendidikan()">
                        <i class="bi bi-plus-circle"></i> Tambah Pendidikan
                    </button>
                    
                    <hr class="my-4">
                    <h6 class="mb-3">Mata Kuliah yang Diampu</h6>

                    <div id="matakuliahContainer">
                        <!-- Will be filled by JS -->
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary mb-3" onclick="addMatakuliah()">
                        <i class="bi bi-plus-circle"></i> Tambah Mata Kuliah
                    </button>                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan & Ajukan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// JavaScript functions dari file asli
function addPendidikan() {
    const container = document.getElementById('pendidikanContainer');
    const newItem = document.createElement('div');
    newItem.className = 'pendidikan-item border rounded p-3 mb-3 bg-light';
    newItem.innerHTML = `
        <div class="row">
            <div class="col-md-3 mb-2">
                <label class="form-label small">Jenjang *</label>
                <select class="form-select form-select-sm" name="pendidikan_jenjang[]" required>
                    <option value="">Pilih Jenjang</option>
                    <option value="D3">D3</option>
                    <option value="D4">D4</option>
                    <option value="S1">S1</option>
                    <option value="S2">S2</option>
                    <option value="S3">S3</option>
                    <option value="Profesi">Profesi</option>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label small">Institusi *</label>
                <input type="text" class="form-control form-control-sm" name="pendidikan_institusi[]" placeholder="Universitas..." required>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label small">Jurusan</label>
                <input type="text" class="form-control form-control-sm" name="pendidikan_jurusan[]" placeholder="Teknik Informatika...">
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label small">Tahun</label>
                <div class="d-flex gap-1">
                    <input type="text" class="form-control form-control-sm" name="pendidikan_tahun[]" placeholder="2020">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removePendidikan(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.appendChild(newItem);
    updateDeleteButtons('pendidikan');
}

function removePendidikan(button) {
    button.closest('.pendidikan-item').remove();
    updateDeleteButtons('pendidikan');
}

function addMatakuliah() {
    const container = document.getElementById('matakuliahContainer');
    const newItem = document.createElement('div');
    newItem.className = 'matakuliah-item border rounded p-3 mb-3 bg-light';
    newItem.innerHTML = `
        <div class="row align-items-end">
            <div class="col-auto mb-2">
                <label class="form-label small d-block">&nbsp;</label>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeMatakuliah(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="col mb-2">
                <label class="form-label small">Nama Mata Kuliah *</label>
                <input type="text" class="form-control form-control-sm" name="matakuliah_nama[]" placeholder="Pemrograman Web" required>
            </div>
        </div>
    `;
    container.appendChild(newItem);
    updateDeleteButtons('matakuliah');
}

function removeMatakuliah(button) {
    button.closest('.matakuliah-item').remove();
    updateDeleteButtons('matakuliah');
}

function updateDeleteButtons(type) {
    const items = document.querySelectorAll(`.${type}-item`);
    items.forEach((item, index) => {
        const deleteBtn = item.querySelector('.btn-danger');
        if (items.length > 1) {
            deleteBtn.style.display = 'block';
        } else {
            deleteBtn.style.display = 'none';
        }
    });
}

function resetForm() {
    document.getElementById('modalTitle').textContent = 'Tambah Anggota Lab';
    document.querySelector('form').reset();
    document.getElementById('id_anggota').value = '';
    document.getElementById('currentFotoPreview').style.display = 'none';
    
    // Reset pendidikan
    const pendidikanContainer = document.getElementById('pendidikanContainer');
    pendidikanContainer.innerHTML = `
        <div class="pendidikan-item border rounded p-3 mb-3 bg-light">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label small">Jenjang *</label>
                    <select class="form-select form-select-sm" name="pendidikan_jenjang[]" required>
                        <option value="">Pilih Jenjang</option>
                        <option value="D3">D3</option>
                        <option value="D4">D4</option>
                        <option value="S1">S1</option>
                        <option value="S2">S2</option>
                        <option value="S3">S3</option>
                        <option value="Profesi">Profesi</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label small">Institusi *</label>
                    <input type="text" class="form-control form-control-sm" name="pendidikan_institusi[]" placeholder="Universitas..." required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label small">Jurusan</label>
                    <input type="text" class="form-control form-control-sm" name="pendidikan_jurusan[]" placeholder="Teknik Informatika...">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label small">Tahun</label>
                    <div class="d-flex gap-1">
                        <input type="text" class="form-control form-control-sm" name="pendidikan_tahun[]" placeholder="2020">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removePendidikan(this)" style="display: none;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Reset mata kuliah
    const matakuliahContainer = document.getElementById('matakuliahContainer');
    matakuliahContainer.innerHTML = `
        <div class="matakuliah-item border rounded p-3 mb-3 bg-light">
            <div class="row align-items-end">
                <div class="col-auto mb-2">
                    <label class="form-label small d-block">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeMatakuliah(this)" style="display: none;">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="col mb-2">
                    <label class="form-label small">Nama Mata Kuliah *</label>
                    <input type="text" class="form-control form-control-sm" name="matakuliah_nama[]" placeholder="Pemrograman Web" required>
                </div>
            </div>
        </div>
    `;
}

function addPendidikan() {
    const container = document.getElementById('pendidikanContainer');
    const newItem = document.createElement('div');
    newItem.className = 'pendidikan-item border rounded p-3 mb-3 bg-light';
    newItem.innerHTML = `
        <div class="row">
            <div class="col-md-3 mb-2">
                <label class="form-label small">Jenjang *</label>
                <select class="form-select form-select-sm" name="pendidikan_jenjang[]" required>
                    <option value="">Pilih Jenjang</option>
                    <option value="D3">D3</option>
                    <option value="D4">D4</option>
                    <option value="S1">S1</option>
                    <option value="S2">S2</option>
                    <option value="S3">S3</option>
                    <option value="Profesi">Profesi</option>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label small">Institusi *</label>
                <input type="text" class="form-control form-control-sm" name="pendidikan_institusi[]" placeholder="Universitas..." required>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label small">Jurusan</label>
                <input type="text" class="form-control form-control-sm" name="pendidikan_jurusan[]" placeholder="Teknik Informatika...">
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label small">Tahun</label>
                <div class="d-flex gap-1">
                    <input type="text" class="form-control form-control-sm" name="pendidikan_tahun[]" placeholder="2020">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removePendidikan(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.appendChild(newItem);
    updateDeleteButtons('pendidikan');
}

function removePendidikan(button) {
    button.closest('.pendidikan-item').remove();
    updateDeleteButtons('pendidikan');
}

function addMatakuliah() {
    const container = document.getElementById('matakuliahContainer');
    const newItem = document.createElement('div');
    newItem.className = 'matakuliah-item border rounded p-3 mb-3 bg-light';
    newItem.innerHTML = `
        <div class="row align-items-end">
            <div class="col-auto mb-2">
                <label class="form-label small d-block">&nbsp;</label>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeMatakuliah(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="col mb-2">
                <label class="form-label small">Nama Mata Kuliah *</label>
                <input type="text" class="form-control form-control-sm" name="matakuliah_nama[]" placeholder="Pemrograman Web" required>
            </div>
        </div>
    `;
    container.appendChild(newItem);
    updateDeleteButtons('matakuliah');
}

function removeMatakuliah(button) {
    button.closest('.matakuliah-item').remove();
    updateDeleteButtons('matakuliah');
}

function updateDeleteButtons(type) {
    const items = document.querySelectorAll(`.${type}-item`);
    items.forEach((item, index) => {
        const deleteBtn = item.querySelector('.btn-danger');
        if (items.length > 1) {
            deleteBtn.style.display = 'block';
        } else {
            deleteBtn.style.display = 'none';
        }
    });
}

function resetForm() {
    document.getElementById('modalTitle').textContent = 'Tambah Anggota Lab';
    document.querySelector('form').reset();
    document.getElementById('id_anggota').value = '';
    
    // Remove foto preview if exists
    const existingPreview = document.getElementById('currentFotoPreview');
    if (existingPreview) {
        existingPreview.remove();
    }
    
    // Reset pendidikan - add one default item
    const pendidikanContainer = document.getElementById('pendidikanContainer');
    pendidikanContainer.innerHTML = '';
    addPendidikan();
    
    // Reset mata kuliah - add one default item
    const matakuliahContainer = document.getElementById('matakuliahContainer');
    matakuliahContainer.innerHTML = '';
    addMatakuliah();
}

function editAnggota(data) {
    document.getElementById('modalTitle').textContent = 'Edit Anggota Lab';
    document.getElementById('id_anggota').value = data.id_anggota;
    document.getElementById('nama').value = data.nama;
    document.getElementById('nip').value = data.nip || '';  // ← Field di operator memang 'nip' bukan 'nip_nim'
    document.getElementById('email').value = data.email || '';
    document.getElementById('kontak').value = data.kontak || '';
    document.getElementById('biodata_teks').value = data.biodata_teks || '';
    document.getElementById('tanggal_bergabung').value = data.tanggal_bergabung || '';
    
    // Show current photo preview if exists
    const existingPreview = document.getElementById('currentFotoPreview');
    if (existingPreview) {
        existingPreview.remove();
    }
    
    if (data.foto) {
        const fotoInput = document.querySelector('input[name="foto"]');
        const previewDiv = document.createElement('div');
        previewDiv.id = 'currentFotoPreview';
        previewDiv.className = 'mb-2';
        previewDiv.innerHTML = `
            <img src="../../uploads/anggota/${data.foto}" width="100" height="100" class="rounded-circle">
            <p class="small text-muted mb-0">Foto saat ini (pilih file baru untuk menggantinya)</p>
        `;
        fotoInput.parentElement.insertBefore(previewDiv, fotoInput);
    }
    
    // Load pendidikan data
    const pendidikanContainer = document.getElementById('pendidikanContainer');
    pendidikanContainer.innerHTML = '';
    
    let pendidikanData = [];
    if (data.pendidikan) {
        try {
            pendidikanData = typeof data.pendidikan === 'string' 
                ? JSON.parse(data.pendidikan) 
                : data.pendidikan;
        } catch (e) {
            console.error('Error parsing pendidikan:', e);
        }
    }
    
    if (pendidikanData.length > 0) {
        pendidikanData.forEach(item => {
            const newItem = document.createElement('div');
            newItem.className = 'pendidikan-item border rounded p-3 mb-3 bg-light';
            newItem.innerHTML = `
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="form-label small">Jenjang *</label>
                        <select class="form-select form-select-sm" name="pendidikan_jenjang[]" required>
                            <option value="">Pilih Jenjang</option>
                            <option value="D3" ${item.jenjang === 'D3' ? 'selected' : ''}>D3</option>
                            <option value="D4" ${item.jenjang === 'D4' ? 'selected' : ''}>D4</option>
                            <option value="S1" ${item.jenjang === 'S1' ? 'selected' : ''}>S1</option>
                            <option value="S2" ${item.jenjang === 'S2' ? 'selected' : ''}>S2</option>
                            <option value="S3" ${item.jenjang === 'S3' ? 'selected' : ''}>S3</option>
                            <option value="Profesi" ${item.jenjang === 'Profesi' ? 'selected' : ''}>Profesi</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label small">Institusi *</label>
                        <input type="text" class="form-control form-control-sm" name="pendidikan_institusi[]" placeholder="Universitas..." value="${item.institusi || ''}" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label small">Jurusan</label>
                        <input type="text" class="form-control form-control-sm" name="pendidikan_jurusan[]" placeholder="Teknik Informatika..." value="${item.jurusan || ''}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label small">Tahun</label>
                        <div class="d-flex gap-1">
                            <input type="text" class="form-control form-control-sm" name="pendidikan_tahun[]" placeholder="2020" value="${item.tahun || ''}">
                            <button type="button" class="btn btn-sm btn-danger" onclick="removePendidikan(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            pendidikanContainer.appendChild(newItem);
        });
    } else {
        addPendidikan();
    }
    updateDeleteButtons('pendidikan');
    
    // Load mata kuliah data
    const matakuliahContainer = document.getElementById('matakuliahContainer');
    matakuliahContainer.innerHTML = '';
    
    let matakuliahData = [];
    if (data.bidang_keahlian) {
        try {
            matakuliahData = typeof data.bidang_keahlian === 'string' 
                ? JSON.parse(data.bidang_keahlian) 
                : data.bidang_keahlian;
        } catch (e) {
            console.error('Error parsing bidang_keahlian:', e);
        }
    }
    
    if (matakuliahData.length > 0) {
        matakuliahData.forEach(item => {
            const newItem = document.createElement('div');
            newItem.className = 'matakuliah-item border rounded p-3 mb-3 bg-light';
            newItem.innerHTML = `
                <div class="row align-items-end">
                    <div class="col-auto mb-2">
                        <label class="form-label small d-block">&nbsp;</label>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeMatakuliah(this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="col mb-2">
                        <label class="form-label small">Nama Mata Kuliah *</label>
                        <input type="text" class="form-control form-control-sm" name="matakuliah_nama[]" placeholder="Pemrograman Web" value="${item.nama || ''}" required>
                    </div>
                </div>
            `;
            matakuliahContainer.appendChild(newItem);
        });
    } else {
        addMatakuliah();
    }
    updateDeleteButtons('matakuliah');
    
    // Show modal
    new bootstrap.Modal(document.getElementById('anggotaModal')).show();
}
</script>

<style>
.pagination .page-link {
    color: #4e73df;
}
.pagination .page-item.active .page-link {
    background-color: #4e73df;
    border-color: #4e73df;
}
</style>

<?php include "footer.php"; ?>