<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "operator";
include "../auth.php";
include "../../conn.php";

$page_title = "Konten";
$current_page = "konten.php";

// Handle Delete
if (isset($_GET['delete'])) {
    try {
        $stmt_check = $pdo->prepare("SELECT id_user, status, judul FROM konten WHERE id_konten = ?");
        $stmt_check->execute([$_GET['delete']]);
        $old_data = $stmt_check->fetch();
        
        if ($old_data && $old_data['id_user'] == $_SESSION['id_user'] && $old_data['status'] == 'pending') {
            $stmt = $pdo->prepare("DELETE FROM konten WHERE id_konten = ?");
            $stmt->execute([$_GET['delete']]);
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['konten', $_GET['delete'], $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus konten: ' . $old_data['judul']]);
            
            $success = "Konten berhasil dihapus!";
        } else {
            $error = "Anda hanya bisa menghapus data pending milik Anda!";
        }
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
    $status = 'pending';
    
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
            
            $stmt_check = $pdo->prepare("SELECT id_user, status FROM konten WHERE id_konten = ?");
            $stmt_check->execute([$id]);
            $data_owner = $stmt_check->fetch();
            
            if ($data_owner && $data_owner['id_user'] == $_SESSION['id_user'] && $data_owner['status'] == 'pending') {
                $stmt = $pdo->prepare("SELECT * FROM sp_update_konten(?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id, $kategori_konten, $judul, $slug, $isi, $gambar, $status, $_SESSION['id_user']]);
                $result = $stmt->fetch();
                $message = $result['p_message'];
                
                if (strpos($message, 'Success') !== false) {
                    $success = "Konten berhasil diupdate! Menunggu persetujuan admin.";
                } else {
                    $error = $message;
                }
            } else {
                $error = "Anda hanya bisa edit data pending milik Anda!";
            }
        } else {
            $stmt = $pdo->prepare("SELECT * FROM sp_insert_konten(?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$kategori_konten, $judul, $slug, $isi, $gambar, $status, $_SESSION['id_user'], 'operator']);
            $result = $stmt->fetch();
            $new_id = $result['p_id_konten'];
            $message = $result['p_message'];
            
            if ($new_id > 0) {
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['konten', $new_id, $_SESSION['id_user'], null, $status, 'Tambah konten: ' . $judul]);
                $success = "Konten berhasil ditambahkan! Menunggu persetujuan admin.";
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
$status_filter = $_GET['status_filter'] ?? '';

// Get konten milik operator dengan filter
try {
    $stmt = $pdo->prepare("SELECT * FROM sp_get_konten(?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['id_user'], 'operator', null, null, 200]);
    $all_konten = $stmt->fetchAll();
    
    // Filter di PHP
    $konten_list = array_filter($all_konten, function($kon) use ($search, $kategori_filter, $status_filter) {
        $match_search = empty($search) || 
            stripos($kon['judul'], $search) !== false || 
            stripos($kon['isi'], $search) !== false;
        
        $match_kategori = empty($kategori_filter) || 
            $kon['kategori_konten'] === $kategori_filter;
            
        $match_status = empty($status_filter) || 
            $kon['status'] === $status_filter;
        
        return $match_search && $match_kategori && $match_status;
    });
} catch (PDOException $e) {
    $error = "Gagal mengambil data: " . $e->getMessage();
    $konten_list = [];
}

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

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show"><?php echo $success; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Semua konten yang Anda tambahkan akan berstatus <span class="badge bg-warning">Pending</span> dan menunggu persetujuan admin.
</div>

<!-- Search & Filter -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label"><i class="bi bi-search"></i> Cari Konten</label>
                <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan judul atau isi..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label"><i class="bi bi-funnel"></i> Kategori</label>
                <select class="form-select" name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategori_list as $kat): ?>
                        <option value="<?php echo $kat; ?>" <?php echo $kategori_filter === $kat ? 'selected' : ''; ?>>
                            <?php echo $kat; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label"><i class="bi bi-flag"></i> Status</label>
                <select class="form-select" name="status_filter">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
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

<?php if ($search || $kategori_filter || $status_filter): ?>
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> 
    Menampilkan <?php echo count($konten_list); ?> hasil
    <?php if ($search): ?>
        untuk pencarian "<strong><?php echo htmlspecialchars($search); ?></strong>"
    <?php endif; ?>
    <?php if ($kategori_filter): ?>
        dengan kategori <strong><?php echo $kategori_filter; ?></strong>
    <?php endif; ?>
    <?php if ($status_filter): ?>
        dengan status <strong><?php echo $status_filter; ?></strong>
    <?php endif; ?>
    <a href="konten.php" class="alert-link ms-2">Reset filter</a>
</div>
<?php endif; ?>

<div class="card shadow">
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
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (count($konten_list) > 0) {
                        $no = 1; 
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
                            <?php if ($kon['status'] == 'pending'): ?>
                                <span class="badge bg-warning">Pending</span>
                            <?php elseif ($kon['status'] == 'active'): ?>
                                <span class="badge bg-success">Approved</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Rejected</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($kon['status'] == 'pending' || $kon['status'] == 'rejected'): ?>
                                <button class="btn btn-sm btn-warning" onclick='editKonten(<?php echo json_encode($kon); ?>)'>
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <a href="?delete=<?php echo $kon['id_konten']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">Disetujui</span>
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
                                <?php if ($search || $kategori_filter || $status_filter): ?>
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
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> Data akan berstatus <strong>Pending</strong> dan menunggu persetujuan admin
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
                    <button type="submit" class="btn btn-primary">Simpan & Ajukan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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

<?php include "footer.php"; ?>