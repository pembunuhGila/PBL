<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "operator";
include "../auth.php";
include "../../conn.php";

$page_title = "Anggota Lab";
$current_page = "anggota.php";

// Handle Delete - operator hanya bisa hapus data miliknya
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // Cek kepemilikan data
        $stmt_check = $pdo->prepare("SELECT id_user FROM anggota_lab WHERE id_anggota = ?");
        $stmt_check->execute([$id]);
        $data_owner = $stmt_check->fetch();
        
        if ($data_owner && $data_owner['id_user'] == $_SESSION['id_user']) {
            $stmt_old = $pdo->prepare("SELECT nama, status FROM anggota_lab WHERE id_anggota = ?");
            $stmt_old->execute([$id]);
            $old_data = $stmt_old->fetch();
            
            $stmt = $pdo->prepare("DELETE FROM anggota_lab WHERE id_anggota = ?");
            $stmt->execute([$id]);
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['anggota_lab', $id, $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus anggota: ' . $old_data['nama']]);
            
            $success = "Anggota berhasil dihapus!";
        } else {
            $error = "Anda tidak memiliki akses untuk menghapus data ini!";
        }
    } catch (PDOException $e) {
        $error = "Gagal menghapus: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $nip = $_POST['nip'];
    $email = $_POST['email'];
    $kontak = $_POST['kontak'];
    $biodata_teks = $_POST['biodata_teks'];
    $pendidikan = $_POST['pendidikan'];
    $pendidikan_terakhir = $_POST['pendidikan_terakhir'];
    $bidang_keahlian = $_POST['bidang_keahlian'];
    $tanggal_bergabung = $_POST['tanggal_bergabung'];
    $ruangan = $_POST['ruangan'];
    
    // AUTO PENDING untuk operator
    $status = 'pending';
    
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "../../uploads/anggota/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = 'anggota_' . time() . '.' . $file_extension;
        move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $foto);
    }
    
    try {
        if (isset($_POST['id_anggota']) && !empty($_POST['id_anggota'])) {
            $id = $_POST['id_anggota'];
            
            // Cek kepemilikan data
            $stmt_check = $pdo->prepare("SELECT id_user, status FROM anggota_lab WHERE id_anggota = ?");
            $stmt_check->execute([$id]);
            $data_owner = $stmt_check->fetch();
            
            if ($data_owner && $data_owner['id_user'] == $_SESSION['id_user']) {
                $status_lama = $data_owner['status'];
                
                if ($foto) {
                    $stmt = $pdo->prepare("UPDATE anggota_lab SET nama=?, nip=?, email=?, kontak=?, biodata_teks=?, pendidikan=?, pendidikan_terakhir=?, bidang_keahlian=?, tanggal_bergabung=?, ruangan=?, foto=?, status=? WHERE id_anggota=?");
                    $stmt->execute([$nama, $nip, $email, $kontak, $biodata_teks, $pendidikan, $pendidikan_terakhir, $bidang_keahlian, $tanggal_bergabung, $ruangan, $foto, $status, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE anggota_lab SET nama=?, nip=?, email=?, kontak=?, biodata_teks=?, pendidikan=?, pendidikan_terakhir=?, bidang_keahlian=?, tanggal_bergabung=?, ruangan=?, status=? WHERE id_anggota=?");
                    $stmt->execute([$nama, $nip, $email, $kontak, $biodata_teks, $pendidikan, $pendidikan_terakhir, $bidang_keahlian, $tanggal_bergabung, $ruangan, $status, $id]);
                }
                
                if ($status_lama != $status) {
                    $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_riwayat->execute(['anggota_lab', $id, $_SESSION['id_user'], $status_lama, $status, 'Update anggota: ' . $nama]);
                }
                
                $success = "Anggota berhasil diupdate! Menunggu persetujuan admin.";
            } else {
                $error = "Anda tidak memiliki akses untuk mengubah data ini!";
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO anggota_lab (nama, nip, email, kontak, biodata_teks, pendidikan, pendidikan_terakhir, bidang_keahlian, tanggal_bergabung, ruangan, foto, status, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nama, $nip, $email, $kontak, $biodata_teks, $pendidikan, $pendidikan_terakhir, $bidang_keahlian, $tanggal_bergabung, $ruangan, $foto, $status, $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['anggota_lab', $new_id, $_SESSION['id_user'], null, $status, 'Tambah anggota: ' . $nama]);
            
            $success = "Anggota berhasil ditambahkan! Menunggu persetujuan admin.";
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Operator hanya bisa lihat data miliknya sendiri
$stmt = $pdo->prepare("SELECT * FROM anggota_lab WHERE id_user = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['id_user']]);
$anggota_list = $stmt->fetchAll();

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manajemen Anggota Lab</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#anggotaModal" onclick="resetForm()">
        <i class="bi bi-plus-circle"></i> Tambah Anggota
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
    <i class="bi bi-info-circle"></i> <strong>Info:</strong> Semua data yang Anda tambahkan akan berstatus <span class="badge bg-warning">Pending</span> dan menunggu persetujuan admin.
</div>

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Email</th>
                        <th>Kontak</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($anggota_list as $anggota): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <?php if ($anggota['foto']): ?>
                                <img src="../../uploads/anggota/<?php echo $anggota['foto']; ?>" width="50" height="50" class="rounded-circle">
                            <?php else: ?>
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($anggota['nama']); ?>" width="50" height="50" class="rounded-circle">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($anggota['nama']); ?></td>
                        <td><?php echo htmlspecialchars($anggota['nip'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($anggota['email'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($anggota['kontak'] ?? '-'); ?></td>
                        <td>
                            <?php if ($anggota['status'] == 'pending'): ?>
                                <span class="badge bg-warning">Pending</span>
                            <?php elseif ($anggota['status'] == 'active'): ?>
                                <span class="badge bg-success">Approved</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Rejected</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($anggota['status'] == 'pending' || $anggota['status'] == 'rejected'): ?>
                                <button class="btn btn-sm btn-warning" onclick="editAnggota(<?php echo htmlspecialchars(json_encode($anggota)); ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="?delete=<?php echo $anggota['id_anggota']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
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
<div class="modal fade" id="anggotaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Anggota Lab</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_anggota" id="id_anggota">
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> Data akan berstatus <strong>Pending</strong> dan menunggu persetujuan admin
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" name="nama" id="nama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIP</label>
                            <input type="text" class="form-control" name="nip" id="nip">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kontak</label>
                            <input type="text" class="form-control" name="kontak" id="kontak">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Foto</label>
                        <input type="file" class="form-control" name="foto" accept="image/*">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Biodata</label>
                        <textarea class="form-control" name="biodata_teks" id="biodata_teks" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pendidikan</label>
                            <textarea class="form-control" name="pendidikan" id="pendidikan" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pendidikan Terakhir</label>
                            <input type="text" class="form-control" name="pendidikan_terakhir" id="pendidikan_terakhir">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Bidang Keahlian</label>
                        <textarea class="form-control" name="bidang_keahlian" id="bidang_keahlian" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Bergabung</label>
                            <input type="date" class="form-control" name="tanggal_bergabung" id="tanggal_bergabung">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ruangan</label>
                            <input type="text" class="form-control" name="ruangan" id="ruangan">
                        </div>
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
    document.getElementById('modalTitle').textContent = 'Tambah Anggota Lab';
    document.querySelector('form').reset();
    document.getElementById('id_anggota').value = '';
}

function editAnggota(data) {
    document.getElementById('modalTitle').textContent = 'Edit Anggota Lab';
    document.getElementById('id_anggota').value = data.id_anggota;
    document.getElementById('nama').value = data.nama;
    document.getElementById('nip').value = data.nip || '';
    document.getElementById('email').value = data.email || '';
    document.getElementById('kontak').value = data.kontak || '';
    document.getElementById('biodata_teks').value = data.biodata_teks || '';
    document.getElementById('pendidikan').value = data.pendidikan || '';
    document.getElementById('pendidikan_terakhir').value = data.pendidikan_terakhir || '';
    document.getElementById('bidang_keahlian').value = data.bidang_keahlian || '';
    document.getElementById('tanggal_bergabung').value = data.tanggal_bergabung || '';
    document.getElementById('ruangan').value = data.ruangan || '';
    
    new bootstrap.Modal(document.getElementById('anggotaModal')).show();
}
</script>

<?php include "footer.php"; ?>