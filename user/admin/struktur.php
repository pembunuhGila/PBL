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
        $stmt_old = $pdo->prepare("SELECT jabatan, status FROM struktur_lab WHERE id_struktur = ?");
        $stmt_old->execute([$id]);
        $old_data = $stmt_old->fetch();
        
        $stmt = $pdo->prepare("DELETE FROM struktur_lab WHERE id_struktur = ?");
        $stmt->execute([$id]);
        
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
    $status = 'active';
    
    try {
        if (isset($_POST['id_struktur']) && !empty($_POST['id_struktur'])) {
            $id = $_POST['id_struktur'];
            
            $stmt_old = $pdo->prepare("SELECT status FROM struktur_lab WHERE id_struktur = ?");
            $stmt_old->execute([$id]);
            $old_data = $stmt_old->fetch();
            $status_lama = $old_data['status'];
            
            $stmt = $pdo->prepare("UPDATE struktur_lab SET id_anggota=?, jabatan=?, urutan=?, status=? WHERE id_struktur=?");
            $stmt->execute([$id_anggota, $jabatan, $urutan, $status, $id]);
            
            if ($status_lama != $status) {
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['struktur_lab', $id, $_SESSION['id_user'], $status_lama, $status, 'Update struktur: ' . $jabatan]);
            }
            
            $success = "Struktur berhasil diupdate!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO struktur_lab (id_anggota, jabatan, urutan, status, id_user) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id_anggota, $jabatan, $urutan, $status, $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['struktur_lab', $new_id, $_SESSION['id_user'], null, $status, 'Tambah struktur: ' . $jabatan]);
            
            $success = "Struktur berhasil ditambahkan dan langsung aktif!";
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total records
$count_stmt = $pdo->query("SELECT COUNT(*) FROM struktur_lab");
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Get struktur dengan pagination
$stmt = $pdo->prepare("
    SELECT s.*, a.nama, a.foto, a.email 
    FROM struktur_lab s
    JOIN anggota_lab a ON s.id_anggota = a.id_anggota
    ORDER BY s.urutan ASC, s.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$limit, $offset]);
$struktur_list = $stmt->fetchAll();

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

<!-- Table View for Admin -->
<div class="card shadow">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Total: <?php echo $total_records; ?> struktur</h6>
            <small class="text-muted">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></small>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Urutan</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Email</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = $offset + 1;
                    foreach ($struktur_list as $struktur): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
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
                        <td><small><?php echo htmlspecialchars($struktur['email'] ?? '-'); ?></small></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick='editStruktur(<?php echo json_encode($struktur); ?>)'>
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <a href="?delete=<?php echo $struktur['id_struktur']; ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="bi bi-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                </li>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=1">1</a></li>
                    <?php if ($start_page > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a></li>
                <?php endif; ?>
                
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
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
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Data akan langsung aktif setelah disimpan
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
                    <button type="submit" class="btn btn-primary">Simpan & Aktifkan</button>
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

<style>
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.15) !important;
}
</style>

<?php include "footer.php"; ?>