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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $tipe_anggota = $_POST['tipe_anggota']; // dosen atau mahasiswa
    $nip_nim = $_POST['nip_nim'];
    $email = $_POST['email'];
    $kontak = $_POST['kontak'];
    $biodata_teks = $_POST['biodata_teks'];
    
    // Process pendidikan array (hanya untuk dosen)
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
    
    // Process mata kuliah array (hanya untuk dosen)
    $matakuliah_array = [];
    if ($tipe_anggota == 'dosen' && isset($_POST['matakuliah_nama']) && is_array($_POST['matakuliah_nama'])) {
        foreach ($_POST['matakuliah_nama'] as $index => $nama_mk) {
            if (!empty($nama_mk)) {
                $matakuliah_array[] = [
                    'nama' => $nama_mk
                ];
            }
        }
    }
    $bidang_keahlian = json_encode($matakuliah_array);
    
    $tanggal_bergabung = $_POST['tanggal_bergabung'];
    
    $status = 'active'; // Admin langsung active
    
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

$stmt = $pdo->query("SELECT * FROM anggota_lab ORDER BY created_at DESC");
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
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form -->
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
                    
                    <!-- Section Dosen Only -->
                    <div id="dosenSection" style="display: none;">
                        <hr class="my-4">
                        <h6 class="mb-3">Riwayat Pendidikan</h6>
                        
                        <div id="pendidikanContainer">
                            <div class="pendidikan-item border rounded p-3 mb-3 bg-light">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label small">Jenjang *</label>
                                        <select class="form-select form-select-sm" name="pendidikan_jenjang[]">
                                            <option value="">Pilih Jenjang</option>
                                            <option value="D3">D3</option>
                                            <option value="D4">D4</option>
                                            <option value="S1">S1</option>
                                            <option value="S2">S2</option>
                                            <option value="S3">S3</option>
                                            <option value="Profesi">Profesi</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label small">Institusi *</label>
                                        <input type="text" class="form-control form-control-sm" name="pendidikan_institusi[]" placeholder="Universitas...">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label small">Jurusan</label>
                                        <input type="text" class="form-control form-control-sm" name="pendidikan_jurusan[]" placeholder="Teknik Informatika...">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label small">Tahun</label>
                                        <div class="d-flex gap-1">
                                            <input type="text" class="form-control form-control-sm" name="pendidikan_tahun[]" placeholder="2020">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="removePendidikan(this)" style="display: none;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-sm btn-outline-primary mb-3" onclick="addPendidikan()">
                            <i class="bi bi-plus-circle"></i> Tambah Pendidikan
                        </button>
                        
                        <hr class="my-4">
                        <h6 class="mb-3">Mata Kuliah yang Diampu</h6>

                        <div id="matakuliahContainer">
                            <div class="matakuliah-item border rounded p-3 mb-3 bg-light">
                                <div class="row align-items-end">
                                    <div class="col-auto mb-2">
                                        <label class="form-label small d-block">&nbsp;</label>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeMatakuliah(this)" style="display: none;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <div class="col mb-2">
                                        <label class="form-label small">Nama Mata Kuliah *</label>
                                        <input type="text" class="form-control form-control-sm" name="matakuliah_nama[]" placeholder="Pemrograman Web">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
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
function toggleAnggotaFields() {
    const tipeAnggota = document.getElementById('tipe_anggota').value;
    const dosenSection = document.getElementById('dosenSection');
    
    if (tipeAnggota === 'dosen') {
        dosenSection.style.display = 'block';
    } else {
        dosenSection.style.display = 'none';
    }
}

// PENDIDIKAN FUNCTIONS
function addPendidikan() {
    const container = document.getElementById('pendidikanContainer');
    const newItem = document.createElement('div');
    newItem.className = 'pendidikan-item border rounded p-3 mb-3 bg-light';
    newItem.innerHTML = `
        <div class="row">
            <div class="col-md-3 mb-2">
                <label class="form-label small">Jenjang *</label>
                <select class="form-select form-select-sm" name="pendidikan_jenjang[]">
                    <option value="">Pilih Jenjang</option>
                    <option value="D3">D3</option>
                    <option value="D4">D4</option>
                    <option value="S1">S1</option>
                    <option value="S2">S2</option>
                    <option value="S3">S3</option>
                    <option value="Profesi">Profesi</option>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label small">Institusi *</label>
                <input type="text" class="form-control form-control-sm" name="pendidikan_institusi[]" placeholder="Universitas...">
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label small">Jurusan</label>
                <input type="text" class="form-control form-control-sm" name="pendidikan_jurusan[]" placeholder="Teknik Informatika...">
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label small">Tahun</label>
                <div class="d-flex gap-1">
                    <input type="text" class="form-control form-control-sm" name="pendidikan_tahun[]" placeholder="2020">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removePendidikan(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.appendChild(newItem);
    updateDeleteButtons('pendidikan');
}

function removePendidikan(button) {
    button.closest('.pendidikan-item').remove();
    updateDeleteButtons('pendidikan');
}

// MATA KULIAH FUNCTIONS
function addMatakuliah() {
    const container = document.getElementById('matakuliahContainer');
    const newItem = document.createElement('div');
    newItem.className = 'matakuliah-item border rounded p-3 mb-3 bg-light';
    newItem.innerHTML = `
        <div class="row align-items-end">
            <div class="col-auto mb-2">
                <label class="form-label small d-block">&nbsp;</label>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeMatakuliah(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="col mb-2">
                <label class="form-label small">Nama Mata Kuliah *</label>
                <input type="text" class="form-control form-control-sm" name="matakuliah_nama[]" placeholder="Pemrograman Web">
            </div>
        </div>
    `;
    container.appendChild(newItem);
    updateDeleteButtons('matakuliah');
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
    
    // Reset pendidikan
    const pendidikanContainer = document.getElementById('pendidikanContainer');
    pendidikanContainer.innerHTML = `
        <div class="pendidikan-item border rounded p-3 mb-3 bg-light">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label small">Jenjang *</label>
                    <select class="form-select form-select-sm" name="pendidikan_jenjang[]">
                        <option value="">Pilih Jenjang</option>
                        <option value="D3">D3</option>
                        <option value="D4">D4</option>
                        <option value="S1">S1</option>
                        <option value="S2">S2</option>
                        <option value="S3">S3</option>
                        <option value="Profesi">Profesi</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label small">Institusi *</label>
                    <input type="text" class="form-control form-control-sm" name="pendidikan_institusi[]" placeholder="Universitas...">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label small">Jurusan</label>
                    <input type="text" class="form-control form-control-sm" name="pendidikan_jurusan[]" placeholder="Teknik Informatika...">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label small">Tahun</label>
                    <div class="d-flex gap-1">
                        <input type="text" class="form-control form-control-sm" name="pendidikan_tahun[]" placeholder="2020">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removePendidikan(this)" style="display: none;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Reset mata kuliah
    const matakuliahContainer = document.getElementById('matakuliahContainer');
    matakuliahContainer.innerHTML = `
        <div class="matakuliah-item border rounded p-3 mb-3 bg-light">
            <div class="row align-items-end">
                <div class="col-auto mb-2">
                    <label class="form-label small d-block">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeMatakuliah(this)" style="display: none;">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="col mb-2">
                    <label class="form-label small">Nama Mata Kuliah *</label>
                    <input type="text" class="form-control form-control-sm" name="matakuliah_nama[]" placeholder="Pemrograman Web">
                </div>
            </div>
        </div>
    `;
}

function editAnggota(data) {
    document.getElementById('modalTitle').textContent = 'Edit Anggota Lab';
    document.getElementById('id_anggota').value = data.id_anggota;
    document.getElementById('nama').value = data.nama;
    document.getElementById('nip_nim').value = data.nip || '';
    document.getElementById('email').value = data.email || '';
    document.getElementById('kontak').value = data.kontak || '';
    document.getElementById('biodata_teks').value = data.biodata_teks || '';
    document.getElementById('tanggal_bergabung').value = data.tanggal_bergabung || '';
    
    // Parse and populate pendidikan
    const pendidikanContainer = document.getElementById('pendidikanContainer');
    pendidikanContainer.innerHTML = '';
    
    let pendidikanData = [];
    try {
        if (data.pendidikan) {
            pendidikanData = JSON.parse(data.pendidikan);
        }
    } catch (e) {
        console.error('Error parsing pendidikan:', e);
    }
    
    if (pendidikanData.length === 0) {
        pendidikanData = [{jenjang: '', institusi: '', jurusan: '', tahun: ''}];
    }
    
    pendidikanData.forEach((edu, index) => {
        const newItem = document.createElement('div');
        newItem.className = 'pendidikan-item border rounded p-3 mb-3 bg-light';
        newItem.innerHTML = `
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label small">Jenjang *</label>
                    <select class="form-select form-select-sm" name="pendidikan_jenjang[]">
                        <option value="">Pilih Jenjang</option>
                        <option value="D3" ${edu.jenjang === 'D3' ? 'selected' : ''}>D3</option>
                        <option value="D4" ${edu.jenjang === 'D4' ? 'selected' : ''}>D4</option>
                        <option value="S1" ${edu.jenjang === 'S1' ? 'selected' : ''}>S1</option>
                        <option value="S2" ${edu.jenjang === 'S2' ? 'selected' : ''}>S2</option>
                        <option value="S3" ${edu.jenjang === 'S3' ? 'selected' : ''}>S3</option>
                        <option value="Profesi" ${edu.jenjang === 'Profesi' ? 'selected' : ''}>Profesi</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label small">Institusi *</label>
                    <input type="text" class="form-control form-control-sm" name="pendidikan_institusi[]" placeholder="Universitas..." value="${edu.institusi || ''}">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label small">Jurusan</label>
                    <input type="text" class="form-control form-control-sm" name="pendidikan_jurusan[]" placeholder="Teknik Informatika..." value="${edu.jurusan || ''}">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label small">Tahun</label>
                    <div class="d-flex gap-1">
                        <input type="text" class="form-control form-control-sm" name="pendidikan_tahun[]" placeholder="2020" value="${edu.tahun || ''}">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removePendidikan(this)" style="${pendidikanData.length > 1 ? '' : 'display: none;'}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        pendidikanContainer.appendChild(newItem);
    });
    
    // Parse and populate mata kuliah
    const matakuliahContainer = document.getElementById('matakuliahContainer');
    matakuliahContainer.innerHTML = '';
    
    let matakuliahData = [];
    try {
        if (data.bidang_keahlian) {
            matakuliahData = JSON.parse(data.bidang_keahlian);
        }
    } catch (e) {
        console.error('Error parsing mata kuliah:', e);
    }
    
    if (matakuliahData.length === 0) {
        matakuliahData = [{nama: ''}];
    }
    
    matakuliahData.forEach((mk, index) => {
        const newItem = document.createElement('div');
        newItem.className = 'matakuliah-item border rounded p-3 mb-3 bg-light';
        newItem.innerHTML = `
            <div class="row align-items-end">
                <div class="col-auto mb-2">
                    <label class="form-label small d-block">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeMatakuliah(this)" style="${matakuliahData.length > 1 ? '' : 'display: none;'}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="col mb-2">
                    <label class="form-label small">Nama Mata Kuliah *</label>
                    <input type="text" class="form-control form-control-sm" name="matakuliah_nama[]" placeholder="Pemrograman Web" value="${mk.nama || ''}">
                </div>
            </div>
        `;
        matakuliahContainer.appendChild(newItem);
    });
    
    // Set tipe anggota dan toggle fields
    document.getElementById('tipe_anggota').value = 'dosen'; // Set sebagai dosen untuk edit
    toggleAnggotaFields();
    
    new bootstrap.Modal(document.getElementById('anggotaModal')).show();
}
</script>

<?php include "footer.php"; ?>