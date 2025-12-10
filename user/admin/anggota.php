<?php
/**
 * Admin - Anggota Lab
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "admin";
include "../auth.php";
include "../../conn.php";

$page_title = "Anggota Lab";
$current_page = "anggota.php";

// Pagination
$limit = 15;
$page_num = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page_num - 1) * $limit;

// Handle Status Change (Approve/Reject Pending)
if (isset($_GET['approve']) || isset($_GET['reject'])) {
    $id = isset($_GET['approve']) ? $_GET['approve'] : $_GET['reject'];
    $new_status = isset($_GET['approve']) ? 'active' : 'rejected';
    
    try {
        $stmt_old = $pdo->prepare("SELECT nama, status FROM anggota_lab WHERE id_anggota = ?");
        $stmt_old->execute([$id]);
        $old_data = $stmt_old->fetch();
        
        $stmt = $pdo->prepare("UPDATE anggota_lab SET status = ? WHERE id_anggota = ?");
        $stmt->execute([$new_status, $id]);
        
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $catatan = isset($_GET['approve']) ? 'DISETUJUI anggota: ' . $old_data['nama'] : 'DITOLAK anggota: ' . $old_data['nama'];
        $stmt_riwayat->execute(['anggota_lab', $id, $_SESSION['id_user'], $old_data['status'], $new_status, $catatan]);
        
        header("Location: anggota.php?success=" . ($new_status == 'active' ? 'approved' : 'rejected') . "&page=" . $page_num);
        exit;
    } catch (PDOException $e) {
        $error = "Gagal: " . $e->getMessage();
    }
}

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
        
        header("Location: anggota.php?success=deleted&page=" . $page_num);
        exit;
    } catch (PDOException $e) {
        $error = "Gagal menghapus: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['search'])) {
    try {
        if (empty($_POST['nama']) || empty($_POST['role_anggota']) || empty($_POST['nip_nim'])) {
            throw new Exception("Nama, Role, dan NIP/NIM wajib diisi!");
        }
        
        $nama = trim($_POST['nama']);
        $role_anggota = $_POST['role_anggota'];
        $nip_nim = trim($_POST['nip_nim']);
        $email = trim($_POST['email'] ?? '');
        $kontak = trim($_POST['kontak'] ?? '');
        $biodata_teks = trim($_POST['biodata_teks'] ?? '');
        $linkedin = trim($_POST['linkedin'] ?? '');
        $shinta = trim($_POST['shinta'] ?? '');
        
        // Process pendidikan array
        $pendidikan_array = [];
        if ($role_anggota == 'dosen' && isset($_POST['pendidikan_jenjang']) && is_array($_POST['pendidikan_jenjang'])) {
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
        $pendidikan = empty($pendidikan_array) ? '[]' : json_encode($pendidikan_array);
        
        // Process mata kuliah array
        $matakuliah_array = [];
        if ($role_anggota == 'dosen' && isset($_POST['matakuliah_nama']) && is_array($_POST['matakuliah_nama'])) {
            foreach ($_POST['matakuliah_nama'] as $index => $nama_mk) {
                if (!empty(trim($nama_mk))) {
                    $matakuliah_array[] = ['nama' => trim($nama_mk)];
                }
            }
        }
        $bidang_keahlian = empty($matakuliah_array) ? '[]' : json_encode($matakuliah_array);
        
        $tanggal_bergabung = $_POST['tanggal_bergabung'] ?? null;
        $status = 'active';
        
        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $target_dir = "../../uploads/anggota/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            
            $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto = 'anggota_' . time() . '.' . $file_extension;
            move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $foto);
        }
        
        if (isset($_POST['id_anggota']) && !empty($_POST['id_anggota'])) {
            $id = $_POST['id_anggota'];
            
            $stmt_old = $pdo->prepare("SELECT status FROM anggota_lab WHERE id_anggota = ?");
            $stmt_old->execute([$id]);
            $old_data = $stmt_old->fetch();
            
            if ($foto) {
                $stmt = $pdo->prepare("UPDATE anggota_lab SET nama=?, nip=?, email=?, kontak=?, biodata_teks=?, pendidikan=?, bidang_keahlian=?, tanggal_bergabung=?, foto=? WHERE id_anggota=?");
                $stmt->execute([$nama, $nip_nim, $email, $kontak, $biodata_teks, $pendidikan, $bidang_keahlian, $tanggal_bergabung, $foto, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE anggota_lab SET nama=?, nip=?, email=?, kontak=?, biodata_teks=?, pendidikan=?, bidang_keahlian=?, tanggal_bergabung=? WHERE id_anggota=?");
                $stmt->execute([$nama, $nip_nim, $email, $kontak, $biodata_teks, $pendidikan, $bidang_keahlian, $tanggal_bergabung, $id]);
            }
            
            // Handle social media
            $stmt_del = $pdo->prepare("DELETE FROM social_media_anggota WHERE id_anggota = ? AND platform IN ('linkedin', 'scholar')");
            $stmt_del->execute([$id]);
            
            if (!empty($linkedin)) {
                $stmt_sm = $pdo->prepare("INSERT INTO social_media_anggota (id_anggota, platform, url) VALUES (?, 'linkedin', ?)");
                $stmt_sm->execute([$id, $linkedin]);
            }
            if (!empty($shinta)) {
                $stmt_sm = $pdo->prepare("INSERT INTO social_media_anggota (id_anggota, platform, url) VALUES (?, 'scholar', ?)");
                $stmt_sm->execute([$id, $shinta]);
            }
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['anggota_lab', $id, $_SESSION['id_user'], $old_data['status'], $status, 'Edit anggota: ' . $nama]);
            
            header("Location: anggota.php?success=updated&page=" . $page_num);
            exit;
        } else {
            $stmt = $pdo->prepare("INSERT INTO anggota_lab (nama, nip, email, kontak, biodata_teks, pendidikan, bidang_keahlian, tanggal_bergabung, foto, status, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nama, $nip_nim, $email, $kontak, $biodata_teks, $pendidikan, $bidang_keahlian, $tanggal_bergabung, $foto, $status, $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            if (!empty($linkedin)) {
                $stmt_sm = $pdo->prepare("INSERT INTO social_media_anggota (id_anggota, platform, url) VALUES (?, 'linkedin', ?)");
                $stmt_sm->execute([$new_id, $linkedin]);
            }
            if (!empty($shinta)) {
                $stmt_sm = $pdo->prepare("INSERT INTO social_media_anggota (id_anggota, platform, url) VALUES (?, 'scholar', ?)");
                $stmt_sm->execute([$new_id, $shinta]);
            }
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['anggota_lab', $new_id, $_SESSION['id_user'], null, $status, 'Tambah anggota: ' . $nama]);
            
            header("Location: anggota.php?success=added");
            exit;
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan (Database): " . $e->getMessage();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';

$where_clauses = [];
$params = [];

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
} else {
    // Default: hide rejected dan deleted klo gak ada filter
    $where_clauses[] = "status NOT IN ('rejected', 'deleted')";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total count
$count_query = "SELECT COUNT(*) FROM anggota_lab $where_sql";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $limit);

// Get data
$query = "SELECT * FROM anggota_lab $where_sql ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params_with_limit = array_merge($params, [$limit, $offset]);
$stmt = $pdo->prepare($query);
$stmt->execute($params_with_limit);
$anggota_list = $stmt->fetchAll();

// Get pending count
$pending_count_query = "SELECT COUNT(DISTINCT a.id_anggota) FROM riwayat_pengajuan rp JOIN anggota_lab a ON rp.id_data = a.id_anggota WHERE rp.tabel_sumber = 'anggota_lab' AND a.status = 'pending'";
$pending_count_stmt = $pdo->prepare($pending_count_query);
$pending_count_stmt->execute();
$pending_count = $pending_count_stmt->fetchColumn();

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

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php 
        if ($_GET['success'] == 'added') echo "✅ Anggota berhasil ditambahkan!";
        if ($_GET['success'] == 'updated') echo "✅ Anggota berhasil diupdate!";
        if ($_GET['success'] == 'deleted') echo "✅ Anggota berhasil dihapus!";
        if ($_GET['success'] == 'approved') echo "✅ Pengajuan berhasil disetujui!";
        if ($_GET['success'] == 'rejected') echo "❌ Pengajuan berhasil ditolak!";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Search -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <input type="text" class="form-control" name="search" placeholder="Cari nama, NIP/NIM, atau email..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status_filter">
                    <option value="">Semua Status</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
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

<div class="card shadow">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Total: <?php echo $total_items; ?> anggota</h6>
        <small class="text-muted">Halaman <?php echo $page_num; ?> dari <?php echo max(1, $total_pages); ?></small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>NIP/NIM</th>
                        <th>Email</th>
                        <th>Kontak</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($anggota_list) > 0): ?>
                        <?php 
                        $no = $offset + 1;
                        foreach ($anggota_list as $anggota): 
                            $stmt_sm = $pdo->prepare("SELECT platform, url FROM social_media_anggota WHERE id_anggota = ?");
                            $stmt_sm->execute([$anggota['id_anggota']]);
                            $social_media = $stmt_sm->fetchAll(PDO::FETCH_KEY_PAIR);
                        ?>
                        <tr>
                            <td class="ps-3"><?php echo $no++; ?></td>
                            <td>
                                <?php if ($anggota['foto']): ?>
                                    <img src="../../uploads/anggota/<?php echo $anggota['foto']; ?>" width="35" height="35" class="rounded-circle" style="object-fit: cover;">
                                <?php else: ?>
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($anggota['nama']); ?>" width="35" height="35" class="rounded-circle">
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($anggota['nama']); ?></strong></td>
                            <td><?php echo htmlspecialchars($anggota['nip'] ?? '-'); ?></td>
                            <td><small><?php echo htmlspecialchars($anggota['email'] ?? '-'); ?></small></td>
                            <td><?php echo htmlspecialchars($anggota['kontak'] ?? '-'); ?></td>
                            <td>
                                <?php if ($anggota['status'] == 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php elseif ($anggota['status'] == 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick='editAnggota(<?php echo htmlspecialchars(json_encode(array_merge($anggota, ["social_media" => $social_media])), ENT_QUOTES, "UTF-8"); ?>)'>
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                
                                <?php if ($anggota['status'] == 'pending'): ?>
                                    <a href="?approve=<?php echo $anggota['id_anggota']; ?>&page=<?php echo $page_num; ?>" class="btn btn-sm btn-success" onclick="return confirm('Setujui pengajuan ini?')">
                                        <i class="bi bi-check"></i> Acc
                                    </a>
                                    <a href="?reject=<?php echo $anggota['id_anggota']; ?>&page=<?php echo $page_num; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tolak pengajuan ini?')">
                                        <i class="bi bi-x"></i> Reject
                                    </a>
                                <?php endif; ?>
                                
                                <a href="?delete=<?php echo $anggota['id_anggota']; ?>&page=<?php echo $page_num; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="mt-3 text-muted mb-0">Belum ada anggota</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($total_pages > 1): ?>
    <div class="card-footer bg-white">
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?php echo $page_num <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page_num - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status_filter=' . urlencode($status_filter) : ''; ?>">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                </li>
                
                <?php
                $start_page = max(1, $page_num - 2);
                $end_page = min($total_pages, $page_num + 2);
                
                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=1' . ($search ? '&search=' . urlencode($search) : '') . ($status_filter ? '&status_filter=' . urlencode($status_filter) : '') . '">1</a></li>';
                    if ($start_page > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $active = $i == $page_num ? 'active' : '';
                    echo "<li class='page-item $active'><a class='page-link' href='?page=$i" . ($search ? '&search=' . urlencode($search) : '') . ($status_filter ? '&status_filter=' . urlencode($status_filter) : '') . "'>$i</a></li>";
                }
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    echo "<li class='page-item'><a class='page-link' href='?page=$total_pages" . ($search ? '&search=' . urlencode($search) : '') . ($status_filter ? '&status_filter=' . urlencode($status_filter) : '') . "'>$total_pages</a></li>";
                }
                ?>
                
                <li class="page-item <?php echo $page_num >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page_num + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status_filter=' . urlencode($status_filter) : ''; ?>">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
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
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" name="nama" id="nama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role *</label>
                            <select class="form-select" name="role_anggota" id="role_anggota" required onchange="toggleRoleFields()">
                                <option value="">-- Pilih Role --</option>
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
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Bergabung</label>
                            <input type="date" class="form-control" name="tanggal_bergabung" id="tanggal_bergabung">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">LinkedIn</label>
                            <input type="url" class="form-control" name="linkedin" id="linkedin" placeholder="https://linkedin.com/in/...">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">SHINTA / Google Scholar</label>
                            <input type="url" class="form-control" name="shinta" id="shinta" placeholder="https://scholar.google.com/...">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Foto</label>
                        <input type="file" class="form-control" name="foto" accept="image/*">
                        <div id="currentFotoPreview"></div>
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
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleRoleFields() {
    const roleAnggota = document.getElementById('role_anggota').value;
    const dosenSection = document.getElementById('dosenSection');
    const pendidikanContainer = document.getElementById('pendidikanContainer');
    const matakuliahContainer = document.getElementById('matakuliahContainer');
    
    if (roleAnggota === 'dosen') {
        dosenSection.style.display = 'block';
        if (pendidikanContainer.children.length === 0) addPendidikan();
        if (matakuliahContainer.children.length === 0) addMatakuliah();
    } else {
        dosenSection.style.display = 'none';
        pendidikanContainer.innerHTML = '';
        matakuliahContainer.innerHTML = '';
    }
}

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
                    <option value="D3">D3</option><option value="D4">D4</option><option value="S1">S1</option>
                    <option value="S2">S2</option><option value="S3">S3</option><option value="Profesi">Profesi</option>
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
                    <button type="button" class="btn btn-sm btn-danger" onclick="removePendidikan(this)"><i class="bi bi-trash"></i></button>
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

function addMatakuliah() {
    const container = document.getElementById('matakuliahContainer');
    const newItem = document.createElement('div');
    newItem.className = 'matakuliah-item border rounded p-3 mb-3 bg-light';
    newItem.innerHTML = `
        <div class="row align-items-end">
            <div class="col-auto mb-2">
                <label class="form-label small d-block">&nbsp;</label>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeMatakuliah(this)"><i class="bi bi-trash"></i></button>
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
    items.forEach(item => {
        const deleteBtn = item.querySelector('.btn-danger');
        deleteBtn.style.display = items.length > 1 ? 'inline-block' : 'none';
    });
}

function resetForm() {
    document.getElementById('modalTitle').textContent = 'Tambah Anggota Lab';
    document.querySelector('form').reset();
    document.getElementById('id_anggota').value = '';
    document.getElementById('role_anggota').value = '';
    document.getElementById('currentFotoPreview').innerHTML = '';
    document.getElementById('dosenSection').style.display = 'none';
    document.getElementById('pendidikanContainer').innerHTML = '';
    document.getElementById('matakuliahContainer').innerHTML = '';
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
    
    const roleAnggota = data.nip && data.nip.length >= 15 ? 'dosen' : 'mahasiswa';
    document.getElementById('role_anggota').value = roleAnggota;
    toggleRoleFields();
    
    if (data.social_media) {
        document.getElementById('linkedin').value = data.social_media.linkedin || '';
        document.getElementById('shinta').value = data.social_media.scholar || '';
    }
    
    const previewContainer = document.getElementById('currentFotoPreview');
    previewContainer.innerHTML = '';
    if (data.foto) {
        previewContainer.innerHTML = `<div class="mt-2"><img src="../../uploads/anggota/${data.foto}" width="100" height="100" class="rounded-circle" style="object-fit: cover;"><p class="small text-muted mb-0 mt-1">Foto saat ini</p></div>`;
    }
    
    const pendidikanContainer = document.getElementById('pendidikanContainer');
    pendidikanContainer.innerHTML = '';
    let pendidikanData = [];
    if (data.pendidikan) {
        try {
            pendidikanData = typeof data.pendidikan === 'string' ? JSON.parse(data.pendidikan) : data.pendidikan;
        } catch (e) { console.error('Error parsing pendidikan:', e); }
    }
    
    if (pendidikanData.length > 0) {
        pendidikanData.forEach(item => {
            const newItem = document.createElement('div');
            newItem.className = 'pendidikan-item border rounded p-3 mb-3 bg-light';
            newItem.innerHTML = `
                <div class="row">
                    <div class="col-md-3 mb-2"><label class="form-label small">Jenjang *</label><select class="form-select form-select-sm" name="pendidikan_jenjang[]"><option value="">Pilih Jenjang</option><option value="D3" ${item.jenjang === 'D3' ? 'selected' : ''}>D3</option><option value="D4" ${item.jenjang === 'D4' ? 'selected' : ''}>D4</option><option value="S1" ${item.jenjang === 'S1' ? 'selected' : ''}>S1</option><option value="S2" ${item.jenjang === 'S2' ? 'selected' : ''}>S2</option><option value="S3" ${item.jenjang === 'S3' ? 'selected' : ''}>S3</option><option value="Profesi" ${item.jenjang === 'Profesi' ? 'selected' : ''}>Profesi</option></select></div>
                    <div class="col-md-4 mb-2"><label class="form-label small">Institusi *</label><input type="text" class="form-control form-control-sm" name="pendidikan_institusi[]" value="${item.institusi || ''}"></div>
                    <div class="col-md-3 mb-2"><label class="form-label small">Jurusan</label><input type="text" class="form-control form-control-sm" name="pendidikan_jurusan[]" value="${item.jurusan || ''}"></div>
                    <div class="col-md-2 mb-2"><label class="form-label small">Tahun</label><div class="d-flex gap-1"><input type="text" class="form-control form-control-sm" name="pendidikan_tahun[]" value="${item.tahun || ''}"><button type="button" class="btn btn-sm btn-danger" onclick="removePendidikan(this)"><i class="bi bi-trash"></i></button></div></div>
                </div>
            `;
            pendidikanContainer.appendChild(newItem);
        });
    } else if (roleAnggota === 'dosen') {
        addPendidikan();
    }
    updateDeleteButtons('pendidikan');
    
    const matakuliahContainer = document.getElementById('matakuliahContainer');
    matakuliahContainer.innerHTML = '';
    let matakuliahData = [];
    if (data.bidang_keahlian) {
        try {
            matakuliahData = typeof data.bidang_keahlian === 'string' ? JSON.parse(data.bidang_keahlian) : data.bidang_keahlian;
        } catch (e) { console.error('Error parsing bidang_keahlian:', e); }
    }
    
    if (matakuliahData.length > 0) {
        matakuliahData.forEach(item => {
            const newItem = document.createElement('div');
            newItem.className = 'matakuliah-item border rounded p-3 mb-3 bg-light';
            newItem.innerHTML = `
                <div class="row align-items-end">
                    <div class="col-auto mb-2"><label class="form-label small d-block">&nbsp;</label><button type="button" class="btn btn-sm btn-danger" onclick="removeMatakuliah(this)"><i class="bi bi-trash"></i></button></div>
                    <div class="col mb-2"><label class="form-label small">Nama Mata Kuliah *</label><input type="text" class="form-control form-control-sm" name="matakuliah_nama[]" value="${item.nama || ''}"></div>
                </div>
            `;
            matakuliahContainer.appendChild(newItem);
        });
    } else if (roleAnggota === 'dosen') {
        addMatakuliah();
    }
    updateDeleteButtons('matakuliah');
    
    new bootstrap.Modal(document.getElementById('anggotaModal')).show();
}
</script>

<?php include "footer.php"; ?>