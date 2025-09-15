<?php
// Example user data (replace with session/session later)
include "../db/config.php";
session_start();

// Initialize variables
$profile_message = '';

$stmt = $conn->prepare("SELECT joined_on,department FROM users WHERE email = ?");
$stmt->bind_param("s", $_SESSION['email']);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();


$userName = $_SESSION['user_name'];
$userEmail = $_SESSION['email'];
$userRole = $_SESSION['user_role'];
$userSince = date("d F Y", strtotime($result['joined_on']));

$userCollege = $_SESSION['college'];
$userDept = $result['department'];


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
  $name = sanitize_input($_POST['name']);
  $college = sanitize_input($_POST['college']);
  $department = sanitize_input($_POST['department']);

  // Validate inputs
  if (empty($name) || empty($college) || empty($department)) {
    $profile_message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                          <i class="fas fa-exclamation-triangle me-2"></i>All fields are required.
                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
  } elseif (!preg_match("/^[a-zA-Z\s.'-]{2,50}$/", $name)) {
    $profile_message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                          <i class="fas fa-exclamation-triangle me-2"></i>Please provide a valid name (2-50 characters, letters and spaces only).
                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
  } elseif (strlen($college) < 2 || strlen($college) > 100) {
    $profile_message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                          <i class="fas fa-exclamation-triangle me-2"></i>College name must be between 2-100 characters.
                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
  } elseif (strlen($department) < 2 || strlen($department) > 100) {
    $profile_message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                          <i class="fas fa-exclamation-triangle me-2"></i>Department name must be between 2-100 characters.
                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
  } else {
    $update_stmt = $conn->prepare("UPDATE users SET name = ?, college = ?, department = ? WHERE email = ?");
    $update_stmt->bind_param("ssss", $name, $college, $department, $_SESSION['email']);

    if ($update_stmt->execute()) {
      $_SESSION['user_name'] = $name;
      $_SESSION['college'] = $college;
      $userName = $name;
      $userCollege = $college;
      $userDept = $department;

      $profile_message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>Profile updated successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
    } else {
      $profile_message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>Failed to update profile. Please try again.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
    }
    $update_stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profile - EduShare</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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

    .alert {
      max-width: 700px;
      margin: 0 auto 20px auto;
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
            <a class="nav-link active" aria-current="page" href="./profile.php">Profile</a>
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
                <span class="ms-1"><?php echo isset($_SESSION['userName']) ? $_SESSION['userName'] : 'User'; ?></span>
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


  <!-- Profile Page -->
  <div class="container my-5" style="max-width: 700px;">
    <h2 class="mb-4">Profile Settings</h2>

    <!-- Display success/error messages -->
    <?php if (!empty($profile_message)) echo $profile_message; ?>

    <form id="profileForm" method="POST" action="profile.php">
      <input type="hidden" name="update_profile" value="1">

      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" class="form-control bg-dark text-light border-secondary" id="userName" name="name" value="<?php echo htmlspecialchars($userName); ?>" required>
        <div class="invalid-feedback" id="nameError">Please provide a valid name.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" class="form-control bg-dark text-light border-secondary" value="<?php echo htmlspecialchars($userEmail); ?>" readonly>
        <small class="text-secondary">Email cannot be changed</small>
      </div>
      <div class="mb-3">
        <label class="form-label">Account Role</label>
        <input type="text" class="form-control bg-dark text-light border-secondary" value="<?php echo htmlspecialchars($userRole); ?>" readonly>
        <small class="text-secondary">Role cannot be changed</small>
      </div>
      <div class="mb-3">
        <label class="form-label">Member Since</label>
        <input type="text" class="form-control bg-dark text-light border-secondary" value="<?php echo htmlspecialchars($userSince); ?>" readonly>
      </div>
      <div class="mb-3">
        <label class="form-label">College</label>
        <input type="text" class="form-control bg-dark text-light border-secondary" id="userCollege" name="college" value="<?php echo htmlspecialchars($userCollege); ?>" required>
        <div class="invalid-feedback" id="collegeError">Please provide your college name.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Department</label>
        <input type="text" class="form-control bg-dark text-light border-secondary" id="userDept" name="department" value="<?php echo htmlspecialchars($userDept); ?>" required>
        <div class="invalid-feedback" id="deptError">Please provide your department name.</div>
      </div>

      <button type="submit" class="btn btn-primary mt-3">Update Profile</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('profileForm');

      // Form submission handler
      form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (validateForm()) {
          // If validation passes, submit the form
          form.submit();
        }
      });

      function validateForm() {
        let isValid = true;

        // Validate required fields
        const requiredFields = [{
            id: 'userName',
            errorId: 'nameError',
            message: 'Please provide your name.',
            validation: validateName
          },
          {
            id: 'userCollege',
            errorId: 'collegeError',
            message: 'Please provide your college name.',
            validation: validateText
          },
          {
            id: 'userDept',
            errorId: 'deptError',
            message: 'Please provide your department name.',
            validation: validateText
          }
        ];

        requiredFields.forEach(field => {
          const input = document.getElementById(field.id);
          const errorElement = document.getElementById(field.errorId);

          if (!input.value.trim()) {
            showError(input, errorElement, field.message);
            isValid = false;
          } else if (field.validation && !field.validation(input.value)) {
            showError(input, errorElement, field.message);
            isValid = false;
          } else {
            clearError(input, errorElement);
          }
        });

        return isValid;
      }

      function validateName(name) {
        // Basic name validation - allows letters, spaces, and common name characters
        const nameRegex = /^[a-zA-Z\s.'-]{2,50}$/;
        return nameRegex.test(name);
      }

      function validateText(text) {
        // Basic text validation - ensures it's not empty and has reasonable length
        return text.trim().length >= 2 && text.trim().length <= 100;
      }

      function showError(input, errorElement, message) {
        input.classList.add('is-invalid');
        errorElement.textContent = message;
      }

      function clearError(input, errorElement) {
        input.classList.remove('is-invalid');
        errorElement.textContent = '';
      }

      // Add real-time validation as user types
      document.getElementById('userName').addEventListener('input', function() {
        const errorElement = document.getElementById('nameError');
        if (!validateName(this.value)) {
          showError(this, errorElement, 'Please provide a valid name (2-50 characters, letters and spaces only).');
        } else {
          clearError(this, errorElement);
        }
      });

      document.getElementById('userCollege').addEventListener('input', function() {
        const errorElement = document.getElementById('collegeError');
        if (!validateText(this.value)) {
          showError(this, errorElement, 'Please provide a valid college name (2-100 characters).');
        } else {
          clearError(this, errorElement);
        }
      });

      document.getElementById('userDept').addEventListener('input', function() {
        const errorElement = document.getElementById('deptError');
        if (!validateText(this.value)) {
          showError(this, errorElement, 'Please provide a valid department name (2-100 characters).');
        } else {
          clearError(this, errorElement);
        }
      });
    });
  </script>
</body>

</html>