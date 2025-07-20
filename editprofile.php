<?php
session_start();
require 'config.php';

// Cek login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Proses upload foto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto'])) {
    $target_dir = "uploads/profiles/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    $new_filename = 'profile_' . $user_id . '.' . $file_ext;
    $target_file = $target_dir . $new_filename;

    // Validasi file
    if (in_array($file_ext, $allowed_ext)) {
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
            try {
                // Update database
                $stmt = $pdo->prepare("REPLACE INTO foto (idUser, foto) VALUES (?, ?)");
                $stmt->execute([$user_id, $new_filename]);
                $success = "Foto profil berhasil diupdate!";
            } catch (PDOException $e) {
                $error = "Error database: " . $e->getMessage();
            }
        } else {
            $error = "Gagal mengupload file.";
        }
    } else {
        $error = "Hanya file JPG, JPEG, PNG, atau GIF yang diizinkan.";
    }
}

// Ambil foto profil saat ini
$current_foto = '';
try {
    $stmt = $pdo->prepare("SELECT foto FROM foto WHERE idUser = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    if ($result) {
        $current_foto = $result['foto'];
    }
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-user-edit me-2"></i>Edit Profil
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="text-center mb-4">
                                <img src="<?= $current_foto ? 'uploads/profiles/' . htmlspecialchars($current_foto) : 'assets/default-profile.png' ?>"
                                     class="rounded-circle img-thumbnail" 
                                     width="150" height="150" 
                                     id="previewFoto">
                            </div>

                            <div class="mb-3">
                                <label for="foto" class="form-label">Pilih Foto Baru</label>
                                <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Simpan
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Preview gambar sebelum upload
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewFoto').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>