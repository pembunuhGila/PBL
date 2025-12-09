<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "admin";
include "../auth.php";
include "../../conn.php";

$page_title = "Anggota Lab";
$current_page = "anggota.php";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt_old = $pdo->prepare("SELECT nama, status FROM anggota_lab WHERE id_anggota = ?");
        $stmt_old->execute([$id]);
        $old_data = $stmt_old->fetch();
        
        $stmt = $pdo->prepare("DELETE FROM anggota_lab WHERE id_anggota = ?");
        $stmt->execute([$id]);
        
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_riwayat->execute(['anggota_lab', $id, $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus anggota: ' . $old_data['nama']]);
        
        $success = "Anggota berhasil dihapus!";
    } catch (PDOException $e) {
        $error = "Gagal menghapus: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['search'])) {
    $nama = $_POST['nama'];
    $tipe_anggota = $_POST['tipe_anggota'];
    $nip_nim = $_POST['nip_nim'];
    $email = $_POST['email'];
    $kontak = $_POST['kontak'];
    $biodata_teks = $_POST['biodata_teks'];
    
    $pendidikan_array = [];
    if ($tipe_anggota == 'dosen' && isset($_POST['pendidikan_jenjang']) && is_array($_POST['pendidikan_jenjang'])) {
        foreach ($_POST['pendidikan_jenjang'] as $index => $jenjang) {
            if (!empty($jenjang) && !empty($_POST['pendidikan_institusi'][$index])) {
                $pendidikan_array[] = [
                    'jenjang' => $jenjang,
                    'institusi' => $_POST['pendidikan_institusi'][$index],
                    'tahun' => $_POST['pendidikan_tahun'][$index] ?? '',
                    'jurusan' => $_POST['pendidikan_jurusan'][$index] ?? ''
                ];
            }
        }
    }
    $pendidikan = json_encode($pendidikan_array);
    
    $matakuliah_array = [];
    if ($tipe_anggota == 'dosen' && isset($_POST['matakuliah_nama']) && is_array($_POST['matakuliah_nama'])) {
        foreach ($_POST['matakuliah_nama'] as $index => $nama_mk) {
            if (!empty($nama_mk)) {
                $matakuliah_array[] = ['nama' => $nama_mk];
            }
        }
    }
    $bidang_keahlian = json_encode($matakuliah_array);
    
    $tanggal_bergabung = $_POST['tanggal_bergabung'];
    $status = 'active';
    
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
            
            $stmt_old = $pdo->prepare("SELECT status FROM anggota_lab WHERE id_anggota = ?");
            $stmt_old->execute([$id]);
            $old_data = $stmt_old->fetch();
            $status_lama = $old_data['status'];
            
            if ($foto) {
                $stmt = $pdo->prepare("UPDATE anggota_lab SET nama=?, nip=?, email=?, kontak=?, biodata_teks=?, pendidikan=?, bidang_keahlian=?, tanggal_bergabung=?, foto=?, status=? WHERE id_anggota=?");
                $stmt->execute([$nama, $nip_nim, $email, $kontak, $biodata_teks, $pendidikan, $bidang_keahlian, $tanggal_bergabung, $foto, $status, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE anggota_lab SET nama=?, nip=?, email=?, kontak=?, biodata_teks=?, pendidikan=?, bidang_keahlian=?, tanggal_bergabung=?, status=? WHERE id_anggota=?");
                $stmt->execute([$nama, $nip_nim, $email, $kontak, $biodata_teks, $pendidikan, $bidang_keahlian, $tanggal_bergabung, $status, $id]);
            }
            
            if ($status_lama != $status) {
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['anggota_lab', $id, $_SESSION['id_user'], $status_lama, $status, 'Update anggota: ' . $nama]);
            }
            
            $success = "Anggota berhasil diupdate!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO anggota_lab (nama, nip, email, kontak, biodata_teks, pendidikan, bidang_keahlian, tanggal_bergabung, foto, status, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nama, $nip_nim, $email, $kontak, $biodata_teks, $pendidikan, $bidang_keahlian, $tanggal_bergabung, $foto, $status, $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['anggota_lab', $new_id, $_SESSION['id_user'], null, $status, 'Tambah anggota: ' . $nama]);
            
            $success = "Anggota berhasil ditambahkan dan langsung aktif!";
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';

$where_clauses = ["id_user = ?"];
$params = [$_SESSION['id_user']];

if ($search) {
    $where_clauses[] = "(nama ILIKE ? OR nip ILIKE ? OR email ILIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($status_filter) {
    $where_clauses[] = "status = ?";
    $params[] = $status_filter;
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

$query = "SELECT * FROM anggota_lab $where_sql ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
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

<!-- Search -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-10">
                <label class="form-label"><i class="bi bi-search"></i> Cari Anggota</label>
                <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan nama, NIP/NIM, atau email..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Cari
                </button>
                <a href="anggota.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<?php if ($search): ?>
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> 
    Menampilkan <?php echo count($anggota_list); ?> hasil untuk pencarian "<strong><?php echo htmlspecialchars($search); ?></strong>"
    <a href="anggota.php" class="alert-link ms-2">Reset pencarian</a>
</div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>NIP/NIM</th>
                        <th>Email</th>
                        <th>Kontak</th>
                        <th>Pendidikan</th>
                        <th>Mata Kuliah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (count($anggota_list) > 0) {
                        $no = 1; 
                        foreach ($anggota_list as $anggota): 
                    ?>
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
                            <?php 
                            if ($anggota['pendidikan']) {
                                $pendidikan_data = json_decode($anggota['pendidikan'], true);
                                if (is_array($pendidikan_data) && count($pendidikan_data) > 0) {
                                    echo '<small>';
                                    foreach ($pendidikan_data as $edu) {
                                        echo '<strong>' . htmlspecialchars($edu['jenjang']) . '</strong><br>';
                                    }
                                    echo '</small>';
                                } else {
                                    echo '-';
                                }
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            if ($anggota['bidang_keahlian']) {
                                $mk_data = json_decode($anggota['bidang_keahlian'], true);
                                if (is_array($mk_data) && count($mk_data) > 0) {
                                    echo '<small>';
                                    $count = 0;
                                    foreach ($mk_data as $mk) {
                                        if ($count >= 2) {
                                            echo '+ ' . (count($mk_data) - 2) . ' lainnya';
                                            break;
                                        }
                                        echo htmlspecialchars($mk['nama']) . '<br>';
                                        $count++;
                                    }
                                    echo '</small>';
                                } else {
                                    echo '-';
                                }
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editAnggota(<?php echo htmlspecialchars(json_encode($anggota)); ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="?delete=<?php echo $anggota['id_anggota']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endforeach;
                    } else {
                    ?>
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-3 text-muted">
                                <?php if ($search): ?>
                                    Tidak ada anggota yang sesuai dengan pencarian
                                <?php else: ?>
                                    Belum ada anggota
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

<!-- Modal Form (sama seperti sebelumnya, tidak ada perubahan) -->
<div class="modal fade" id="anggotaModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Anggota Lab</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_anggota" id="id_anggota">
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Data akan langsung aktif setelah disimpan
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" name="nama" id="nama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipe Anggota *</label>
                            <select class="form-select" name="tipe_anggota" id="tipe_anggota" required onchange="toggleAnggotaFields()">
                                <option value="">-- Pilih Tipe --</option>
                                <option value="dosen">Dosen</option>
                                <option value="mahasiswa">Mahasiswa</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">NIP/NIM *</label>
                            <input type="text" class="form-control" name="nip_nim" id="nip_nim" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="email">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kontak</label>
                            <input type="text" class="form-control" name="kontak" id="kontak">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Tanggal Bergabung</label>
                            <input type="date" class="form-control" name="tanggal_bergabung" id="tanggal_bergabung">
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
                    
                    <div id="dosenSection" style="display: none;">
                        <hr class="my-4">
                        <h6 class="mb-3">Riwayat Pendidikan</h6>
                        
                        <div id="pendidikanContainer"></div>
                        
                        <button type="button" class="btn btn-sm btn-outline-primary mb-3" onclick="addPendidikan()">
                            <i class="bi bi-plus-circle"></i> Tambah Pendidikan
                        </button>
                        
                        <hr class="my-4">
                        <h6 class="mb-3">Mata Kuliah yang Diampu</h6>

                        <div id="matakuliahContainer"></div>
                        
                        <button type="button" class="btn btn-sm btn-outline-primary mb-3" onclick="addMatakuliah()">
                            <i class="bi bi-plus-circle"></i> Tambah Mata Kuliah
                        </button>
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
// JavaScript functions sama seperti sebelumnya
function toggleAnggotaFields() {
    const tipeAnggota = document.getElementById('tipe_anggota').value;
    const dosenSection = document.getElementById('dosenSection');
    
    if (tipeAnggota === 'dosen') {
        dosenSection.style.display = 'block';
    } else {
        dosenSection.style.display = 'none';
    }
}

function addPendidikan() {
    // Implementation sama seperti sebelumnya
}

function removePendidikan(button) {
    button.closest('.pendidikan-item').remove();
    updateDeleteButtons('pendidikan');
}

function addMatakuliah() {
    // Implementation sama seperti sebelumnya
}

function removeMatakuliah(button) {
    button.closest('.matakuliah-item').remove();
    updateDeleteButtons('matakuliah');
}

function updateDeleteButtons(type) {
    const items = document.querySelectorAll(`.${type}-item`);
    items.forEach((item, index) => {
        const deleteBtn = item.querySelector('.btn-danger');
        if (items.length > 1) {
            deleteBtn.style.display = 'block';
        } else {
            deleteBtn.style.display = 'none';
        }
    });
}

function resetForm() {
    document.getElementById('modalTitle').textContent = 'Tambah Anggota Lab';
    document.querySelector('form').reset();
    document.getElementById('id_anggota').value = '';
    document.getElementById('tipe_anggota').value = '';
    toggleAnggotaFields();
}

function editAnggota(data) {
    // Implementation sama seperti sebelumnya
    document.getElementById('modalTitle').textContent = 'Edit Anggota Lab';
    document.getElementById('id_anggota').value = data.id_anggota;
    document.getElementById('nama').value = data.nama;
    document.getElementById('nip_nim').value = data.nip || '';
    document.getElementById('email').value = data.email || '';
    document.getElementById('kontak').value = data.kontak || '';
    document.getElementById('biodata_teks').value = data.biodata_teks || '';
    document.getElementById('tanggal_bergabung').value = data.tanggal_bergabung || '';
    
    document.getElementById('tipe_anggota').value = 'dosen';
    toggleAnggotaFields();
    
    new bootstrap.Modal(document.getElementById('anggotaModal')).show();
}
</script>

<style>
.pagination {
    margin-bottom: 0;
}

.page-link {
    color: #1e3c72;
}

.page-item.active .page-link {
    background-color: #1e3c72;
    border-color: #1e3c72;
}

.page-link:hover {
    color: #2a5298;
}
</style>

<?php include "footer.php"; ?>