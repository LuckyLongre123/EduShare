<?php
session_start();
require_once '../db/config.php';

// Check if user is logged in and is admin
if (!is_logged_in() || $_SESSION['user_role'] !== 'admin') {
    redirect('../index.php');
}

$success_message = '';
$error_message = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete_material':
                $material_id = (int)$_POST['material_id'];
                // Get file path before deletion
                $file_stmt = $conn->prepare("SELECT file_path FROM materials WHERE material_id = ?");
                $file_stmt->bind_param("i", $material_id);
                $file_stmt->execute();
                $file_result = $file_stmt->get_result();
                if ($file_result->num_rows > 0) {
                    $file_data = $file_result->fetch_assoc();
                    $file_path = $file_data['file_path'];

                    // Delete from database
                    $delete_stmt = $conn->prepare("DELETE FROM materials WHERE material_id = ?");
                    $delete_stmt->bind_param("i", $material_id);
                    if ($delete_stmt->execute()) {
                        // Delete physical file
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                        $success_message = "Material deleted successfully!";
                    }
                    $delete_stmt->close();
                }
                $file_stmt->close();
                break;

            case 'toggle_user_status':
                $user_id = (int)$_POST['user_id'];
                $new_status = $_POST['new_status'];
                $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
                $stmt->bind_param("si", $new_status, $user_id);
                if ($stmt->execute()) {
                    $success_message = "User status updated successfully!";
                }
                $stmt->close();
                break;

            case 'change_user_role':
                $user_id = (int)$_POST['user_id'];
                $new_role = $_POST['new_role'];
                $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
                $stmt->bind_param("si", $new_role, $user_id);
                if ($stmt->execute()) {
                    $success_message = "User role updated successfully!";
                }
                $stmt->close();
                break;

            case 'approve_material':
                $material_id = (int)$_POST['material_id'];
                $stmt = $conn->prepare("UPDATE materials SET status = 'approved' WHERE material_id = ?");
                $stmt->bind_param("i", $material_id);
                if ($stmt->execute()) {
                    $success_message = "Material approved successfully!";
                }
                $stmt->close();
                break;

            case 'reject_material':
                $material_id = (int)$_POST['material_id'];
                $stmt = $conn->prepare("UPDATE materials SET status = 'rejected' WHERE material_id = ?");
                $stmt->bind_param("i", $material_id);
                if ($stmt->execute()) {
                    $success_message = "Material rejected successfully!";
                }
                $stmt->close();
                break;

            case 'change_material_status':
                $material_id = (int)$_POST['material_id'];
                $new_status = $_POST['new_status'];
                $stmt = $conn->prepare("UPDATE materials SET status = ? WHERE material_id = ?");
                $stmt->bind_param("si", $new_status, $material_id);
                if ($stmt->execute()) {
                    $success_message = "Material status updated successfully!";
                }
                $stmt->close();
                break;

            case 'delete_message':
                $message_id = (int)$_POST['message_id'];
                $stmt = $conn->prepare("DELETE FROM contact_messages WHERE message_id = ?");
                $stmt->bind_param("i", $message_id);
                if ($stmt->execute()) {
                    $success_message = "Message deleted successfully!";
                }
                $stmt->close();
                break;

            case 'mark_as_read':
                $message_id = (int)$_POST['message_id'];
                $stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE message_id = ?");
                $stmt->bind_param("i", $message_id);
                if ($stmt->execute()) {
                    $success_message = "Message marked as read!";
                }
                $stmt->close();
                break;

            case 'mark_as_unread':
                $message_id = (int)$_POST['message_id'];
                $stmt = $conn->prepare("UPDATE contact_messages SET status = 'unread' WHERE message_id = ?");
                $stmt->bind_param("i", $message_id);
                if ($stmt->execute()) {
                    $success_message = "Message marked as unread!";
                }
                $stmt->close();
                break;
        }
    }
}

// Get statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $result->fetch_assoc()['count'];

// Total materials
$result = $conn->query("SELECT COUNT(*) as count FROM materials");
$stats['total_materials'] = $result->fetch_assoc()['count'];

// Pending materials
$result = $conn->query("SELECT COUNT(*) as count FROM materials WHERE status = 'pending'");
$stats['pending_materials'] = $result->fetch_assoc()['count'];

// Approved materials
$result = $conn->query("SELECT COUNT(*) as count FROM materials WHERE status = 'approved'");
$stats['approved_materials'] = $result->fetch_assoc()['count'];

// Rejected materials
$result = $conn->query("SELECT COUNT(*) as count FROM materials WHERE status = 'rejected'");
$stats['rejected_materials'] = $result->fetch_assoc()['count'];

// Total downloads
$result = $conn->query("SELECT SUM(downloads) as total FROM materials");
$stats['total_downloads'] = $result->fetch_assoc()['total'] ?: 0;

// Total likes
$result = $conn->query("SELECT COUNT(*) as count FROM likes");
$stats['total_likes'] = $result->fetch_assoc()['count'];

// Total comments
$result = $conn->query("SELECT COUNT(*) as count FROM comments");
$stats['total_comments'] = $result->fetch_assoc()['count'];

// Get pending materials
$pending_query = "
    SELECT m.*, u.name as uploader_name, c.college_name 
    FROM materials m 
    LEFT JOIN users u ON m.uploaded_by = u.user_id 
    LEFT JOIN colleges c ON m.college_id = c.college_id 
    WHERE m.status = 'pending' 
    ORDER BY m.upload_date DESC
";
$pending_materials = $conn->query($pending_query);

// Get approved materials
$approved_query = "
    SELECT m.*, u.name as uploader_name, c.college_name 
    FROM materials m 
    LEFT JOIN users u ON m.uploaded_by = u.user_id 
    LEFT JOIN colleges c ON m.college_id = c.college_id 
    WHERE m.status = 'approved' 
    ORDER BY m.upload_date DESC
    LIMIT 10
";
$approved_materials = $conn->query($approved_query);

// Get rejected materials
$rejected_query = "
    SELECT m.*, u.name as uploader_name, c.college_name 
    FROM materials m 
    LEFT JOIN users u ON m.uploaded_by = u.user_id 
    LEFT JOIN colleges c ON m.college_id = c.college_id 
    WHERE m.status = 'rejected' 
    ORDER BY m.upload_date DESC
    LIMIT 10
";
$rejected_materials = $conn->query($rejected_query);

// Get recent users
$users_query = "
    SELECT u.*, c.college_name 
    FROM users u 
    LEFT JOIN colleges c ON u.college = c.college_id 
    WHERE u.role != 'admin' 
    ORDER BY u.joined_on DESC 
    LIMIT 10
";
$recent_users = $conn->query($users_query);

// Get contact messages statistics
$result = $conn->query("SELECT COUNT(*) as count FROM contact_messages");
$stats['total_messages'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
$stats['unread_messages'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'read'");
$stats['read_messages'] = $result->fetch_assoc()['count'];

// Get recent contact messages
$messages_query = "
    SELECT * FROM contact_messages 
    ORDER BY date DESC 
    LIMIT 10
";
$recent_messages = $conn->query($messages_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel - EduShare</title>
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

    .status-active {
        background-color: #2d7d46;
        color: #fff;
    }

    .status-inactive {
        background-color: #c53030;
        color: #fff;
    }

    .role-admin {
        background-color: #6c2bd9;
        color: #fff;
    }

    .role-teacher {
        background-color: #2b6ed9;
        color: #fff;
    }

    .role-student {
        background-color: #6c757d;
        color: #fff;
    }

    .card-stats {
        transition: transform 0.2s;
    }

    .card-stats:hover {
        transform: translateY(-5px);
    }

    .admin-badge {
        background: linear-gradient(45deg, #ff6b6b, #ee5a24);
        color: white;
    }

    .card-header-custom {
        border-bottom: 1px solid #444;
    }

    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
    }

    .nav-tabs .nav-link {
        color: #adb5bd;
        border: none;
    }

    .nav-tabs .nav-link.active {
        color: #fff;
        background: transparent;
        border-bottom: 2px solid #0d6efd;
    }

    .material-tab-content {
        padding: 15px;
        border-left: 1px solid #444;
        border-right: 1px solid #444;
        border-bottom: 1px solid #444;
        border-radius: 0 0 5px 5px;
    }

    .status-unread {
        background-color: #c05621;
        color: #fff;
    }

    .status-read {
        background-color: #2d7d46;
        color: #fff;
    }

    .message-preview {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    </style>
</head>

<body class="bg-dark text-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">EduShare</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="./dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="./admin.php">Admin Panel</a>
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
                        <a class="btn btn-outline-light btn-sm dropdown-toggle" href="#" role="button"
                            data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i>
                            <span
                                class="ms-1"><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User'; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end bg-dark">
                            <li><a class="dropdown-item text-light" href="dashboard.php"><i
                                        class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item text-light" href="upload-notes.php"><i
                                        class="fas fa-upload me-2"></i>Upload Notes</a></li>
                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                            <li><a class="dropdown-item text-light active" href="admin.php"><i
                                        class="fas fa-cog me-2"></i>Admin Panel</a></li>
                            <?php endif; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="./logout.php"><i
                                        class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Admin Panel <span class="badge admin-badge ms-2">Admin Access</span></h2>
                <p class="text-secondary">Manage users, materials, and system settings</p>
            </div>
            <div>
                <span class="text-warning">Welcome, <?php echo $_SESSION['user_name']; ?></span>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <h4>System Statistics</h4>
        <div class="row text-center my-4 g-3">
            <div class="col-md-2 col-sm-4">
                <div class="card bg-dark text-light border-secondary p-3 card-stats">
                    <i class="fas fa-users fs-1 mb-2 text-primary"></i>
                    <h3><?php echo number_format($stats['total_users']); ?></h3>
                    <p class="mb-0">Total Users</p>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="card bg-dark text-light border-secondary p-3 card-stats">
                    <i class="fas fa-file-alt fs-1 mb-2 text-success"></i>
                    <h3><?php echo number_format($stats['total_materials']); ?></h3>
                    <p class="mb-0">Total Materials</p>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="card bg-dark text-light border-secondary p-3 card-stats">
                    <i class="fas fa-clock fs-1 mb-2 text-warning"></i>
                    <h3><?php echo number_format($stats['pending_materials']); ?></h3>
                    <p class="mb-0">Pending Approval</p>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="card bg-dark text-light border-secondary p-3 card-stats">
                    <i class="fas fa-check-circle fs-1 mb-2 text-info"></i>
                    <h3><?php echo number_format($stats['approved_materials']); ?></h3>
                    <p class="mb-0">Approved</p>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="card bg-dark text-light border-secondary p-3 card-stats">
                    <i class="fas fa-times-circle fs-1 mb-2 text-danger"></i>
                    <h3><?php echo number_format($stats['rejected_materials']); ?></h3>
                    <p class="mb-0">Rejected</p>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="card bg-dark text-light border-secondary p-3 card-stats">
                    <i class="fas fa-download fs-1 mb-2 text-secondary"></i>
                    <h3><?php echo number_format($stats['total_downloads']); ?></h3>
                    <p class="mb-0">Total Downloads</p>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="card bg-dark text-light border-secondary p-3 card-stats">
                    <i class="fas fa-envelope fs-1 mb-2 text-info"></i>
                    <h3><?php echo number_format($stats['total_messages']); ?></h3>
                    <p class="mb-0">Total Messages</p>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="card bg-dark text-light border-secondary p-3 card-stats">
                    <i class="fas fa-envelope-open fs-1 mb-2 text-warning"></i>
                    <h3><?php echo number_format($stats['unread_messages']); ?></h3>
                    <p class="mb-0">Unread Messages</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Users -->
            <div class="col-lg-4 mb-4">
                <div class="card bg-dark border-secondary">
                    <div class="card-header
                     card-header-custom bg-dark     text-info">
                        <h6 class="mb-0"><i class="fas fa-user-plus me-2"></i>Recent Users</h6>
                    </div>
                    <div class="card-body ">
                        <?php if ($recent_users->num_rows > 0): ?>
                        <?php while ($user = $recent_users->fetch_assoc()): ?>
                        <div
                            class="d-flex justify-content-between align-items-center mb-3 pb-2 text-white border-bottom border-secondary">
                            <div>
                                <div class="fw-bold"><?php echo htmlspecialchars($user['name']); ?></div>
                                <small class="text-white">
                                    <?php echo htmlspecialchars($user['email']); ?><br>
                                    <span class="badge role-<?php echo $user['role']; ?> me-1">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                    <span class="badge status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </small>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                    data-bs-toggle="dropdown">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <ul class="dropdown-menu bg-dark">
                                    <li>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_user_status">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <input type="hidden" name="new_status"
                                                value="<?php echo $user['status'] == 'active' ? 'inactive' : 'active'; ?>">
                                            <button type="submit" class="dropdown-item text-light">
                                                <i
                                                    class="fas fa-<?php echo $user['status'] == 'active' ? 'ban' : 'check'; ?> me-2"></i>
                                                <?php echo $user['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="change_user_role">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <input type="hidden" name="new_role"
                                                value="<?php echo $user['role'] == 'student' ? 'teacher' : 'student'; ?>">
                                            <button type="submit" class="dropdown-item text-light">
                                                <i class="fas fa-exchange-alt me-2"></i>
                                                Make <?php echo $user['role'] == 'student' ? 'Teacher' : 'Student'; ?>
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <p class="text-white text-center">No users found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Materials Management -->
            <div class="col-lg-8 mb-4">
                <div class="card bg-dark border-secondary">
                    <div class="card-header card-header-custom bg-dark text-warning p-0">
                        <ul class="nav nav-tabs" id="materialsTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab"
                                    data-bs-target="#pending" type="button" role="tab">
                                    <i class="fas fa-clock me-1"></i>Pending
                                    <span
                                        class="badge bg-warning text-dark ms-1"><?php echo $stats['pending_materials']; ?></span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="approved-tab" data-bs-toggle="tab"
                                    data-bs-target="#approved" type="button" role="tab">
                                    <i class="fas fa-check-circle me-1"></i>Approved
                                    <span
                                        class="badge bg-success ms-1"><?php echo $stats['approved_materials']; ?></span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="rejected-tab" data-bs-toggle="tab"
                                    data-bs-target="#rejected" type="button" role="tab">
                                    <i class="fas fa-times-circle me-1"></i>Rejected
                                    <span
                                        class="badge bg-danger ms-1"><?php echo $stats['rejected_materials']; ?></span>
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content material-tab-content" id="materialsTabContent">
                            <!-- Pending Materials Tab -->
                            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                                <?php if ($pending_materials->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-dark table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Subject</th>
                                                <th>Uploader</th>
                                                <th>College</th>
                                                <th>Upload Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                if ($pending_materials->num_rows > 0) {
                                                    $pending_materials->data_seek(0);
                                                }
                                                while ($material = $pending_materials->fetch_assoc()):
                                                    $clean_description = htmlspecialchars($material['description']);
                                                    $truncated_description = strlen($clean_description) > 50
                                                        ? substr($clean_description, 0, 50) . '...'
                                                        : $clean_description;
                                                ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">
                                                        <?php echo htmlspecialchars($material['title']); ?>
                                                    </div>
                                                    <small class="text-white" title="<?php echo $clean_description; ?>">
                                                        <?php echo $truncated_description; ?>
                                                    </small>
                                                </td>
                                                <td><span
                                                        class="badge bg-primary"><?php echo htmlspecialchars($material['subject']); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($material['uploader_name']); ?></td>
                                                <td><?php echo htmlspecialchars($material['college_name']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($material['upload_date'])); ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="../auth/admin-view-note.php?id=<?php echo $material['material_id']; ?>"
                                                            class="btn btn-outline-info" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="approve_material">
                                                            <input type="hidden" name="material_id"
                                                                value="<?php echo $material['material_id']; ?>">
                                                            <button type="submit" class="btn btn-outline-success"
                                                                title="Approve">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>

                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="reject_material">
                                                            <input type="hidden" name="material_id"
                                                                value="<?php echo $material['material_id']; ?>">
                                                            <button type="submit" class="btn btn-outline-danger"
                                                                title="Reject">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>

                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="text-center text-white py-4">
                                    <i class="fas fa-check-circle fs-1 mb-3"></i>
                                    <p>No pending materials. All caught up!</p>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Approved Materials Tab -->
                            <div class="tab-pane fade" id="approved" role="tabpanel">
                                <?php if ($approved_materials->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-dark table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Subject</th>
                                                <th>Uploader</th>
                                                <th>College</th>
                                                <th>Upload Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($material = $approved_materials->fetch_assoc()):
                                                    $clean_description = htmlspecialchars($material['description']);
                                                    $truncated_description = strlen($clean_description) > 50
                                                        ? substr($clean_description, 0, 50) . '...'
                                                        : $clean_description;
                                                ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">
                                                        <?php echo htmlspecialchars($material['title']); ?>
                                                    </div>
                                                    <small class="text-white" title="<?php echo $clean_description; ?>">
                                                        <?php echo $truncated_description; ?>
                                                    </small>
                                                </td>
                                                <td><span
                                                        class="badge bg-primary"><?php echo htmlspecialchars($material['subject']); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($material['uploader_name']); ?></td>
                                                <td><?php echo htmlspecialchars($material['college_name']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($material['upload_date'])); ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="../auth/admin-view-note.php?id=<?php echo $material['material_id']; ?>"
                                                            class="btn btn-outline-info" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <div class="dropdown">
                                                            <button class="btn btn-outline-secondary dropdown-toggle"
                                                                data-bs-toggle="dropdown" title="Change Status">
                                                                <i class="fas fa-exchange-alt"></i>
                                                            </button>
                                                            <ul class="dropdown-menu bg-dark">
                                                                <li>
                                                                    <form method="POST" class="d-inline">
                                                                        <input type="hidden" name="action"
                                                                            value="change_material_status">
                                                                        <input type="hidden" name="material_id"
                                                                            value="<?php echo $material['material_id']; ?>">
                                                                        <input type="hidden" name="new_status"
                                                                            value="pending">
                                                                        <button type="submit"
                                                                            class="dropdown-item text-light">
                                                                            <i class="fas fa-clock me-2"></i>Mark as
                                                                            Pending
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form method="POST" class="d-inline">
                                                                        <input type="hidden" name="action"
                                                                            value="change_material_status">
                                                                        <input type="hidden" name="material_id"
                                                                            value="<?php echo $material['material_id']; ?>">
                                                                        <input type="hidden" name="new_status"
                                                                            value="rejected">
                                                                        <button type="submit"
                                                                            class="dropdown-item text-light">
                                                                            <i class="fas fa-times me-2"></i>Mark as
                                                                            Rejected
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <form method="POST" class="d-inline"
                                                            onsubmit="return confirm('Are you sure you want to delete this material?')">
                                                            <input type="hidden" name="action" value="delete_material">
                                                            <input type="hidden" name="material_id"
                                                                value="<?php echo $material['material_id']; ?>">
                                                            <button type="submit" class="btn btn-outline-danger"
                                                                title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="text-center text-white py-4">
                                    <i class="fas fa-check-circle fs-1 mb-3"></i>
                                    <p>No approved materials found.</p>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Rejected Materials Tab -->
                            <div class="tab-pane fade" id="rejected" role="tabpanel">
                                <?php if ($rejected_materials->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-dark table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Subject</th>
                                                <th>Uploader</th>
                                                <th>College</th>
                                                <th>Upload Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($material = $rejected_materials->fetch_assoc()):
                                                    $clean_description = htmlspecialchars($material['description']);
                                                    $truncated_description = strlen($clean_description) > 50
                                                        ? substr($clean_description, 0, 50) . '...'
                                                        : $clean_description;
                                                ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">
                                                        <?php echo htmlspecialchars($material['title']); ?>
                                                    </div>
                                                    <small class="text-white" title="<?php echo $clean_description; ?>">
                                                        <?php echo $truncated_description; ?>
                                                    </small>
                                                </td>
                                                <td><span
                                                        class="badge bg-primary"><?php echo htmlspecialchars($material['subject']); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($material['uploader_name']); ?></td>
                                                <td><?php echo htmlspecialchars($material['college_name']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($material['upload_date'])); ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="../auth/admin-view-note.php?id=<?php echo $material['material_id']; ?>"
                                                            class="btn btn-outline-info" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <div class="dropdown">
                                                            <button class="btn btn-outline-secondary dropdown-toggle"
                                                                data-bs-toggle="dropdown" title="Change Status">
                                                                <i class="fas fa-exchange-alt"></i>
                                                            </button>
                                                            <ul class="dropdown-menu bg-dark">
                                                                <li>
                                                                    <form method="POST" class="d-inline">
                                                                        <input type="hidden" name="action"
                                                                            value="change_material_status">
                                                                        <input type="hidden" name="material_id"
                                                                            value="<?php echo $material['material_id']; ?>">
                                                                        <input type="hidden" name="new_status"
                                                                            value="pending">
                                                                        <button type="submit"
                                                                            class="dropdown-item text-light">
                                                                            <i class="fas fa-clock me-2"></i>Mark as
                                                                            Pending
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form method="POST" class="d-inline">
                                                                        <input type="hidden" name="action"
                                                                            value="change_material_status">
                                                                        <input type="hidden" name="material_id"
                                                                            value="<?php echo $material['material_id']; ?>">
                                                                        <input type="hidden" name="new_status"
                                                                            value="approved">
                                                                        <button type="submit"
                                                                            class="dropdown-item text-light">
                                                                            <i class="fas fa-check me-2"></i>Mark as
                                                                            Approved
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <form method="POST" class="d-inline"
                                                            onsubmit="return confirm('Are you sure you want to delete this material?')">
                                                            <input type="hidden" name="action" value="delete_material">
                                                            <input type="hidden" name="material_id"
                                                                value="<?php echo $material['material_id']; ?>">
                                                            <button type="submit" class="btn btn-outline-danger"
                                                                title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="text-center text-white py-4">
                                    <i class="fas fa-times-circle fs-1 mb-3"></i>
                                    <p>No rejected materials found.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Messages Section -->
            <div class="col-12 mb-4">
                <div class="card bg-dark border-secondary">
                    <div class="card-header card-header-custom bg-dark text-info">
                        <h6 class="mb-0"><i class="fas fa-envelope me-2"></i>Contact Messages</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($recent_messages->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Subject</th>
                                        <th>Message</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($message = $recent_messages->fetch_assoc()):
                                            $message_preview = strlen($message['message']) > 50
                                                ? substr($message['message'], 0, 50) . '...'
                                                : $message['message'];
                                        ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                                        <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                        <td class="message-preview"
                                            title="<?php echo htmlspecialchars($message['message']); ?>">
                                            <?php echo htmlspecialchars($message_preview); ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($message['date'])); ?></td>
                                        <td>
                                            <span class="badge status-<?php echo $message['status']; ?>">
                                                <?php echo ucfirst($message['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-info view-message"
                                                    data-bs-toggle="modal" data-bs-target="#messageModal"
                                                    data-name="<?php echo htmlspecialchars($message['name']); ?>"
                                                    data-email="<?php echo htmlspecialchars($message['email']); ?>"
                                                    data-subject="<?php echo htmlspecialchars($message['subject']); ?>"
                                                    data-message="<?php echo htmlspecialchars($message['message']); ?>"
                                                    data-date="<?php echo date('M j, Y g:i A', strtotime($message['date'])); ?>"
                                                    data-status="<?php echo $message['status']; ?>"
                                                    data-id="<?php echo $message['message_id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($message['status'] == 'unread'): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="mark_as_read">
                                                    <input type="hidden" name="message_id"
                                                        value="<?php echo $message['message_id']; ?>">
                                                    <button type="submit" class="btn btn-outline-success"
                                                        title="Mark as Read">
                                                        <i class="fas fa-envelope-open"></i>
                                                    </button>
                                                </form>
                                                <?php else: ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="mark_as_unread">
                                                    <input type="hidden" name="message_id"
                                                        value="<?php echo $message['message_id']; ?>">
                                                    <button type="submit" class="btn btn-outline-warning"
                                                        title="Mark as Unread">
                                                        <i class="fas fa-envelope"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this message?')">
                                                    <input type="hidden" name="action" value="delete_message">
                                                    <input type="hidden" name="message_id"
                                                        value="<?php echo $message['message_id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-white text-center">No contact messages found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-dark text-light">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title">Message Details</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Name:</strong> <span id="modal-name"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Email:</strong> <span id="modal-email"></span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Date:</strong> <span id="modal-date"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong> <span id="modal-status"></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <strong>Subject:</strong> <span id="modal-subject"></span>
                        </div>
                        <div class="mb-3">
                            <strong>Message:</strong>
                            <div class="border border-secondary p-3 mt-2 rounded" id="modal-message"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <form method="POST" id="modal-status-form" class="me-auto">
                            <input type="hidden" name="message_id" id="modal-message-id">
                            <input type="hidden" name="action" id="modal-status-action" value="mark_as_read">
                            <button type="submit" class="btn btn-outline-primary" id="modal-status-button">
                                <i class="fas fa-envelope-open me-1"></i> Mark as Read
                            </button>
                        </form>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this message?')">
                            <input type="hidden" name="action" value="delete_message">
                            <input type="hidden" name="message_id" id="modal-delete-id">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-1"></i> Delete
                            </button>
                        </form>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Message modal functionality
    document.querySelectorAll('.view-message').forEach(button => {
        button.addEventListener('click', function() {
            const name = this.getAttribute('data-name');
            const email = this.getAttribute('data-email');
            const subject = this.getAttribute('data-subject');
            const message = this.getAttribute('data-message');
            const date = this.getAttribute('data-date');
            const status = this.getAttribute('data-status');
            const id = this.getAttribute('data-id');

            document.getElementById('modal-name').textContent = name;
            document.getElementById('modal-email').textContent = email;
            document.getElementById('modal-subject').textContent = subject;
            document.getElementById('modal-message').textContent = message;
            document.getElementById('modal-date').textContent = date;
            document.getElementById('modal-status').textContent = status.charAt(0).toUpperCase() +
                status.slice(1);
            document.getElementById('modal-message-id').value = id;
            document.getElementById('modal-delete-id').value = id;

            // Update status form based on current status
            const statusForm = document.getElementById('modal-status-form');
            const statusAction = document.getElementById('modal-status-action');
            const statusButton = document.getElementById('modal-status-button');

            if (status === 'unread') {
                statusAction.value = 'mark_as_read';
                statusButton.innerHTML = '<i class="fas fa-envelope-open me-1"></i> Mark as Read';
                statusButton.className = 'btn btn-outline-primary';
            } else {
                statusAction.value = 'mark_as_unread';
                statusButton.innerHTML = '<i class="fas fa-envelope me-1"></i> Mark as Unread';
                statusButton.className = 'btn btn-outline-warning';
            }
        });
    });
    </script>
</body>

</html>