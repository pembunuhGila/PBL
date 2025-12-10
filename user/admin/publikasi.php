<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "admin";
include "../auth.php";
include "../../conn.php";

$page_title = "Publikasi";
$current_page = "publikasi.php";

// Pagination setup
$limit = 10; // Items per page
$page_num = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page_num - 1) * $limit;

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$year_filter = isset($_GET['year']) ? trim($_GET['year']) : '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt_old = $pdo->prepare("SELECT judul, status FROM publikasi WHERE id_publikasi = ?");
        $stmt_old->execute([$id]);
        $old_data = $stmt_old->fetch();
        
        $stmt = $pdo->prepare("DELETE FROM publikasi WHERE id_publikasi = ?");
        $stmt->execute([$id]);
        
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_riwayat->execute(['publikasi', $id, $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus publikasi: ' . $old_data['judul']]);
        
        header("Location: publikasi.php?success=deleted");
        exit;
    } catch (PDOException $e) {
        $error = "Gagal menghapus: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $abstrak = $_POST['abstrak'];
    $tahun = $_POST['tahun'];
    $jurnal = $_POST['jurnal'];
    $link_shinta = $_POST['link_shinta'] ?? '';
    $tanggal_publikasi = $_POST['tanggal_publikasi'];
    $status = 'active'; 
    $penulis_ids = $_POST['penulis'] ?? [];
    
    // Validasi jika edit: cek apakah status rejected
    if (isset($_POST['id_publikasi']) && !empty($_POST['id_publikasi'])) {
        $id = $_POST['id_publikasi'];
        
        $stmt_check = $pdo->prepare("SELECT status FROM publikasi WHERE id_publikasi = ?");
        $stmt_check->execute([$id]);
        $current_data = $stmt_check->fetch();
        
        if ($current_data && $current_data['status'] === 'rejected') {
            header("Location: publikasi.php?error=rejected");
            exit;
        }
    }
    
    // Handle cover upload
    $cover = null;
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
        $target_dir = "../../uploads/publikasi/cover/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $file_extension = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
        $cover = 'cover_' . time() . '.' . $file_extension;
        move_uploaded_file($_FILES['cover']['tmp_name'], $target_dir . $cover);
    }
    
    // Handle file upload
    $file_path = null;
    if (isset($_FILES['file_path']) && $_FILES['file_path']['error'] == 0) {
        $target_dir = "../../uploads/publikasi/files/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $file_extension = pathinfo($_FILES['file_path']['name'], PATHINFO_EXTENSION);
        $file_path = 'pub_' . time() . '.' . $file_extension;
        move_uploaded_file($_FILES['file_path']['tmp_name'], $target_dir . $file_path);
    }
    
    try {
        $pdo->beginTransaction();
        
        if (isset($_POST['id_publikasi']) && !empty($_POST['id_publikasi'])) {
            $id = $_POST['id_publikasi'];
            
            $stmt_old = $pdo->prepare("SELECT status FROM publikasi WHERE id_publikasi = ?");
            $stmt_old->execute([$id]);
            $old_data = $stmt_old->fetch();
            $status_lama = $old_data['status'];
            
            $sql = "UPDATE publikasi SET judul=?, abstrak=?, tahun=?, jurnal=?, link_shinta=?, tanggal_publikasi=?, status=?";
            $params = [$judul, $abstrak, $tahun, $jurnal, $link_shinta, $tanggal_publikasi, $status];
            
            if ($cover) {
                $sql .= ", cover=?";
                $params[] = $cover;
            }
            if ($file_path) {
                $sql .= ", file_path=?";
                $params[] = $file_path;
            }
            
            $sql .= " WHERE id_publikasi=?";
            $params[] = $id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $pdo->prepare("DELETE FROM publikasi_anggota WHERE id_publikasi=?")->execute([$id]);
            
            if ($status_lama != $status) {
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['publikasi', $id, $_SESSION['id_user'], $status_lama, $status, 'Update publikasi: ' . $judul]);
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO publikasi (judul, abstrak, tahun, jurnal, link_shinta, tanggal_publikasi, cover, file_path, status, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$judul, $abstrak, $tahun, $jurnal, $link_shinta, $tanggal_publikasi, $cover, $file_path, $status, $_SESSION['id_user']]);
            
            $id = $pdo->lastInsertId();
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['publikasi', $id, $_SESSION['id_user'], null, $status, 'Tambah publikasi: ' . $judul]);
        }
        
        // Insert penulis
        $valid_penulis = array_filter($penulis_ids, function($p) { return $p !== '' && $p !== null; });
        foreach ($valid_penulis as $index => $id_anggota) {
            $stmt = $pdo->prepare("INSERT INTO publikasi_anggota (id_publikasi, id_anggota, urutan_penulis) VALUES (?, ?, ?)");
            $stmt->execute([$id, (int)$id_anggota, $index + 1]);
        }
        
        $pdo->commit();
        header("Location: publikasi.php?success=" . (isset($_POST['id_publikasi']) ? 'updated' : 'added'));
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Build WHERE clause for search
$where_conditions = [];
$where_params = [];

if (!empty($search)) {
    $where_conditions[] = "(p.judul ILIKE ? OR p.jurnal ILIKE ?)";
    $search_term = "%$search%";
    $where_params[] = $search_term;
    $where_params[] = $search_term;
}

if (!empty($year_filter)) {
    $where_conditions[] = "p.tahun = ?";
    $where_params[] = $year_filter;
}

$where_sql = '';
if (count($where_conditions) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

// Get total count for pagination
$count_query = "SELECT COUNT(DISTINCT p.id_publikasi) FROM publikasi p $where_sql";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($where_params);
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $limit);

// Get data with pagination
$query = "
    SELECT p.*, 
           STRING_AGG(a.nama, ', ' ORDER BY pa.urutan_penulis) as penulis
    FROM publikasi p
    LEFT JOIN publikasi_anggota pa ON p.id_publikasi = pa.id_publikasi
    LEFT JOIN anggota_lab a ON pa.id_anggota = a.id_anggota
    $where_sql
    GROUP BY p.id_publikasi
    ORDER BY p.created_at DESC
    LIMIT ? OFFSET ?
";
$params = array_merge($where_params, [$limit, $offset]);
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$publikasi_list = $stmt->fetchAll();

// Get anggota for dropdown
$stmt_anggota = $pdo->query("SELECT id_anggota, nama FROM anggota_lab WHERE status = 'active' ORDER BY nama");
$anggota_options = $stmt_anggota->fetchAll();

// Get available years for filter
$years_stmt = $pdo->query("SELECT DISTINCT tahun FROM publikasi ORDER BY tahun DESC");
$available_years = $years_stmt->fetchAll(PDO::FETCH_COLUMN);

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Publikasi Ilmiah</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#publikasiModal" onclick="resetForm()">
        <i class="bi bi-plus-circle"></i> Tambah Publikasi
    </button>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php 
        if ($_GET['success'] == 'added') echo "Publikasi berhasil ditambahkan!";
        if ($_GET['success'] == 'updated') echo "Publikasi berhasil diupdate!";
        if ($_GET['success'] == 'deleted') echo "Publikasi berhasil dihapus!";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] == 'rejected'): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        Publikasi yang sudah di-reject tidak dapat diedit!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Search & Filter -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Cari Judul/Jurnal</label>
                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Ketik judul atau nama jurnal...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tahun</label>
                <select class="form-select" name="year">
                    <option value="">Semua Tahun</option>
                    <?php foreach ($available_years as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo $year_filter == $year ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Cari
                </button>
                <a href="publikasi.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            Total: <?php echo $total_items; ?> publikasi
            <?php if ($search || $year_filter): ?>
                <span class="badge bg-info">Filtered</span>
            <?php endif; ?>
        </h6>
        <span class="text-muted">Halaman <?php echo $page_num; ?> dari <?php echo $total_pages; ?></span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Cover</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Jurnal</th>
                        <th>Tahun</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($publikasi_list) > 0): ?>
                        <?php 
                        $no = $offset + 1;
                        foreach ($publikasi_list as $pub): 
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td>
                                <?php if ($pub['cover']): ?>
                                    <img src="../../uploads/publikasi/cover/<?php echo $pub['cover']; ?>" width="50" height="70" class="img-thumbnail">
                                <?php else: ?>
                                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="width:50px;height:70px;font-size:10px;">No Cover</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($pub['judul']); ?></strong>
                                <?php if ($pub['link_shinta']): ?>
                                    <br><small class="text-muted">link_shinta: <?php echo htmlspecialchars($pub['link_shinta']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($pub['penulis'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($pub['jurnal'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($pub['tahun']); ?></td>
                            <td>
                                <?php
                                $status_badge = '';
                                switch($pub['status']) {
                                    case 'active':
                                        $status_badge = '<span class="badge bg-success">Active</span>';
                                        break;
                                    case 'rejected':
                                        $status_badge = '<span class="badge bg-danger">Rejected</span>';
                                        break;
                                    default:
                                        $status_badge = '<span class="badge bg-secondary">' . htmlspecialchars($pub['status']) . '</span>';
                                }
                                echo $status_badge;
                                ?>
                            </td>
                            <td>
                                <?php if ($pub['status'] !== 'rejected'): ?>
                                    <button class="btn btn-sm btn-warning" onclick='editPublikasi(<?php echo json_encode($pub); ?>)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled title="Publikasi rejected tidak dapat diedit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                <?php endif; ?>
                                <a href="?delete=<?php echo $pub['id_publikasi']; ?>&page=<?php echo $page_num; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <?php if ($search || $year_filter): ?>
                                    Tidak ada publikasi yang sesuai dengan pencarian.
                                <?php else: ?>
                                    Belum ada publikasi. Klik tombol "Tambah Publikasi" untuk menambahkan.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($total_pages > 1): ?>
    <div class="card-footer bg-white">
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?php echo $page_num <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page_num - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $year_filter ? '&year=' . urlencode($year_filter) : ''; ?>">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                </li>
                
                <?php
                $start_page = max(1, $page_num - 2);
                $end_page = min($total_pages, $page_num + 2);
                
                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=1' . ($search ? '&search=' . urlencode($search) : '') . ($year_filter ? '&year=' . urlencode($year_filter) : '') . '">1</a></li>';
                    if ($start_page > 2) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $active = $i == $page_num ? 'active' : '';
                    echo "<li class='page-item $active'><a class='page-link' href='?page=$i" . ($search ? '&search=' . urlencode($search) : '') . ($year_filter ? '&year=' . urlencode($year_filter) : '') . "'>$i</a></li>";
                }
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    echo "<li class='page-item'><a class='page-link' href='?page=$total_pages" . ($search ? '&search=' . urlencode($search) : '') . ($year_filter ? '&year=' . urlencode($year_filter) : '') . "'>$total_pages</a></li>";
                }
                ?>
                
                <li class="page-item <?php echo $page_num >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page_num + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $year_filter ? '&year=' . urlencode($year_filter) : ''; ?>">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Form -->
<div class="modal fade" id="publikasiModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Publikasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_publikasi" id="id_publikasi">
                    
                    <div class="mb-3">
                        <label class="form-label">Judul Publikasi *</label>
                        <input type="text" class="form-control" name="judul" id="judul" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cover</label>
                            <input type="file" class="form-control" name="cover" accept="image/*">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">File PDF</label>
                            <input type="file" class="form-control" name="file_path" accept=".pdf">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Abstrak</label>
                        <textarea class="form-control" name="abstrak" id="abstrak" rows="4"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tahun *</label>
                            <input type="text" class="form-control" name="tahun" id="tahun" required placeholder="2024">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Jurnal</label>
                            <input type="text" class="form-control" name="jurnal" id="jurnal">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tanggal Publikasi</label>
                            <input type="date" class="form-control" name="tanggal_publikasi" id="tanggal_publikasi">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">link_shinta</label>
                        <input type="text" class="form-control" name="link_shinta" id="link_shinta" placeholder="10.xxxx/xxxxx">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Penulis (urut sesuai publikasi)</label>
                        <div id="penulisContainer">
                            <div class="input-group mb-2">
                                <select class="form-select" name="penulis[]">
                                    <option value="">-- Pilih Penulis --</option>
                                    <?php foreach ($anggota_options as $anggota): ?>
                                        <option value="<?php echo $anggota['id_anggota']; ?>">
                                            <?php echo htmlspecialchars($anggota['nama']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addPenulis()">
                            <i class="bi bi-plus"></i> Tambah Penulis
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const anggotaOptions = <?php echo json_encode($anggota_options); ?>;

function resetForm() {
    document.getElementById('modalTitle').textContent = 'Tambah Publikasi';
    document.querySelector('form').reset();
    document.getElementById('id_publikasi').value = '';
    document.getElementById('penulisContainer').innerHTML = `
        <div class="input-group mb-2">
            <select class="form-select" name="penulis[]">
                <option value="">-- Pilih Penulis --</option>
                ${anggotaOptions.map(a => `<option value="${a.id_anggota}">${a.nama}</option>`).join('')}
            </select>
            <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
}

function addPenulis() {
    const container = document.getElementById('penulisContainer');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <select class="form-select" name="penulis[]">
            <option value="">-- Pilih Penulis --</option>
            ${anggotaOptions.map(a => `<option value="${a.id_anggota}">${a.nama}</option>`).join('')}
        </select>
        <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">
            <i class="bi bi-trash"></i>
        </button>
    `;
    container.appendChild(div);
}

function editPublikasi(data) {
    // Cek apakah status rejected
    if (data.status === 'rejected') {
        alert('Publikasi yang sudah di-reject tidak dapat diedit!');
        return;
    }
    
    document.getElementById('modalTitle').textContent = 'Edit Publikasi';
    document.getElementById('id_publikasi').value = data.id_publikasi;
    document.getElementById('judul').value = data.judul;
    document.getElementById('abstrak').value = data.abstrak || '';
    document.getElementById('tahun').value = data.tahun;
    document.getElementById('jurnal').value = data.jurnal || '';
    document.getElementById('link_shinta').value = data.link_shinta || '';
    document.getElementById('tanggal_publikasi').value = data.tanggal_publikasi || '';
    
    new bootstrap.Modal(document.getElementById('publikasiModal')).show();
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