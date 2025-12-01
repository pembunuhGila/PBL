<?php
$required_role = "operator";
include "../../auth.php";
include "../../conn.php";

$page_title = "Dashboard Lab";
$current_page = "dashboard.php";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt_check = $pdo->prepare("SELECT id_user, status, jabatan FROM struktur_lab WHERE id_struktur = ?");
        $stmt_check->execute([$id]);
        $old_data = $stmt_check->fetch();
        
        if ($old_data && $old_data['id_user'] == $_SESSION['id_user'] && $old_data['status'] == 'pending') {
            $stmt = $pdo->prepare("DELETE FROM struktur_lab WHERE id_struktur = ?");
            $stmt->execute([$id]);
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['struktur_lab', $id, $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus struktur: ' . $old_data['jabatan']]);
            
            $success = "Struktur berhasil dihapus!";
        } else {
            $error = "Anda hanya bisa menghapus data pending milik Anda!";
        }
    } catch (PDOException $e) {
        $error = "Gagal menghapus: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_anggota = $_POST['id_anggota'];
    $jabatan = $_POST['jabatan'];
    $urutan = $_POST['urutan'];
    $status = 'pending'; // AUTO PENDING
    
    try {
        if (isset($_POST['id_struktur']) && !empty($_POST['id_struktur'])) {
            $id = $_POST['id_struktur'];
            
            $stmt_check = $pdo->prepare("SELECT id_user, status FROM struktur_lab WHERE id_struktur = ?");
            $stmt_check->execute([$id]);
            $data_owner = $stmt_check->fetch();
            
            if ($data_owner && $data_owner['id_user'] == $_SESSION['id_user'] && $data_owner['status'] == 'pending') {
                $stmt = $pdo->prepare("UPDATE struktur_lab SET id_anggota=?, jabatan=?, urutan=?, status=? WHERE id_struktur=?");
                $stmt->execute([$id_anggota, $jabatan, $urutan, $status, $id]);
                
                $success = "Struktur berhasil diupdate! Menunggu persetujuan admin.";
            } else {
                $error = "Anda hanya bisa edit data pending milik Anda!";
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO struktur_lab (id_anggota, jabatan, urutan, status, id_user) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id_anggota, $jabatan, $urutan, $status, $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['struktur_lab', $new_id, $_SESSION['id_user'], null, $status, 'Tambah struktur: ' . $jabatan]);
            
            $success = "Struktur berhasil ditambahkan! Menunggu persetujuan admin.";
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Operator hanya lihat data miliknya
$stmt = $pdo->prepare("
    SELECT s.*, a.nama, a.foto, a.email 
    FROM struktur_lab s
    JOIN anggota_lab a ON s.id_anggota = a.id_anggota
    WHERE s.id_user = ?
    ORDER BY s.urutan ASC, s.created_at DESC
");
$stmt->execute([$_SESSION['id_user']]);
$struktur_list = $stmt->fetchAll();

// Get anggota for dropdown (semua anggota active)
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
    <i class="bi bi-info-circle"></i> Semua struktur yang Anda tambahkan akan berstatus <span class="badge bg-warning">Pending</span> dan menunggu persetujuan admin.
</div>

<!-- Table View -->
<div class="card shadow">
    <div class="card-header">
        <h5 class="mb-0">Data Struktur</h5>
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
                            <?php if ($struktur['status'] == 'pending'): ?>
                                <span class="badge bg-warning">Pending</span>
                            <?php elseif ($struktur['status'] == 'active'): ?>
                                <span class="badge bg-success">Approved</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Rejected</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($struktur['status'] == 'pending' || $struktur['status'] == 'rejected'): ?>
                                <button class="btn btn-sm btn-warning" onclick='editStruktur(<?php echo json_encode($struktur); ?>)'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="?delete=<?php echo $struktur['id_struktur']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">Sudah disetujui</span>
                            <?php endif; ?>
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
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> Data akan berstatus <strong>Pending</strong> dan menunggu persetujuan admin
                    </div>
                    
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
    
    new bootstrap.Modal(document.getElementById('strukturModal')).show();
}
</script>

<?php include "footer.php"; ?>