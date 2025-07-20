<?php 
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'uas2025');
if (!$conn) die("Connection failed: " . mysqli_connect_error());

// Get student data by ID
$id = $_GET['id'];
$query = "SELECT * FROM mahasiswa WHERE id = $id";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Escape inputs
    $npm = mysqli_real_escape_string($conn, $_POST['npm']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $program_studi = mysqli_real_escape_string($conn, $_POST['program_studi']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $current_foto = $row['foto'];
    $foto = $current_foto;

    // Handle file upload
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        // Delete old photo if exists
        if (!empty($current_foto) && file_exists($target_dir . $current_foto)) {
            unlink($target_dir . $current_foto);
        }

        $file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('foto_', true) . '.' . $file_ext;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                $foto = $new_filename;
            }
        }
    }

    // Handle remove photo request
    if (isset($_POST['remove_foto']) && $_POST['remove_foto'] == '1') {
        if (!empty($current_foto) && file_exists($target_dir . $current_foto)) {
            unlink($target_dir . $current_foto);
        }
        $foto = '';
    }

    // Update student data
    $query = "UPDATE mahasiswa SET 
              npm = '$npm', 
              nama = '$nama', 
              program_studi = '$program_studi', 
              email = '$email', 
              alamat = '$alamat', 
              foto = '$foto' 
              WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        // Sync email in users table
        $updateUser = "UPDATE users SET email = '$email' WHERE idMhs = $id";
        mysqli_query($conn, $updateUser);

        header("Location: index.php");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        header {
            background: #004754;
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            margin: -20px -20px 20px -20px;
        }
        h1, h2 {
            margin: 0 0 15px 0;
        }
        nav ul {
            padding: 0;
            list-style: none;
            display: flex;
            gap: 15px;
        }
        nav a {
            color: white;
            text-decoration: none;
        }
        .logout-btn {
            float: right;
            color: white;
            text-decoration: none;
        }
        .error-message {
            background: #ffecec;
            color: #e74c3c;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            border-left: 3px solid #e74c3c;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
        }
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        .photo-upload {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        .photo-preview {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ddd;
        }
        .file-label {
            display: inline-block;
            padding: 8px 12px;
            background: #bebd00;
            color: #333;
            border-radius: 4px;
            cursor: pointer;
        }
        .file-input {
            display: none;
        }
        .remove-photo {
            display: inline-block;
            padding: 8px 12px;
            background: #e74c3c;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            margin-left: 10px;
        }
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-submit {
            background: #bebd00;
            color: #333;
        }
        .btn-cancel {
            background: #95a5a6;
            color: white;
        }
        footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
        }
        @media (max-width: 600px) {
            .photo-upload {
                flex-direction: column;
                align-items: flex-start;
            }
            .form-actions {
                flex-direction: column;
            }
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Student Information System</h1>
            <a href="login.php?action=logout" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="create.php"><i class="fas fa-user-plus"></i> Add Student</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <h2><i class="fas fa-user-edit"></i> Edit Student</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $row['id']; ?>">
                
                <!-- Photo Upload Section -->
                <div class="photo-upload">
                    <img src="<?= !empty($row['foto']) ? 'uploads/'.htmlspecialchars($row['foto']) : 'assets/default-profile.png' ?>" 
                         alt="Student Photo" class="photo-preview" id="photoPreview">
                    
                    <div>
                        <input type="file" id="foto" name="foto" class="file-input" accept="image/*">
                        <label for="foto" class="file-label">
                            <i class="fas fa-camera"></i> Choose Photo
                        </label>
                        <?php if (!empty($row['foto'])): ?>
                            <button type="button" class="remove-photo" id="removePhoto">
                                <i class="fas fa-trash"></i> Remove Photo
                            </button>
                            <input type="hidden" name="remove_foto" id="removeFotoInput" value="0">
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="npm"><i class="fas fa-id-card"></i> Student ID</label>
                    <input type="text" id="npm" name="npm" value="<?= htmlspecialchars($row['npm']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="nama"><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($row['nama']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="program_studi"><i class="fas fa-graduation-cap"></i> Study Program</label>
                    <select id="program_studi" name="program_studi" required>
                        <option value="SI" <?= $row['program_studi'] == 'SI' ? 'selected' : '' ?>>Information Systems</option>
                        <option value="TI" <?= $row['program_studi'] == 'TI' ? 'selected' : '' ?>>Informatics Engineering</option>
                        <option value="RPL" <?= $row['program_studi'] == 'RPL' ? 'selected' : '' ?>>Software Engineering</option>
                        <option value="MI" <?= $row['program_studi'] == 'MI' ? 'selected' : '' ?>>Informatics Management</option>
                        <option value="PI" <?= $row['program_studi'] == 'PI' ? 'selected' : '' ?>>Informatics Education</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($row['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="alamat"><i class="fas fa-map-marker-alt"></i> Address</label>
                    <textarea id="alamat" name="alamat" required><?= htmlspecialchars($row['alamat']); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="index.php" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </main>

        <footer>
            <p>&copy; <?= date('Y'); ?> Student Information System</p>
        </footer>
    </div>

    <script>
        // Photo preview
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photoPreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Remove photo
        document.getElementById('removePhoto')?.addEventListener('click', function() {
            document.getElementById('photoPreview').src = 'assets/default-profile.png';
            document.getElementById('removeFotoInput').value = '1';
            document.getElementById('foto').value = '';
        });
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>