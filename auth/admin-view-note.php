<?php
session_start();
require_once "../db/config.php";

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: ../index.php");
  exit;
}

// Get material ID
$material_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($material_id <= 0) {
  header("Location: ../pages/browse.php");
  exit;
}

// Handle comment deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_comment'])) {
  $comment_id = (int)$_POST['comment_id'];
  if ($comment_id > 0) {
    $delete_stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
    $delete_stmt->bind_param("i", $comment_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    // Redirect to prevent form resubmission
    header("Location: admin-view-note.php?id=$material_id");
    exit;
  }
}

// Fetch material details
$stmt = $conn->prepare("
    SELECT m.*, u.name as uploader_name, u.user_id as uploader_id, c.college_name 
    FROM materials m
    LEFT JOIN users u ON m.uploaded_by = u.user_id
    LEFT JOIN colleges c ON m.college_id = c.college_id
    WHERE m.material_id = ? AND (m.status = 'pending' OR m.status = 'rejected' OR m.status = 'approved')  
");
$stmt->bind_param("i", $material_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
  header("Location: ../pages/browse.php");
  exit;
}
$note = $result->fetch_assoc();
$uploader_id = $note['uploader_id'];
$stmt->close();

// Like count
$like_stmt = $conn->prepare("SELECT COUNT(*) as total FROM likes WHERE material_id = ?");
$like_stmt->bind_param("i", $material_id);
$like_stmt->execute();
$like_count = $like_stmt->get_result()->fetch_assoc()['total'];
$like_stmt->close();

// Get comments with user roles
$comments_stmt = $conn->prepare("
    SELECT c.*, u.name as commenter_name, u.role as commenter_role, u.user_id as commenter_id 
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.user_id 
    WHERE c.material_id = ? 
    ORDER BY c.comment_date DESC
");
$comments_stmt->bind_param("i", $material_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Note View - EduShare</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    rel="stylesheet" />
  <style>
    body {
      background-color: #121212;
      color: #f8f9fa;
    }

    .note-card {
      background-color: #1e1e1e;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      margin-bottom: 20px;
      overflow: hidden;
    }

    .note-header {
      background-color: #2c2c2c;
      padding: 15px 20px;
      border-bottom: 1px solid #333;
    }

    .note-body {
      padding: 20px;
    }

    .btn-primary {
      background-color: #0d6efd;
      border-color: #0d6efd;
    }

    .btn-primary:hover {
      background-color: #0b5ed7;
      border-color: #0a58ca;
    }

    .btn-outline-light:hover {
      background-color: rgba(255, 255, 255, 0.1);
    }

    .comment-section {
      margin-top: 30px;
    }

    .comment {
      background-color: #1e1e1e;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
      border-left: 3px solid #0d6efd;
      position: relative;
    }

    .comment-header {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      font-size: 0.9rem;
      color: #6c757d;
    }

    .commenter-name {
      font-weight: 600;
      color: #e9ecef;
    }

    .comment-form {
      margin-bottom: 30px;
    }

    .stats-badge {
      background-color: #2c2c2c;
      color: #e9ecef;
      padding: 5px 10px;
      border-radius: 4px;
      font-size: 0.85rem;
      display: inline-flex;
      align-items: center;
      margin-right: 10px;
    }

    .stats-badge i {
      margin-right: 5px;
    }

    .file-preview {
      background-color: #2c2c2c;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .role-badge {
      font-size: 0.7rem;
      padding: 3px 8px;
      border-radius: 12px;
      margin-left: 8px;
    }

    .role-admin {
      background-color: #dc3545;
      color: white;
    }

    .role-author {
      background-color: #0d6efd;
      color: white;
    }

    .role-teacher {
      background-color: #198754;
      color: white;
    }

    .role-student {
      background-color: #6c757d;
      color: white;
    }

    .delete-comment-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      background: transparent;
      border: none;
      color: #dc3545;
      cursor: pointer;
      opacity: 0.7;
      transition: opacity 0.3s;
    }

    .delete-comment-btn:hover {
      opacity: 1;
    }

    .admin-badge {
      background-color: #dc3545;
      color: white;
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 0.8rem;
      margin-left: 10px;
    }
  </style>
</head>

<body class="bg-dark text-light">
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
            <a class="nav-link active" href="./admin-view-note.php?id=<?php echo $material_id; ?>">Admin View</a>
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
                <li><a class="dropdown-item" href="../auth/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                <li><a class="dropdown-item" href="../auth/upload-notes.php"><i class="fas fa-upload me-2"></i>Upload Notes</a></li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                  <li><a class="dropdown-item" href="../auth/admin.php"><i class="fas fa-cog me-2"></i>Admin Panel</a></li>
                <?php endif; ?>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item text-danger" href="./logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
              </ul>
            </div>
          <?php else: ?>
            <a href="./login.php" class="btn btn-outline-light btn-sm">Login</a>
            <a href="./register.php" class="btn btn-primary btn-sm">Register</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>


  <!-- Note Details -->
  <main class="container my-4" style="max-width: 800px">
    <div class="note-card">
      <div class="note-header">
        <h2 class="mb-0"><?php echo htmlspecialchars($note['title']); ?></h2>
      </div>
      <div class="note-body">
        <div class="d-flex flex-wrap mb-3">
          <span class="stats-badge">
            <i class="fas fa-user"></i> <?php echo htmlspecialchars($note['uploader_name']); ?>
          </span>
          <span class="stats-badge">
            <i class="fas fa-university"></i> <?php echo htmlspecialchars($note['college_name']); ?>
          </span>
          <span class="stats-badge">
            <i class="fas fa-calendar"></i> Semester <?php echo $note['semester']; ?>
          </span>
          <span class="stats-badge">
            <i class="fas fa-download"></i> <?php echo $note['downloads']; ?> Downloads
          </span>
          <span class="stats-badge">
            <i class="fas fa-heart"></i> <?php echo $like_count; ?> Likes
          </span>
          <span class="stats-badge">
            <i class="fas fa-comment"></i> <?php echo $comments_result->num_rows; ?> Comments
          </span>
        </div>

        <p class="mb-4"><?php echo nl2br(htmlspecialchars($note['description'])); ?></p>

        <!-- Buttons -->
        <div class="mb-4 d-flex align-items-center gap-2 flex-wrap">
          <a href="note-details.php?id=<?php echo $material_id; ?>&download=1" class="btn btn-primary">
            <i class="fas fa-download me-1"></i> Download
          </a>
          <button class="btn btn-outline-light" id="shareBtn"
            data-link="http://localhost/Collage%20Project/Personal/pages/note-details.php?id=<?php echo $material_id; ?>">
            <i class="fas fa-share-alt me-1"></i> Share Note
          </button>
        </div>

        <!-- Preview -->
        <?php if (!empty($note['file_path'])): ?>
          <div class="file-preview">
            <h5 class="mb-3"><i class="fas fa-file me-2"></i>File Preview</h5>
            <iframe src="<?php echo $note['file_path']; ?>" style="width: 100%; height: 500px" frameborder="0"></iframe>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Comments Section -->
    <div class="comment-section">
      <h4 class="mb-4"><i class="fas fa-comments me-2"></i>Comments (<?php echo $comments_result->num_rows; ?>)</h4>

      <!-- Comments List -->
      <div class="comments-list">
        <?php if ($comments_result->num_rows > 0): ?>
          <?php while ($comment = $comments_result->fetch_assoc()):
            // Determine the role badge to display
            $role_badge = '';
            if ($comment['commenter_role'] == 'admin') {
              $role_badge = '<span class="role-badge role-admin">Admin</span>';
            } elseif ($comment['commenter_id'] == $uploader_id) {
              $role_badge = '<span class="role-badge role-author">Author</span>';
            } elseif ($comment['commenter_role'] == 'teacher') {
              $role_badge = '<span class="role-badge role-teacher">Teacher</span>';
            } elseif ($comment['commenter_role'] == 'student') {
              $role_badge = '<span class="role-badge role-student">Student</span>';
            }
          ?>
            <div class="comment">
              <!-- Delete button for admin -->
              <form method="POST" class="delete-comment-form">
                <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                <button type="submit" name="delete_comment" class="delete-comment-btn" title="Delete Comment" onclick="return confirm('Are you sure you want to delete this comment?')">
                  <i class="fas fa-trash"></i>
                </button>
              </form>

              <div class="comment-header">
                <div>
                  <span class="commenter-name"><?php echo htmlspecialchars($comment['commenter_name']); ?></span>
                  <?php echo $role_badge; ?>
                </div>
                <span class="comment-date"><?php echo date('M j, Y \a\t g:i A', strtotime($comment['comment_date'])); ?></span>
              </div>
              <p class="comment-text mb-0"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="text-center py-4 text-muted">
            <i class="fas fa-comment-slash fa-2x mb-2"></i>
            <p>No comments yet.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="text-center py-3 border-top border-secondary mt-5">
    <p class="mb-0">&copy; 2025 EduShare. All rights reserved.</p>
  </footer>

  <script>
    const shareBtn = document.getElementById('shareBtn');

    shareBtn.addEventListener('click', () => {
      const link = shareBtn.dataset.link; // get data-link value
      navigator.clipboard.writeText(link)
        .then(() => alert("Note link copied to clipboard!"))
        .catch(err => console.error("Failed to copy: ", err));
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>