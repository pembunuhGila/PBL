<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "operator";
include "../auth.php";
include "../../conn.php";

$page_title = "Struktur Lab";
$current_page = "struktur.php";

// Handle Delete - Hanya data pending milik sendiri
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
            // EDIT MODE - Bisa edit semua data, akan jadi pending
            $id = $_POST['id_struktur'];
            
            // Ambil data lama untuk riwayat
            $stmt_old = $pdo->prepare("SELECT status, jabatan FROM struktur_lab WHERE id_struktur = ?");
            $stmt_old->execute([$id]);
            $old_data = $stmt_old->fetch();
            
            // Update data dengan status pending
            $stmt = $pdo->prepare("UPDATE struktur_lab SET id_anggota=?, jabatan=?, urutan=?, status=?, id_user=? WHERE id_struktur=?");
            $stmt->execute([$id_anggota, $jabatan, $urutan, $status, $_SESSION['id_user'], $id]);
            
            // Catat ke riwayat
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['struktur_lab', $id, $_SESSION['id_user'], $old_data['status'], $status, 'Update struktur: ' . $jabatan]);
            
            $success = "Struktur berhasil diupdate! Menunggu persetujuan admin.";
        } else {
            // ADD MODE
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

// Pagination untuk data milik operator
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total records milik operator
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM struktur_lab WHERE id_user = ?");
$count_stmt->execute([$_SESSION['id_user']]);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Data milik operator dengan pagination
$stmt = $pdo->prepare("
    SELECT s.*, a.nama, a.foto, a.email 
    FROM struktur_lab s
    JOIN anggota_lab a ON s.id_anggota = a.id_anggota
    WHERE s.id_user = ?
    ORDER BY s.urutan ASC, s.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$_SESSION['id_user'], $limit, $offset]);
$struktur_list = $stmt->fetchAll();

// Pagination untuk semua data (bisa diedit)
$limit_all = 10;
$page_all = isset($_GET['page_all']) ? (int)$_GET['page_all'] : 1;
$offset_all = ($page_all - 1) * $limit_all;

// Get total all records
$count_stmt_all = $pdo->query("SELECT COUNT(*) FROM struktur_lab");
$total_records_all = $count_stmt_all->fetchColumn();
$total_pages_all = ceil($total_records_all / $limit_all);

// Semua data struktur dengan pagination (untuk diedit)
$stmt_all = $pdo->prepare("
    SELECT s.*, a.nama, a.foto, a.email 
    FROM struktur_lab s
    JOIN anggota_lab a ON s.id_anggota = a.id_anggota
    ORDER BY s.status ASC, s.urutan ASC, s.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt_all->execute([$limit_all, $offset_all]);
$all_struktur_list = $stmt_all->fetchAll();

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

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Anda dapat mengedit semua struktur. Semua perubahan akan berstatus <span class="badge bg-warning">Pending</span> dan menunggu persetujuan admin.
</div>

<!-- Organizational Chart View - Active Only -->
<div class="row mb-4">
    <?php 
    $stmt_chart = $pdo->query("
        SELECT s.*, a.nama, a.foto, a.email 
        FROM struktur_lab s
        JOIN anggota_lab a ON s.id_anggota = a.id_anggota
        WHERE s.status = 'active'
        ORDER BY s.urutan ASC
        LIMIT 12
    ");
    $struktur_chart = $stmt_chart->fetchAll();
    
    if (count($struktur_chart) > 0) {
        $current_urutan = 0;
        foreach ($struktur_chart as $struktur): 
            if ($current_urutan != $struktur['urutan']) {
                if ($current_urutan != 0) echo '</div><div class="row mb-4 justify-content-center">';
                $current_urutan = $struktur['urutan'];
            }
    ?>
    <div class="col-md-4 mb-3">
        <div class="card shadow text-center h-100">
            <div class="card-body">
                <?php if ($struktur['foto']): ?>
                    <img src="../../uploads/anggota/<?php echo $struktur['foto']; ?>" class="rounded-circle mb-3" width="100" height="100" style="object-fit: cover;">
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
    <?php 
        endforeach;
    } else {
        echo '<div class="col-12"><div class="alert alert-warning"><i class="bi bi-info-circle"></i> Belum ada struktur yang aktif</div></div>';
    }
    ?>
</div>

<!-- Table View for All Data (Bisa Edit) -->
<div class="card shadow">
    <div class="card-body">
        <div class="card shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Total Semua Data: <?php echo $total_records_all; ?> struktur</h6>
                    <small class="text-muted">Halaman <?php echo $page_all; ?> dari <?php echo $total_pages_all; ?></small>
                </div>
                
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> Anda dapat mengedit semua data di bawah ini. Setiap edit akan mengubah status menjadi <strong>Pending</strong> dan memerlukan persetujuan admin.
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
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (count($all_struktur_list) > 0) {
                                $no_all = $offset_all + 1;
                                foreach ($all_struktur_list as $struktur): 
                            ?>
                            <tr>
                                <td><?php echo $no_all++; ?></td>
                                <td>
                                    <span class="badge bg-secondary">#<?php echo $struktur['urutan']; ?></span>
                                    <?php if ($struktur['urutan'] == 1): ?>
                                        <i class="bi bi-star-fill text-warning" title="Ketua"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($struktur['foto']): ?>
                                        <img src="../../uploads/anggota/<?php echo $struktur['foto']; ?>" width="50" height="50" class="rounded-circle" style="object-fit: cover;">
                                    <?php else: ?>
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($struktur['nama']); ?>" width="50" height="50" class="rounded-circle">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($struktur['nama']); ?></td>
                                <td><?php echo htmlspecialchars($struktur['jabatan']); ?></td>
                                <td><small><?php echo htmlspecialchars($struktur['email'] ?? '-'); ?></small></td>
                                <td>
                                    <?php if ($struktur['status'] == 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php elseif ($struktur['status'] == 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick='editStruktur(<?php echo json_encode($struktur); ?>)' title="Edit (akan jadi Pending)">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                </td>
                            </tr>
                            <?php 
                                endforeach;
                            } else {
                            ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-3 mb-0">Belum ada data struktur</p>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination untuk All Data -->
                <?php if ($total_pages_all > 1): ?>
                <nav aria-label="Page navigation" class="mt-3">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page_all <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page_all=<?php echo $page_all - 1; ?>#all-data">
                                <i class="bi bi-chevron-left"></i> Previous
                            </a>
                        </li>
                        
                        <?php
                        $start_page_all = max(1, $page_all - 2);
                        $end_page_all = min($total_pages_all, $page_all + 2);
                        
                        if ($start_page_all > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page_all=1#all-data">1</a></li>
                            <?php if ($start_page_all > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start_page_all; $i <= $end_page_all; $i++): ?>
                            <li class="page-item <?php echo $i == $page_all ? 'active' : ''; ?>">
                                <a class="page-link" href="?page_all=<?php echo $i; ?>#all-data"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($end_page_all < $total_pages_all): ?>
                            <?php if ($end_page_all < $total_pages_all - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item"><a class="page-link" href="?page_all=<?php echo $total_pages_all; ?>#all-data"><?php echo $total_pages_all; ?></a></li>
                        <?php endif; ?>
                        
                        <li class="page-item <?php echo $page_all >= $total_pages_all ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page_all=<?php echo $page_all + 1; ?>#all-data">
                                Next <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
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
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Simpan & Ajukan
                    </button>
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

// Auto-switch to all-data tab if page_all parameter exists
window.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('page_all')) {
        const allDataTab = new bootstrap.Tab(document.getElementById('all-data-tab'));
        allDataTab.show();
    }
});
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