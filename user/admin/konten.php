<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "admin";
include "../auth.php";
include "../../conn.php";

$page_title = "Konten";
$current_page = "konten.php";

// Pagination setup
$limit = 10;
$page_num = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page_num - 1) * $limit;

// Handle Delete
if (isset($_GET['delete'])) {
    try {
        $stmt_old = $pdo->prepare("SELECT judul, status FROM konten WHERE id_konten = ?");
        $stmt_old->execute([$_GET['delete']]);
        $old_data = $stmt_old->fetch();
        
        $stmt = $pdo->prepare("DELETE FROM konten WHERE id_konten = ?");
        $stmt->execute([$_GET['delete']]);
        
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_riwayat->execute(['konten', $_GET['delete'], $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus konten: ' . $old_data['judul']]);
        
        header("Location: konten.php?success=deleted&page=" . $page_num);
        exit;
    } catch (PDOException $e) {
        $error = "Gagal menghapus: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['search'])) {
    $kategori_konten = $_POST['kategori_konten'];
    $judul = $_POST['judul'];
    $slug = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9 ]/', '', $judul)));
    $isi = $_POST['isi'];
    $status = 'active';
    
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../../uploads/konten/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar = 'konten_' . time() . '.' . $file_extension;
        move_uploaded_file($_FILES['gambar']['tmp_name'], $target_dir . $gambar);
    }
    
    try {
        if (isset($_POST['id_konten']) && !empty($_POST['id_konten'])) {
            $id = $_POST['id_konten'];
            
            $stmt_old = $pdo->prepare("SELECT status FROM konten WHERE id_konten = ?");
            $stmt_old->execute([$id]);
            $old_data = $stmt_old->fetch();
            $status_lama = $old_data['status'];
            
            $stmt = $pdo->prepare("SELECT * FROM sp_update_konten(?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $kategori_konten, $judul, $slug, $isi, $gambar, $status, $_SESSION['id_user']]);
            $result = $stmt->fetch();
            $message = $result['p_message'];
            
            if (strpos($message, 'Success') !== false) {
                if ($status_lama != $status) {
                    $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_riwayat->execute(['konten', $id, $_SESSION['id_user'], $status_lama, $status, 'Update konten: ' . $judul]);
                }
                header("Location: konten.php?success=updated&page=" . $page_num);
                exit;
            } else {
                $error = $message;
            }
        } else {
            $stmt = $pdo->prepare("SELECT * FROM sp_insert_konten(?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$kategori_konten, $judul, $slug, $isi, $gambar, $status, $_SESSION['id_user'], 'admin']);
            $result = $stmt->fetch();
            $new_id = $result['p_id_konten'];
            $message = $result['p_message'];
            
            if ($new_id > 0) {
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['konten', $new_id, $_SESSION['id_user'], null, $status, 'Tambah konten: ' . $judul]);
                header("Location: konten.php?success=added");
                exit;
            } else {
                $error = $message;
            }
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Search functionality
$search = $_GET['search'] ?? '';
$kategori_filter = $_GET['kategori'] ?? '';

// Build WHERE conditions
$where_clauses = ["id_user = ?"];
$params = [$_SESSION['id_user']];

if ($search) {
    $where_clauses[] = "(judul ILIKE ? OR isi ILIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($kategori_filter) {
    $where_clauses[] = "kategori_konten = ?";
    $params[] = $kategori_filter;
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

// Get total count
$count_query = "SELECT COUNT(*) FROM konten $where_sql";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $limit);

// Get data with pagination
$query = "SELECT * FROM konten $where_sql ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params_with_limit = array_merge($params, [$limit, $offset]);
$stmt = $pdo->prepare($query);
$stmt->execute($params_with_limit);
$konten_list = $stmt->fetchAll();

$kategori_list = ['Berita', 'Agenda', 'Pengumuman'];

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manajemen Konten</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kontenModal" onclick="resetForm()">
        <i class="bi bi-plus-circle"></i> Tambah Konten
    </button>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php 
        if ($_GET['success'] == 'added') echo "Konten berhasil ditambahkan!";
        if ($_GET['success'] == 'updated') echo "Konten berhasil diupdate!";
        if ($_GET['success'] == 'deleted') echo "Konten berhasil dihapus!";
        ?>
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
                <label class="form-label"><i class="bi bi-search"></i> Cari Konten</label>
                <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan judul atau isi..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label"><i class="bi bi-funnel"></i> Filter Kategori</label>
                <select class="form-select" name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategori_list as $kat): ?>
                        <option value="<?php echo $kat; ?>" <?php echo $kategori_filter === $kat ? 'selected' : ''; ?>>
                            <?php echo $kat; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Cari
                </button>
                <a href="konten.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<?php if ($search || $kategori_filter): ?>
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> 
    Menampilkan <?php echo count($konten_list); ?> hasil
    <?php if ($search): ?>
        untuk pencarian "<strong><?php echo htmlspecialchars($search); ?></strong>"
    <?php endif; ?>
    <?php if ($kategori_filter): ?>
        dengan kategori <strong><?php echo $kategori_filter; ?></strong>
    <?php endif; ?>
    <a href="konten.php" class="alert-link ms-2">Reset filter</a>
</div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            Total: <?php echo $total_items; ?> konten
            <?php if ($search || $kategori_filter): ?>
                <span class="badge bg-info">Filtered</span>
            <?php endif; ?>
        </h6>
        <span class="text-muted">Halaman <?php echo $page_num; ?> dari <?php echo max(1, $total_pages); ?></span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Gambar</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (count($konten_list) > 0) {
                        $no = $offset + 1; 
                        foreach ($konten_list as $kon): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <?php if ($kon['gambar']): ?>
                                <img src="../../uploads/konten/<?php echo $kon['gambar']; ?>" width="60" height="40" class="img-thumbnail">
                            <?php else: ?>
                                <div class="bg-secondary" style="width:60px;height:40px;"></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($kon['judul']); ?></strong>
                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($kon['isi'] ?? '', 0, 60)); ?>...</small>
                        </td>
                        <td><span class="badge bg-info"><?php echo htmlspecialchars($kon['kategori_konten'] ?? '-'); ?></span></td>
                        <td><?php echo date('d M Y', strtotime($kon['tanggal_posting'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick='editKonten(<?php echo htmlspecialchars(json_encode($kon)); ?>)'>
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <a href="?delete=<?php echo $kon['id_konten']; ?>&page=<?php echo $page_num; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $kategori_filter ? '&kategori=' . urlencode($kategori_filter) : ''; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">
                                <i class="bi bi-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endforeach;
                    } else {
                    ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-3 text-muted">
                                <?php if ($search || $kategori_filter): ?>
                                    Tidak ada konten yang sesuai dengan pencarian
                                <?php else: ?>
                                    Belum ada konten
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
                <li class="page-item <?php echo $page_num <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page_num - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $kategori_filter ? '&kategori=' . urlencode($kategori_filter) : ''; ?>">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                </li>
                
                <?php
                $start_page = max(1, $page_num - 2);
                $end_page = min($total_pages, $page_num + 2);
                
                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=1' . ($search ? '&search=' . urlencode($search) : '') . ($kategori_filter ? '&kategori=' . urlencode($kategori_filter) : '') . '">1</a></li>';
                    if ($start_page > 2) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $active = $i == $page_num ? 'active' : '';
                    echo "<li class='page-item $active'><a class='page-link' href='?page=$i" . ($search ? '&search=' . urlencode($search) : '') . ($kategori_filter ? '&kategori=' . urlencode($kategori_filter) : '') . "'>$i</a></li>";
                }
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    echo "<li class='page-item'><a class='page-link' href='?page=$total_pages" . ($search ? '&search=' . urlencode($search) : '') . ($kategori_filter ? '&kategori=' . urlencode($kategori_filter) : '') . "'>$total_pages</a></li>";
                }
                ?>
                
                <li class="page-item <?php echo $page_num >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page_num + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $kategori_filter ? '&kategori=' . urlencode($kategori_filter) : ''; ?>">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="kontenModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Konten</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_konten" id="id_konten">
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Data akan langsung aktif setelah disimpan
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Judul *</label>
                            <input type="text" class="form-control" name="judul" id="judul" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kategori *</label>
                            <select class="form-select" name="kategori_konten" id="kategori_konten" required>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Berita">Berita</option>
                                <option value="Agenda">Agenda</option>
                                <option value="Pengumuman">Pengumuman</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Gambar Cover</label>
                        <input type="file" class="form-control" name="gambar" accept="image/*">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Isi Konten *</label>
                        <textarea class="form-control" name="isi" id="isi" rows="10" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan & Aktifkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// SEMUA JAVASCRIPT DIGABUNG DI SINI
function resetForm() {
    document.getElementById('modalTitle').textContent = 'Tambah Konten';
    document.querySelector('form').reset();
    document.getElementById('id_konten').value = '';
}

function editKonten(data) {
    document.getElementById('modalTitle').textContent = 'Edit Konten';
    document.getElementById('id_konten').value = data.id_konten;
    document.getElementById('judul').value = data.judul;
    document.getElementById('kategori_konten').value = data.kategori_konten || '';
    document.getElementById('isi').value = data.isi || '';
    new bootstrap.Modal(document.getElementById('kontenModal')).show();
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