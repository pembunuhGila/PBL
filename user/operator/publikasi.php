<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "operator";
include "../auth.php";
include "../../conn.php";

$page_title = "Publikasi";
$current_page = "publikasi.php";

// Handle Delete - operator hanya bisa hapus data miliknya yang masih pending
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt_check = $pdo->prepare("SELECT id_user, status, judul FROM publikasi WHERE id_publikasi = ?");
        $stmt_check->execute([$id]);
        $old_data = $stmt_check->fetch();
        
        if ($old_data && $old_data['id_user'] == $_SESSION['id_user'] && $old_data['status'] == 'pending') {
            $stmt = $pdo->prepare("DELETE FROM publikasi WHERE id_publikasi = ?");
            $stmt->execute([$id]);
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['publikasi', $id, $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus publikasi: ' . $old_data['judul']]);
            
            $success = "Publikasi berhasil dihapus!";
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
    $abstrak = $_POST['abstrak'];
    $tahun = $_POST['tahun'];
    $jurnal = $_POST['jurnal'];
    $doi = $_POST['doi'];
    $tanggal_publikasi = $_POST['tanggal_publikasi'];
    $status = 'pending'; // AUTO PENDING untuk operator
    $penulis_ids = $_POST['penulis'] ?? [];
    
    // Handle cover upload
    $cover = null;
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
        $target_dir = "../../uploads/publikasi/cover/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
        $cover = 'cover_' . time() . '.' . $file_extension;
        move_uploaded_file($_FILES['cover']['tmp_name'], $target_dir . $cover);
    }
    
    // Handle file upload
    $file_path = null;
    if (isset($_FILES['file_path']) && $_FILES['file_path']['error'] == 0) {
        $target_dir = "../../uploads/publikasi/files/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES['file_path']['name'], PATHINFO_EXTENSION);
        $file_path = 'pub_' . time() . '.' . $file_extension;
        move_uploaded_file($_FILES['file_path']['tmp_name'], $target_dir . $file_path);
    }
    
    try {
        $pdo->beginTransaction();
        
        if (isset($_POST['id_publikasi']) && !empty($_POST['id_publikasi'])) {
            $id = $_POST['id_publikasi'];
            
            // Cek kepemilikan dan status
            $stmt_check = $pdo->prepare("SELECT id_user, status FROM publikasi WHERE id_publikasi = ?");
            $stmt_check->execute([$id]);
            $data_owner = $stmt_check->fetch();
            
            if ($data_owner && $data_owner['id_user'] == $_SESSION['id_user'] && $data_owner['status'] == 'pending') {
                $status_lama = $data_owner['status'];
                
                $sql = "UPDATE publikasi SET judul=?, abstrak=?, tahun=?, jurnal=?, doi=?, tanggal_publikasi=?, status=?";
                $params = [$judul, $abstrak, $tahun, $jurnal, $doi, $tanggal_publikasi, $status];
                
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
                
                // Delete old penulis
                $pdo->prepare("DELETE FROM publikasi_anggota WHERE id_publikasi=?")->execute([$id]);
                
                $message = "Publikasi berhasil diupdate! Menunggu persetujuan admin.";
            } else {
                throw new Exception("Anda hanya bisa edit data pending milik Anda!");
            }
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO publikasi (judul, abstrak, tahun, jurnal, doi, tanggal_publikasi, cover, file_path, status, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$judul, $abstrak, $tahun, $jurnal, $doi, $tanggal_publikasi, $cover, $file_path, $status, $_SESSION['id_user']]);
            $id = $pdo->lastInsertId();
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['publikasi', $id, $_SESSION['id_user'], null, $status, 'Tambah publikasi: ' . $judul]);
            
            $message = "Publikasi berhasil ditambahkan! Menunggu persetujuan admin.";
        }
        
        // Insert penulis
        foreach ($penulis_ids as $index => $id_anggota) {
            $stmt = $pdo->prepare("INSERT INTO publikasi_anggota (id_publikasi, id_anggota, urutan_penulis) VALUES (?, ?, ?)");
            $stmt->execute([$id, $id_anggota, $index + 1]);
        }
        
        $pdo->commit();
        $success = $message;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Operator hanya bisa lihat data miliknya sendiri
$stmt = $pdo->prepare("
    SELECT p.*, 
           STRING_AGG(a.nama, ', ' ORDER BY pa.urutan_penulis) as penulis
    FROM publikasi p
    LEFT JOIN publikasi_anggota pa ON p.id_publikasi = pa.id_publikasi
    LEFT JOIN anggota_lab a ON pa.id_anggota = a.id_anggota
    WHERE p.id_user = ?
    GROUP BY p.id_publikasi
    ORDER BY p.created_at DESC
");
$stmt->execute([$_SESSION['id_user']]);
$publikasi_list = $stmt->fetchAll();

// Get anggota for dropdown (semua anggota, tidak hanya milik operator)
$stmt_anggota = $pdo->query("SELECT id_anggota, nama FROM anggota_lab WHERE status = 'active' ORDER BY nama");
$anggota_options = $stmt_anggota->fetchAll();

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
    <i class="bi bi-info-circle"></i> <strong>Info:</strong> Semua publikasi yang Anda tambahkan akan berstatus <span class="badge bg-warning">Pending</span> dan menunggu persetujuan admin.
</div>

<div class="card shadow">
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
                    <?php $no = 1; foreach ($publikasi_list as $pub): ?>
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
                            <?php if ($pub['doi']): ?>
                                <br><small class="text-muted">DOI: <?php echo htmlspecialchars($pub['doi']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($pub['penulis'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($pub['jurnal'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($pub['tahun']); ?></td>
                        <td>
                            <?php if ($pub['status'] == 'pending'): ?>
                                <span class="badge bg-warning">Pending</span>
                            <?php elseif ($pub['status'] == 'active'): ?>
                                <span class="badge bg-success">Approved</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Rejected</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($pub['status'] == 'pending' || $pub['status'] == 'rejected'): ?>
                                <button class="btn btn-sm btn-warning" onclick='editPublikasi(<?php echo json_encode($pub); ?>)'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="?delete=<?php echo $pub['id_publikasi']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
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
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> Data akan berstatus <strong>Pending</strong> dan menunggu persetujuan admin
                    </div>
                    
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
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tahun *</label>
                            <input type="text" class="form-control" name="tahun" id="tahun" required placeholder="2024">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nama Jurnal</label>
                            <input type="text" class="form-control" name="jurnal" id="jurnal">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">DOI</label>
                            <input type="text" class="form-control" name="doi" id="doi" placeholder="10.xxxx/xxxxx">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Publikasi</label>
                            <input type="date" class="form-control" name="tanggal_publikasi" id="tanggal_publikasi">
                        </div>
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
                    <button type="submit" class="btn btn-primary">Simpan & Ajukan</button>
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
    document.getElementById('modalTitle').textContent = 'Edit Publikasi';
    document.getElementById('id_publikasi').value = data.id_publikasi;
    document.getElementById('judul').value = data.judul;
    document.getElementById('abstrak').value = data.abstrak || '';
    document.getElementById('tahun').value = data.tahun;
    document.getElementById('jurnal').value = data.jurnal || '';
    document.getElementById('doi').value = data.doi || '';
    document.getElementById('tanggal_publikasi').value = data.tanggal_publikasi || '';
    
    new bootstrap.Modal(document.getElementById('publikasiModal')).show();
}
</script>

<?php include "footer.php"; ?>