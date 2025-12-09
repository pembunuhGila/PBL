<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "operator";
include "../auth.php";
include "../../conn.php";

$page_title = "Slider";
$current_page = "slider.php";

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Handle Delete
if (isset($_GET['delete'])) {
    try {
        $stmt_check = $pdo->prepare("SELECT id_user, status, judul FROM slider WHERE id_slider = ?");
        $stmt_check->execute([$_GET['delete']]);
        $old_data = $stmt_check->fetch();
        
        if ($old_data && $old_data['id_user'] == $_SESSION['id_user'] && $old_data['status'] == 'pending') {
            $stmt = $pdo->prepare("DELETE FROM slider WHERE id_slider = ?");
            $stmt->execute([$_GET['delete']]);
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['slider', $_GET['delete'], $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus slider: ' . $old_data['judul']]);
            
            header("Location: slider.php?success=deleted&page=$page");
            exit;
        } else {
            $error = "Anda hanya bisa menghapus data pending milik Anda!";
        }
    } catch (PDOException $e) {
        $error = "Gagal menghapus: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $urutan = $_POST['urutan'];
    $status = 'pending';
    
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
            
            $stmt_check = $pdo->prepare("SELECT id_user, status FROM slider WHERE id_slider = ?");
            $stmt_check->execute([$id]);
            $data_owner = $stmt_check->fetch();
            
            if ($data_owner && $data_owner['id_user'] == $_SESSION['id_user'] && ($data_owner['status'] == 'pending' || $data_owner['status'] == 'rejected')) {
                $status_lama = $data_owner['status'];
                
                if ($gambar) {
                    $stmt = $pdo->prepare("UPDATE slider SET judul=?, deskripsi=?, urutan=?, gambar=?, status=? WHERE id_slider=?");
                    $stmt->execute([$judul, $deskripsi, $urutan, $gambar, $status, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE slider SET judul=?, deskripsi=?, urutan=?, status=? WHERE id_slider=?");
                    $stmt->execute([$judul, $deskripsi, $urutan, $status, $id]);
                }
                
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['slider', $id, $_SESSION['id_user'], $status_lama, $status, 'Update slider: ' . $judul]);
                
                header("Location: slider.php?success=updated");
                exit;
            } else {
                $error = "Anda hanya bisa edit data pending/rejected milik Anda!";
            }
        } else {
            if (!$gambar) {
                $error = "Gambar wajib diupload!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO slider (judul, deskripsi, urutan, gambar, status, id_user) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$judul, $deskripsi, $urutan, $gambar, $status, $_SESSION['id_user']]);
                
                $new_id = $pdo->lastInsertId();
                
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['slider', $new_id, $_SESSION['id_user'], null, $status, 'Tambah slider: ' . $judul]);
                
                header("Location: slider.php?success=added");
                exit;
            }
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Get total count
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM slider WHERE id_user = ?");
$count_stmt->execute([$_SESSION['id_user']]);
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $limit);

// Get data with pagination
$stmt = $pdo->prepare("SELECT * FROM slider WHERE id_user = ? ORDER BY urutan ASC, tanggal_dibuat DESC LIMIT ? OFFSET ?");
$stmt->execute([$_SESSION['id_user'], $limit, $offset]);
$slider_list = $stmt->fetchAll();

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
        if ($_GET['success'] == 'added') echo "Slider berhasil ditambahkan! Menunggu persetujuan admin.";
        if ($_GET['success'] == 'updated') echo "Slider berhasil diupdate! Menunggu persetujuan admin.";
        if ($_GET['success'] == 'deleted') echo "Slider berhasil dihapus!";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Semua slider yang Anda tambahkan akan berstatus <span class="badge bg-warning">Pending</span> dan menunggu persetujuan admin.
</div>

<div class="card shadow">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Total: <?php echo $total_items; ?> slider</h6>
        <?php if ($total_pages > 1): ?>
            <span class="text-muted">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></span>
        <?php endif; ?>
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
                            <td><?php echo htmlspecialchars(substr($slider['deskripsi'] ?? '', 0, 60)); ?>...</td>
                            <td>
                                <?php if ($slider['status'] == 'pending'): ?>
                                    <span class="badge bg-warning">Pending</span>
                                <?php elseif ($slider['status'] == 'active'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($slider['status'] == 'pending' || $slider['status'] == 'rejected'): ?>
                                    <button class="btn btn-sm btn-warning" onclick='editSlider(<?php echo json_encode($slider); ?>)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="?delete=<?php echo $slider['id_slider']; ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">Sudah disetujui</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($total_pages > 1): ?>
    <div class="card-footer bg-white">
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                </li>
                
                <?php
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
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> Data akan berstatus <strong>Pending</strong> dan menunggu persetujuan admin
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Gambar *</label>
                        <input type="file" class="form-control" name="gambar" accept="image/*">
                        <small class="text-muted">Rekomendasi: 1920x800px atau rasio 16:9. Wajib upload saat tambah baru, opsional saat edit</small>
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
                    <button type="submit" class="btn btn-primary">Simpan & Ajukan</button>
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