<?php
session_start();
include_once '../db/config.php';
if (!isset($_SESSION['user_id'])) {
  redirect('../pages/login.php');
}

// Initialize variables
$title = $description = $subject = $semester = $college_id = $tags = '';
$error_message = $success_message = '';

// Fetch tags from DB
$tags_result = $conn->query("SELECT id, name FROM tags ORDER BY name ASC");

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Sanitize input data
  $title = sanitize_input($_POST['title']);
  $description = sanitize_input($_POST['description']);
  $subject = sanitize_input($_POST['subject']);
  $semester = sanitize_input($_POST['semester']);
  $college_id = 1;
  $user_id = $_SESSION['user_id'];
  $user_role = $_SESSION['user_role'] ?? 'user';

  // Handle multiple tags
  if (isset($_POST['tags']) && is_array($_POST['tags'])) {
    $tags = implode(',', array_map('sanitize_input', $_POST['tags']));
  } else {
    $tags = '';
  }

  // Handle file upload
  if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['file'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_type = $file['type'];

    $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
    $allowed_extensions = ['pdf', 'doc', 'docx', 'txt', 'ppt', 'pptx'];

    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (!in_array($file_type, $allowed_types) && !in_array($file_extension, $allowed_extensions)) {
      $error_message = "Invalid file type. Please upload PDF, DOC, DOCX, TXT, PPT, or PPTX files only.";
    } else if ($file_size > 10 * 1024 * 1024) {
      $error_message = "File size too large. Maximum size allowed is 50MB.";
    } else {
      $upload_dir = '../uploads/';
      if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
      }

      $unique_filename = time() . '_' . uniqid() . '.' . $file_extension;
      $file_path = $upload_dir . $unique_filename;

      if (move_uploaded_file($file_tmp, $file_path)) {
        $status = ($user_role == 'admin') ? 'approved' : 'pending';

        $stmt = $conn->prepare("INSERT INTO materials 
        (title, description, subject, semester, college_id, uploaded_by, file_name, file_path, file_type, file_size, tags, status, upload_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmt->bind_param(
          "sssisisssiss",
          $title,
          $description,
          $subject,
          $semester,
          $college_id,
          $user_id,
          $file_name,
          $file_path,
          $file_type,
          $file_size,
          $tags,
          $status
        );

        if ($stmt->execute()) {
          $success_message = "File uploaded successfully! " . ($status == 'pending' ? "It will be reviewed by admin before being published." : "It has been published immediately.");
          $title = $description = $subject = $semester = $college_id = $tags = '';
          redirect("./dashboard.php");
        } else {
          $error_message = "Database error: " . $stmt->error;
          unlink($file_path);
        }
        $stmt->close();
      } else {
        $error_message = "Failed to upload file. Please try again.";
      }
    }
  } else {
    $error_message = "Please select a file to upload.";
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Upload Notes - EduShare</title>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .alert {
      max-width: 500px;
      margin: 20px auto;
    }

    /* 🔹 Select2 main container (selected items dikhte hain) */
    .select2-container--default .select2-selection--multiple {
      background-color: #1e1e1e !important;
      border: 1px solid #444 !important;
      color: #fff !important;
    }

    /* 🔹 Selected tag chips (jo select karne ke baad dikhte hain) */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
      background-color: #333 !important;
      border: 1px solid #555 !important;
      color: #fff !important;
    }

    /* 🔹 Chips ke text aur cross button */
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
      color: #aaa !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
      color: #fff !important;
    }

    /* 🔹 Dropdown background */
    .select2-container--default .select2-dropdown {
      background-color: #1e1e1e !important;
      border: 1px solid #444 !important;
    }

    /* 🔹 Search input inside dropdown */
    .select2-container--default .select2-search--dropdown .select2-search__field {
      background-color: #2a2a2a !important;
      color: #fff !important;
      border: 1px solid #555 !important;
    }

    /* 🔹 Dropdown options */
    .select2-container--default .select2-results__option {
      background-color: #1e1e1e !important;
      color: #fff !important;
    }

    /* 🔹 Hover/Selected state in dropdown */
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
      background-color: #333 !important;
      color: #fff !important;
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
            <a class="nav-link active" aria-current="page" href="./upload-notes.php">Upload</a>
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

  <!-- Upload Notes Form -->
  <main class="w-100 mt-3 d-flex flex-column align-items-center justify-content-center px-2">
  <div style="max-width: 500px; width: 100%;">
    <h3 class="mb-4 text-center">Upload New Notes</h3>

    <!-- Error/Success -->
    <?php if (!empty($error_message)): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <?php if (!empty($success_message)): ?>
      <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <form id="uploadForm" action="upload-notes.php" method="POST" enctype="multipart/form-data" novalidate>

      <!-- Title -->
      <div class="mb-3">
        <label for="noteTitle" class="form-label">Title</label>
        <input type="text" class="form-control bg-dark text-light border-secondary" id="noteTitle" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
        <div id="titleError" class="invalid-feedback"></div>
      </div>

      <!-- Description -->
      <div class="mb-3">
        <label for="noteDesc" class="form-label">Description</label>
        <textarea class="form-control bg-dark text-light border-secondary" id="noteDesc" name="description" rows="3" required><?php echo htmlspecialchars($description); ?></textarea>
        <div id="descError" class="invalid-feedback"></div>
      </div>

      <!-- Subject -->
      <div class="mb-3">
        <label for="noteSubject" class="form-label">Subject</label>
        <input type="text" class="form-control bg-dark text-light border-secondary" id="noteSubject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" required>
        <div id="subjectError" class="invalid-feedback"></div>
      </div>

      <!-- Semester -->
      <div class="mb-3">
        <label for="noteSemester" class="form-label">Semester</label>
        <input type="number" min="1" max="12" class="form-control bg-dark text-light border-secondary" id="noteSemester" name="semester" value="<?php echo htmlspecialchars($semester); ?>" required>
        <div id="semesterError" class="invalid-feedback"></div>
      </div>

      <!-- College -->
      <div class="mb-3">
        <label for="noteCollege" class="form-label">College</label>
        <input type="text" readonly class="form-control bg-dark text-light border-secondary" id="noteCollege" name="college_id" value="<?php echo htmlspecialchars($_SESSION['college']); ?>" required>
        <div id="collegeError" class="invalid-feedback"></div>
      </div>

      <!-- Tags Dropdown -->
      <div class="mb-3">
        <label for="noteTags" class="form-label">Tags</label>
        <select class="form-control text-white" id="noteTags" name="tags[]" multiple="multiple" required>
          <?php while ($tag = $tags_result->fetch_assoc()): ?>
            <option class="text-white" value="<?php echo htmlspecialchars($tag['name']); ?>"><?php echo htmlspecialchars($tag['name']); ?></option>
          <?php endwhile; ?>
        </select>
        <div id="tagsError" class="invalid-feedback"></div>
        <div class="form-text">Search and select one or more relevant tags.</div>
      </div>

      <!-- File Upload -->
      <div class="mb-3">
        <label for="noteFile" class="form-label">Upload File</label>
        <input class="form-control bg-dark text-light border-secondary" type="file" id="noteFile" name="file" accept=".pdf,.doc,.docx,.txt,.ppt,.pptx" required>
        <div id="fileError" class="invalid-feedback"></div>
      </div>

      <button type="submit" class="btn btn-primary w-100">Upload Notes</button>
    </form>
  </div>
</main>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- jQuery (required for Select2) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('uploadForm');
      const fileInput = document.getElementById('noteFile');
      const maxSize = 10 * 1024 * 1024; // 10MB in bytes
      const allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation'
      ];

      // Validate file on selection
      fileInput.addEventListener('change', function() {
        validateFile(this);
      });

      // Form submission handler
      form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (validateForm()) {
          // If validation passes, submit the form
          form.submit();
        }
      });

      function validateFile(input) {
        const file = input.files[0];
        const errorElement = document.getElementById('fileError');

        if (!file) {
          showError(input, errorElement, 'Please select a file.');
          return false;
        }

        // Check file size
        if (file.size > maxSize) {
          showError(input, errorElement, 'File size exceeds 10MB limit.');
          return false;
        }

        // Check file type
        if (!allowedTypes.includes(file.type)) {
          showError(input, errorElement, 'Invalid file type. Please select a PDF, DOC, DOCX, TXT, PPT, or PPTX file.');
          return false;
        }

        // Clear any previous error
        clearError(input, errorElement);
        return true;
      }

      function validateForm() {
        let isValid = true;

        // Validate required text fields
        const requiredFields = [{
            id: 'noteTitle',
            errorId: 'titleError',
            message: 'Please provide a title.'
          },
          {
            id: 'noteDesc',
            errorId: 'descError',
            message: 'Please provide a description.'
          },
          {
            id: 'noteSubject',
            errorId: 'subjectError',
            message: 'Please provide a subject.'
          },
          {
            id: 'noteSemester',
            errorId: 'semesterError',
            message: 'Please provide a valid semester.'
          },
          {
            id: 'noteCollege',
            errorId: 'collegeError',
            message: 'Please provide your college.'
          },
          {
            id: 'noteTags',
            errorId: 'tagsError',
            message: 'Please provide at least one tag.'
          }
        ];

        requiredFields.forEach(field => {
          const input = document.getElementById(field.id);
          const errorElement = document.getElementById(field.errorId);

          if (!input.value.trim()) {
            showError(input, errorElement, field.message);
            isValid = false;
          } else {
            // Additional validation for semester field
            if (field.id === 'noteSemester') {
              const semesterValue = parseInt(input.value);
              if (isNaN(semesterValue) || semesterValue < 1 || semesterValue > 12) {
                showError(input, errorElement, 'Please enter a valid semester (1-12).');
                isValid = false;
              } else {
                clearError(input, errorElement);
              }
            }
            // Additional validation for tags field
            else if (field.id === 'noteTags') {
              const tags = input.value.split(',').map(tag => tag.trim()).filter(tag => tag !== '');
              if (tags.length === 0) {
                showError(input, errorElement, 'Please provide at least one valid tag.');
                isValid = false;
              } else {
                clearError(input, errorElement);
              }
            }
            // Additional validation for description field
            else if (field.id === 'noteDesc') {
              if (input.value.trim().length < 10) {
                showError(input, errorElement, 'Description should be at least 10 characters long.');
                isValid = false;
              } else {
                clearError(input, errorElement);
              }
            } else {
              clearError(input, errorElement);
            }
          }
        });

        // Validate file
        if (!validateFile(fileInput)) {
          isValid = false;
        }

        return isValid;
      }

      function showError(input, errorElement, message) {
        input.classList.add('is-invalid');
        errorElement.textContent = message;
      }

      function clearError(input, errorElement) {
        input.classList.remove('is-invalid');
        errorElement.textContent = '';
      }
    });
    $(document).ready(function() {
      $('#noteTags').select2({
        placeholder: "Select tags",
        allowClear: true,
      });
    });
  </script>
</body>

</html>