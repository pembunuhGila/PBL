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

// Handle Delete
if (isset($_GET['delete'])) {
    try {
        // Get data untuk riwayat
        $stmt_old = $pdo->prepare("SELECT judul, status FROM konten WHERE id_konten = ?");
        $stmt_old->execute([$_GET['delete']]);
        $old_data = $stmt_old->fetch();
        
        $stmt = $pdo->prepare("DELETE FROM konten WHERE id_konten = ?");
        $stmt->execute([$_GET['delete']]);
        
        // Catat riwayat
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_riwayat->execute(['konten', $_GET['delete'], $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus konten: ' . $old_data['judul']]);
        
        $success = "Konten berhasil dihapus!";
    } catch (PDOException $e) {
        $error = "Gagal menghapus: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_kategori = $_POST['id_kategori'];
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
            
            // Get status lama
            $stmt_old = $pdo->prepare("SELECT status FROM konten WHERE id_konten = ?");
            $stmt_old->execute([$id]);
            $old_data = $stmt_old->fetch();
            $status_lama = $old_data['status'];
            
            if ($gambar) {
                $stmt = $pdo->prepare("UPDATE konten SET id_kategori=?, judul=?, slug=?, isi=?, gambar=?, status=? WHERE id_konten=?");
                $stmt->execute([$id_kategori, $judul, $slug, $isi, $gambar, $status, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE konten SET id_kategori=?, judul=?, slug=?, isi=?, status=? WHERE id_konten=?");
                $stmt->execute([$id_kategori, $judul, $slug, $isi, $status, $id]);
            }
            
            // Catat riwayat jika status berubah
            if ($status_lama != $status) {
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['konten', $id, $_SESSION['id_user'], $status_lama, $status, 'Update konten: ' . $judul]);
            }
            
            $success = "Konten berhasil diupdate!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO konten (id_kategori, judul, slug, isi, gambar, status, id_user) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id_kategori, $judul, $slug, $isi, $gambar, $status, $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            // Catat riwayat
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['konten', $new_id, $_SESSION['id_user'], null, $status, 'Tambah konten: ' . $judul]);
            
            $success = "Konten berhasil ditambahkan!";
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

$stmt = $pdo->query("
    SELECT k.*, kat.nama as kategori_nama 
    FROM konten k 
    LEFT JOIN kategori kat ON k.id_kategori = kat.id_kategori 
    ORDER BY k.tanggal_posting DESC
");
$konten_list = $stmt->fetchAll();

$stmt_kat = $pdo->query("SELECT * FROM kategori ORDER BY nama");
$kategori_list = $stmt_kat->fetchAll();

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
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($konten_list as $kon): ?>
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
                        <td><span class="badge bg-info"><?php echo htmlspecialchars($kon['kategori_nama'] ?? '-'); ?></span></td>
                        <td><?php echo date('d M Y', strtotime($kon['tanggal_posting'])); ?></td>
                        <td>
                            <span class="badge <?php echo $kon['status']=='active' ? 'bg-success' : ($kon['status']=='pending' ? 'bg-warning' : 'bg-danger'); ?>">
                                <?php echo ucfirst($kon['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick='editKonten(<?php echo json_encode($kon); ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="?delete=<?php echo $kon['id_konten']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
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
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Judul *</label>
                            <input type="text" class="form-control" name="judul" id="judul" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kategori *</label>
                            <select class="form-select" name="id_kategori" id="id_kategori" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach ($kategori_list as $kat): ?>
                                    <option value="<?php echo $kat['id_kategori']; ?>"><?php echo htmlspecialchars($kat['nama']); ?></option>
                                <?php endforeach; ?>
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
                    <button type="submit" class="btn btn-primary">Simpan</button>
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
    document.getElementById('id_kategori').value = data.id_kategori || '';
    document.getElementById('isi').value = data.isi || '';
    document.getElementById('status').value = data.status;
    new bootstrap.Modal(document.getElementById('kontenModal')).show();
}
</script>

<?php include "footer.php"; ?>