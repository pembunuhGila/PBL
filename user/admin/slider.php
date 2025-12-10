<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "admin";
include "../auth.php";
include "../../conn.php";

$page_title = "Slider";
$current_page = "slider.php";

// Pagination setup
$limit = 10; // Items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Handle Approve/Reject Pending
if (isset($_GET['approve']) || isset($_GET['reject'])) {
    $id = isset($_GET['approve']) ? $_GET['approve'] : $_GET['reject'];
    $new_status = isset($_GET['approve']) ? 'active' : 'rejected';
    
    try {
        $stmt_old = $pdo->prepare("SELECT judul, status FROM slider WHERE id_slider = ?");
        $stmt_old->execute([$id]);
        $old_data = $stmt_old->fetch();
        
        $stmt = $pdo->prepare("UPDATE slider SET status = ? WHERE id_slider = ?");
        $stmt->execute([$new_status, $id]);
        
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $catatan = isset($_GET['approve']) ? 'DISETUJUI slider: ' . $old_data['judul'] : 'DITOLAK slider: ' . $old_data['judul'];
        $stmt_riwayat->execute(['slider', $id, $_SESSION['id_user'], $old_data['status'], $new_status, $catatan]);
        
        header("Location: slider.php?success=" . ($new_status == 'active' ? 'approved' : 'rejected') . "&page=" . $page);
        exit;
    } catch (PDOException $e) {
        $error = "Gagal: " . $e->getMessage();
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    try {
        $stmt_old = $pdo->prepare("SELECT judul, status FROM slider WHERE id_slider = ?");
        $stmt_old->execute([$_GET['delete']]);
        $old_data = $stmt_old->fetch();
        
        $stmt = $pdo->prepare("DELETE FROM slider WHERE id_slider = ?");
        $stmt->execute([$_GET['delete']]);
        
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_riwayat->execute(['slider', $_GET['delete'], $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus slider: ' . $old_data['judul']]);
        
        header("Location: slider.php?success=deleted&page=$page");
        exit;
    } catch (PDOException $e) {
        $error = "Gagal menghapus: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $urutan = $_POST['urutan'];
    $status = 'active';
    
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../../uploads/slider/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar = 'slider_' . time() . '.' . $file_extension;
        move_uploaded_file($_FILES['gambar']['tmp_name'], $target_dir . $gambar);
    }
    
    try {
        if (isset($_POST['id_slider']) && !empty($_POST['id_slider'])) {
            $id = $_POST['id_slider'];
            
            $stmt_old = $pdo->prepare("SELECT status FROM slider WHERE id_slider = ?");
            $stmt_old->execute([$id]);
            $old_data = $stmt_old->fetch();
            $status_lama = $old_data['status'];
            
            if ($gambar) {
                $stmt = $pdo->prepare("UPDATE slider SET judul=?, deskripsi=?, urutan=?, gambar=?, status=? WHERE id_slider=?");
                $stmt->execute([$judul, $deskripsi, $urutan, $gambar, $status, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE slider SET judul=?, deskripsi=?, urutan=?, status=? WHERE id_slider=?");
                $stmt->execute([$judul, $deskripsi, $urutan, $status, $id]);
            }
            
            if ($status_lama != $status) {
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['slider', $id, $_SESSION['id_user'], $status_lama, $status, 'Update slider: ' . $judul]);
            }
            
            header("Location: slider.php?success=updated");
            exit;
        } else {
            if (!$gambar) {
                $error = "Gambar wajib diupload!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO slider (judul, deskripsi, urutan, gambar, status, id_user) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$judul, $deskripsi, $urutan, $gambar, $status, $_SESSION['id_user']]);
                
                $new_id = $pdo->lastInsertId();
                
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['slider', $new_id, $_SESSION['id_user'], null, $status, 'Tambah slider: ' . $judul]);
                
                header("Location: slider.php?success=added");
                exit;
            }
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Get total count for pagination
$count_stmt = $pdo->query("SELECT COUNT(*) FROM slider");
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $limit);

// Get data with pagination
$stmt = $pdo->prepare("SELECT s.*, u.nama as nama_pembuat FROM slider s LEFT JOIN users u ON s.id_user = u.id_user ORDER BY s.urutan ASC, s.tanggal_dibuat DESC LIMIT ? OFFSET ?");
$stmt->execute([$limit, $offset]);
$slider_list = $stmt->fetchAll();

// Get pending count
$pending_count_query = "SELECT COUNT(*) FROM slider WHERE status = 'pending'";
$pending_count_stmt = $pdo->prepare($pending_count_query);
$pending_count_stmt->execute();
$pending_count = $pending_count_stmt->fetchColumn();

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Slider Homepage</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sliderModal" onclick="resetForm()">
        <i class="bi bi-plus-circle"></i> Tambah Slider
    </button>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php 
        if ($_GET['success'] == 'added') echo "✅ Slider berhasil ditambahkan!";
        if ($_GET['success'] == 'updated') echo "✅ Slider berhasil diupdate!";
        if ($_GET['success'] == 'deleted') echo "✅ Slider berhasil dihapus!";
        if ($_GET['success'] == 'approved') echo "✅ Pengajuan slider berhasil disetujui!";
        if ($_GET['success'] == 'rejected') echo "❌ Pengajuan slider berhasil ditolak!";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php if ($pending_count > 0): ?>
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="bi bi-exclamation-circle"></i> Ada <strong><?php echo $pending_count; ?> slider</strong> menunggu persetujuan Anda
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Total: <?php echo $total_items; ?> slider</h6>
        <span class="text-muted">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Gambar</th>
                        <th>Judul</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Pembuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($slider_list) > 0): ?>
                        <?php foreach ($slider_list as $slider): ?>
                        <tr>
                            <td><span class="badge bg-primary">#<?php echo $slider['urutan']; ?></span></td>
                            <td>
                                <?php if ($slider['gambar']): ?>
                                    <img src="../../uploads/slider/<?php echo $slider['gambar']; ?>" width="120" height="70" class="img-thumbnail" style="object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-secondary" style="width:120px;height:70px;"></div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($slider['judul']); ?></strong></td>
                            <td><?php echo htmlspecialchars(substr($slider['deskripsi'] ?? '', 0, 80)); ?>...</td>
                            <td>
                                <?php if ($slider['status'] == 'pending'): ?>
                                    <span class="badge bg-secondary">Pending</span>
                                <?php elseif ($slider['status'] == 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small><?php echo htmlspecialchars($slider['nama_pembuat'] ?? 'Admin'); ?></small>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick='editSlider(<?php echo json_encode($slider); ?>)'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                
                                <?php if ($slider['status'] == 'pending'): ?>
                                    <a href="?approve=<?php echo $slider['id_slider']; ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-success" onclick="return confirm('Setujui pengajuan ini?')">
                                        <i class="bi bi-check"></i> Acc
                                    </a>
                                    <a href="?reject=<?php echo $slider['id_slider']; ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tolak pengajuan ini?')">
                                        <i class="bi bi-x"></i> Reject
                                    </a>
                                <?php endif; ?>
                                
                                <a href="?delete=<?php echo $slider['id_slider']; ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="mt-3 text-muted">Belum ada slider</p>
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
                <!-- Previous Button -->
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                </li>
                
                <?php
                // Show page numbers with smart logic
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                    if ($start_page > 2) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $active = $i == $page ? 'active' : '';
                    echo "<li class='page-item $active'><a class='page-link' href='?page=$i'>$i</a></li>";
                }
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    echo "<li class='page-item'><a class='page-link' href='?page=$total_pages'>$total_pages</a></li>";
                }
                ?>
                
                <!-- Next Button -->
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="sliderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Slider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_slider" id="id_slider">
                    
                    <div class="mb-3">
                        <label class="form-label">Gambar *</label>
                        <input type="file" class="form-control" name="gambar" accept="image/*">
                        <small class="text-muted">Rekomendasi: 1920x800px atau rasio 16:9, Kosongkan jika edit dan tidak ingin ganti gambar</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Judul *</label>
                        <input type="text" class="form-control" name="judul" id="judul" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="deskripsi" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Urutan *</label>
                        <input type="number" class="form-control" name="urutan" id="urutan" required min="1" value="1">
                        <small class="text-muted">Urutan tampil slider (1 = pertama)</small>
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
function resetForm() {
    document.getElementById('modalTitle').textContent = 'Tambah Slider';
    document.querySelector('form').reset();
    document.getElementById('id_slider').value = '';
}

function editSlider(data) {
    document.getElementById('modalTitle').textContent = 'Edit Slider';
    document.getElementById('id_slider').value = data.id_slider;
    document.getElementById('judul').value = data.judul;
    document.getElementById('deskripsi').value = data.deskripsi || '';
    document.getElementById('urutan').value = data.urutan;
    new bootstrap.Modal(document.getElementById('sliderModal')).show();
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