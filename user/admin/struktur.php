<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "admin";
include "../auth.php";
include "../../conn.php";

$page_title = "Struktur Lab";
$current_page = "struktur.php";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // Get data untuk riwayat
        $stmt_old = $pdo->prepare("SELECT jabatan, status FROM struktur_lab WHERE id_struktur = ?");
        $stmt_old->execute([$id]);
        $old_data = $stmt_old->fetch();
        
        $stmt = $pdo->prepare("DELETE FROM struktur_lab WHERE id_struktur = ?");
        $stmt->execute([$id]);
        
        // Catat riwayat
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_riwayat->execute(['struktur_lab', $id, $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus struktur: ' . $old_data['jabatan']]);
        
        $success = "Struktur berhasil dihapus!";
    } catch (PDOException $e) {
        $error = "Gagal menghapus: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_anggota = $_POST['id_anggota'];
    $jabatan = $_POST['jabatan'];
    $urutan = $_POST['urutan'];
    $status = $_POST['status'];
    
    try {
        if (isset($_POST['id_struktur']) && !empty($_POST['id_struktur'])) {
            // Update
            $id = $_POST['id_struktur'];
            
            // Get status lama
            $stmt_old = $pdo->prepare("SELECT status FROM struktur_lab WHERE id_struktur = ?");
            $stmt_old->execute([$id]);
            $old_data = $stmt_old->fetch();
            $status_lama = $old_data['status'];
            
            $stmt = $pdo->prepare("UPDATE struktur_lab SET id_anggota=?, jabatan=?, urutan=?, status=? WHERE id_struktur=?");
            $stmt->execute([$id_anggota, $jabatan, $urutan, $status, $id]);
            
            // Catat riwayat jika status berubah
            if ($status_lama != $status) {
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['struktur_lab', $id, $_SESSION['id_user'], $status_lama, $status, 'Update struktur: ' . $jabatan]);
            }
            
            $success = "Struktur berhasil diupdate!";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO struktur_lab (id_anggota, jabatan, urutan, status, id_user) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id_anggota, $jabatan, $urutan, $status, $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            // Catat riwayat
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['struktur_lab', $new_id, $_SESSION['id_user'], null, $status, 'Tambah struktur: ' . $jabatan]);
            
            $success = "Struktur berhasil ditambahkan!";
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Get all struktur with anggota info, ordered by urutan
$stmt = $pdo->query("
    SELECT s.*, a.nama, a.foto, a.email 
    FROM struktur_lab s
    JOIN anggota_lab a ON s.id_anggota = a.id_anggota
    ORDER BY s.urutan ASC, s.created_at DESC
");
$struktur_list = $stmt->fetchAll();

// Get anggota for dropdown
$stmt_anggota = $pdo->query("SELECT id_anggota, nama, foto FROM anggota_lab WHERE status = 'active' ORDER BY nama");
$anggota_options = $stmt_anggota->fetchAll();

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Struktur Organisasi Lab</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#strukturModal" onclick="resetForm()">
        <i class="bi bi-plus-circle"></i> Tambah Struktur
    </button>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Organizational Chart View -->
<div class="row mb-4">
    <?php 
    $current_urutan = 0;
    foreach ($struktur_list as $struktur): 
        if ($struktur['status'] != 'active') continue;
        
        // New row for each urutan level
        if ($current_urutan != $struktur['urutan']) {
            if ($current_urutan != 0) echo '</div><div class="row mb-4 justify-content-center">';
            $current_urutan = $struktur['urutan'];
        }
    ?>
    <div class="col-md-4 mb-3">
        <div class="card shadow text-center">
            <div class="card-body">
                <?php if ($struktur['foto']): ?>
                    <img src="../../uploads/anggota/<?php echo $struktur['foto']; ?>" class="rounded-circle mb-3" width="100" height="100">
                <?php else: ?>
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($struktur['nama']); ?>&size=100" class="rounded-circle mb-3">
                <?php endif; ?>
                <h5 class="card-title mb-1"><?php echo htmlspecialchars($struktur['nama']); ?></h5>
                <p class="text-muted mb-2"><?php echo htmlspecialchars($struktur['jabatan']); ?></p>
                <small class="text-muted"><?php echo htmlspecialchars($struktur['email']); ?></small>
                <?php if ($struktur['urutan'] == 1): ?>
                    <div class="mt-2">
                        <span class="badge bg-primary">Ketua/Pimpinan</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Table View for Admin -->
<div class="card shadow">
    <div class="card-header">
        <h5 class="mb-0">Data Struktur (Admin View)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($struktur_list as $struktur): ?>
                    <tr>
                        <td>
                            <span class="badge bg-secondary">#<?php echo $struktur['urutan']; ?></span>
                            <?php if ($struktur['urutan'] == 1): ?>
                                <i class="bi bi-star-fill text-warning" title="Ketua"></i>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($struktur['foto']): ?>
                                <img src="../../uploads/anggota/<?php echo $struktur['foto']; ?>" width="50" height="50" class="rounded-circle">
                            <?php else: ?>
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($struktur['nama']); ?>" width="50" height="50" class="rounded-circle">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($struktur['nama']); ?></td>
                        <td><?php echo htmlspecialchars($struktur['jabatan']); ?></td>
                        <td>
                            <?php 
                            $badge_class = $struktur['status'] == 'active' ? 'bg-success' : ($struktur['status'] == 'pending' ? 'bg-warning' : 'bg-danger');
                            ?>
                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($struktur['status']); ?></span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick='editStruktur(<?php echo json_encode($struktur); ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="?delete=<?php echo $struktur['id_struktur']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
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

<!-- Modal Form -->
<div class="modal fade" id="strukturModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Struktur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_struktur" id="id_struktur">
                    
                    <div class="mb-3">
                        <label class="form-label">Anggota *</label>
                        <select class="form-select" name="id_anggota" id="id_anggota" required>
                            <option value="">-- Pilih Anggota --</option>
                            <?php foreach ($anggota_options as $anggota): ?>
                                <option value="<?php echo $anggota['id_anggota']; ?>">
                                    <?php echo htmlspecialchars($anggota['nama']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Jabatan *</label>
                        <input type="text" class="form-control" name="jabatan" id="jabatan" required placeholder="Contoh: Ketua Lab, Sekretaris, dll">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Urutan *</label>
                        <input type="number" class="form-control" name="urutan" id="urutan" required min="1" value="1">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Urutan 1 = Ketua/Pimpinan (ditampilkan paling atas)
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="status" required>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                        </select>
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
    document.getElementById('modalTitle').textContent = 'Tambah Struktur';
    document.querySelector('form').reset();
    document.getElementById('id_struktur').value = '';
}

function editStruktur(data) {
    document.getElementById('modalTitle').textContent = 'Edit Struktur';
    document.getElementById('id_struktur').value = data.id_struktur;
    document.getElementById('id_anggota').value = data.id_anggota;
    document.getElementById('jabatan').value = data.jabatan;
    document.getElementById('urutan').value = data.urutan;
    document.getElementById('status').value = data.status;
    
    new bootstrap.Modal(document.getElementById('strukturModal')).show();
}
</script>

<style>
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-5px);
}
</style>

<?php include "footer.php"; ?>