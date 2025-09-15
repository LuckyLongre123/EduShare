<?php
session_start();
require_once '../db/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('../pages/login.php');
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_note'])) {
    $material_id = intval($_POST['material_id']);
    $user_id = $_SESSION['user_id'];
    
    // Verify user owns the note before deleting
    $verify_query = "SELECT uploaded_by FROM materials WHERE material_id = ?";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bind_param("i", $material_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows > 0) {
        $material = $verify_result->fetch_assoc();
        
        if ($material['uploaded_by'] == $user_id || $user_role == 'admin') {
            // Delete the note
            $delete_query = "DELETE FROM materials WHERE material_id = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("i", $material_id);
            
            if ($delete_stmt->execute()) {
                $_SESSION['success_message'] = "Note deleted successfully!";
                // Refresh the page to show updated list
                header("Location: dashboard.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Error deleting note: " . $conn->error;
            }
        } else {
            $_SESSION['error_message'] = "You don't have permission to delete this note.";
        }
    } else {
        $_SESSION['error_message'] = "Note not found.";
    }
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['email'];

// Get user statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM materials WHERE uploaded_by = ? AND status = 'approved') as approved_notes,
    (SELECT COUNT(*) FROM materials WHERE uploaded_by = ? AND status = 'pending') as pending_notes,
    (SELECT COUNT(*) FROM materials WHERE uploaded_by = ? AND status = 'rejected') as rejected_notes,
    (SELECT SUM(downloads) FROM materials WHERE uploaded_by = ? AND status = 'approved') as total_downloads,
    (SELECT COUNT(*) FROM likes l JOIN materials m ON l.material_id = m.material_id WHERE m.uploaded_by = ?) as total_likes";

$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Calculate total notes
$notes_count = ($stats['approved_notes'] ?? 0) + ($stats['pending_notes'] ?? 0) + ($stats['rejected_notes'] ?? 0);

// Get user's uploaded materials
$materials_query = "SELECT m.*, 
    (SELECT COUNT(*) FROM likes l WHERE l.material_id = m.material_id) as like_count,
    (SELECT COUNT(*) FROM comments c WHERE c.material_id = m.material_id) as comment_count
    FROM materials m 
    WHERE m.uploaded_by = ? 
    ORDER BY m.upload_date DESC";

$materials_stmt = $conn->prepare($materials_query);
$materials_stmt->bind_param("i", $user_id);
$materials_stmt->execute();
$materials_result = $materials_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - EduShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Dropdown dark theme override */
        .dropdown-menu.bg-dark .dropdown-item {
            color: #e0e0e0;
        }

        .dropdown-menu.bg-dark .dropdown-item:hover,
        .dropdown-menu.bg-dark .dropdown-item:focus {
            background-color: #333 !important;
            color: #fff !important;
        }

        .dropdown-menu.bg-dark .dropdown-divider {
            border-color: #555;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .status-approved {
            background-color: #2d7d46;
            color: #fff;
        }

        .status-pending {
            background-color: #c05621;
            color: #fff;
        }

        .status-rejected {
            background-color: #c53030;
            color: #fff;
        }
        
        /* Toast notification styling */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1055;
        }
        
        .toast {
            background-color: #1a1a1a;
            border: 1px solid #444;
        }
    </style>
</head>

<body class="bg-dark text-light">

    <!-- Toast notifications -->
    <div class="toast-container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="toast align-items-center text-white bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">EduShare</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="./dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/browse.php">Browse Notes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/contact.php">Contact</a>
                    </li>
                </ul>

                <!-- Auth Section -->
                <div class="d-flex gap-2">
                    <?php if (is_logged_in()): ?>
                        <div class="nav-item dropdown">
                            <a class="btn btn-outline-light btn-sm dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i>
                                <span class="ms-1"><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User'; ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item" href="upload-notes.php"><i class="fas fa-upload me-2"></i>Upload Notes</a></li>
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                                    <li><a class="dropdown-item" href="admin.php"><i class="fas fa-cog me-2"></i>Admin Panel</a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="./logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="pages/login.php" class="btn btn-outline-light btn-sm">Login</a>
                        <a href="pages/register.php" class="btn btn-primary btn-sm">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <!-- Dashboard Header -->
    <div class="container my-4">
        <h2>Welcome back, <?php echo $user_name; ?>!</h2>
        <p class="text-secondary">Dashboard - Manage your notes and profile</p>

        <!-- Quick Stats -->
        <div class="row text-center my-4 g-3">
            <div class="col-md-4">
                <div class="card bg-dark text-light border-secondary p-3">
                    <h3><?php echo $notes_count; ?></h3>
                    <p>Notes</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-dark text-light border-secondary p-3">
                    <h3><?php echo $stats['total_downloads'] ?? 0; ?></h3>
                    <p>Downloads</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-dark text-light border-secondary p-3">
                    <h3><?php echo $stats['total_likes'] ?? 0; ?></h3>
                    <p>Likes</p>
                </div>
            </div>
        </div>

        <!-- Your Statistics -->
        <h4>Your Statistics</h4>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card bg-dark text-light border-secondary p-3">
                    <p class="mb-1">Approved Notes</p>
                    <h5><?php echo $stats['approved_notes'] ?? 0; ?></h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-dark text-light border-secondary p-3">
                    <p class="mb-1">Pending Approval</p>
                    <h5><?php echo $stats['pending_notes'] ?? 0; ?></h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-dark text-light border-secondary p-3">
                    <p class="mb-1">Total Downloads</p>
                    <h5><?php echo $stats['total_downloads'] ?? 0; ?></h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-dark text-light border-secondary p-3">
                    <p class="mb-1">Total Likes</p>
                    <h5><?php echo $stats['total_likes'] ?? 0; ?></h5>
                </div>
            </div>
        </div>

        <!-- My Uploads -->
        <h4>My Uploads</h4>
        <?php if ($materials_result->num_rows > 0): ?>
            <div class="table-responsive mb-4">
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Downloads</th>
                            <th>Likes</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($material = $materials_result->fetch_assoc()): ?>
                            <tr id="note-<?php echo $material['material_id']; ?>">
                                <td><?php echo htmlspecialchars($material['title']); ?></td>
                                <td><?php echo htmlspecialchars($material['subject']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $material['status']; ?>">
                                        <?php echo ucfirst($material['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $material['downloads']; ?></td>
                                <td><?php echo $material['like_count']; ?></td>
                                <td><?php echo date('M j, Y', strtotime($material['upload_date'])); ?></td>
                                <td>
                                    <?php if ($material['status'] == 'approved'): ?>
                                        <a href="../pages/note-details.php?id=<?php echo $material['material_id']; ?>"
                                            class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="material_id" value="<?php echo $material['material_id']; ?>">
                                        <button type="submit" name="delete_note" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Are you sure you want to delete this note? This action cannot be undone.')"
                                            title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <p>No uploads yet. Start sharing your knowledge with the community!</p>
                <a href="./upload-notes.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Upload Your First Note
                </a>
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <h4>Quick Actions</h4>
        <div class="d-flex gap-2 flex-wrap mb-5">
            <a href="../auth/upload-notes.php" class="btn btn-primary">Upload Notes</a>
            <a href="../pages/browse.php" class="btn btn-outline-light">Browse Notes</a>
            <a href="../pages/contact.php" class="btn btn-outline-light">Contact Support</a>
            <a href="./profile.php" class="btn btn-outline-light">Profile Settings</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide toast notifications after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                setTimeout(() => {
                    const bsToast = new bootstrap.Toast(toast);
                    bsToast.hide();
                }, 5000);
            });
        });
    </script>
</body>

</html>