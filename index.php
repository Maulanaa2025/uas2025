<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'uas2025');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information System</title>
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
            max-width: 1200px;
            margin: 0 auto;
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
        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .search-box input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button, .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #004754;
            color: white;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .student-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-edit {
            background: #bebd00;
            color: #333;
        }
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
        }
        @media (max-width: 768px) {
            nav ul {
                flex-direction: column;
                gap: 5px;
            }
            table {
                display: block;
                overflow-x: auto;
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
            <h2><i class="fas fa-users"></i> Student List</h2>
            
            <form method="GET" action="" class="search-box">
                <input type="text" name="search" placeholder="Search students..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
                <?php if (!empty($search)): ?>
                    <a href="index.php" class="btn btn-delete">
                        <i class="fas fa-times"></i> Reset
                    </a>
                <?php endif; ?>
            </form>
            
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Photo</th>
                        <th>NPM</th>
                        <th>Name</th>
                        <th>Program</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $query = "SELECT * FROM mahasiswa";
                    if (!empty($search)) {
                        $query .= " WHERE npm LIKE '%$search%' OR 
                                    nama LIKE '%$search%' OR 
                                    program_studi LIKE '%$search%' OR 
                                    email LIKE '%$search%' OR 
                                    alamat LIKE '%$search%'";
                    }
                    
                    $result = mysqli_query($conn, $query);
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td>
                            <img src="<?= !empty($row['foto']) ? 'uploads/'.htmlspecialchars($row['foto']) : 'assets/default-profile.png' ?>" 
                                 alt="Photo of <?= htmlspecialchars($row['nama']) ?>" class="student-photo">
                        </td>
                        <td><?= htmlspecialchars($row['npm']); ?></td>
                        <td><?= htmlspecialchars($row['nama']); ?></td>
                        <td><?= htmlspecialchars($row['program_studi']); ?></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete.php?id=<?= $row['id']; ?>" class="btn btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this?')">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>

        <footer>
            <p>&copy; <?= date('Y'); ?> Student Information System</p>
        </footer>
    </div>
</body>
</html>

<?php mysqli_close($conn); ?>