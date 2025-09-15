<?php
session_start();
require_once '../db/config.php';
$error = null;

// Fetch colleges from database
$colleges = [];
$college_query = "SELECT college_id, college_name FROM colleges ORDER BY college_name";
$college_result = $conn->query($college_query);
if ($college_result && $college_result->num_rows > 0) {
    while ($row = $college_result->fetch_assoc()) {
        $colleges[$row['college_id']] = $row['college_name'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $role = sanitize_input($_POST['role']);
    $college_id = sanitize_input($_POST['college']);
    $other_college = isset($_POST['other_college']) ? sanitize_input($_POST['other_college']) : '';
    $department = sanitize_input($_POST['department']);

    // Determine college value
    $college_value = '';
    if ($college_id === 'other' && !empty($other_college)) {
        $college_value = $other_college;
    } elseif (isset($colleges[$college_id])) {
        $college_value = $colleges[$college_id];
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = 'Email address already registered.';
    } else {
        // Hash password and insert user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, college, department) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $hashed_password, $role, $college_value, $department);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['user_role'] = $role;
            $_SESSION['college'] = $college_value;

            $success = "Registration successful! Welcome to EduShare.";
            header("Location: ../auth/dashboard.php");
            exit();
        } else {
            $error = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .error-message {
      color: #dc3545;
      font-size: 0.875rem;
      margin-top: 0.25rem;
    }

    .form-control.error {
      border-color: #dc3545;
    }

    .form-control.success {
      border-color: #198754;
    }
    
    #otherCollegeContainer {
      display: none;
      margin-top: 10px;
    }
  </style>
</head>

<body class="bg-dark text-light d-flex flex-column align-items-center justify-content-center">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark w-100">
    <div class="container-fluid">
      <a class="navbar-brand" href="../index.php">EduShare</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
        aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" name="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="../index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="./browse.php">Browse Notes</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="./contact.php">Contact</a>
          </li>
        </ul>
        <div class="d-flex gap-2">
          <a href="./login.php" class="btn btn-outline-light btn-sm">Login</a>
          <a href="./register.php" class="btn btn-primary btn-sm">Register</a>
        </div>
      </div>
    </div>
  </nav>
  
  <?php if ($error): ?>
    <div class="alert alert-danger w-100 mt-3" style="max-width: 400px;">
      <?php echo $error; ?>
    </div>
  <?php endif; ?>

  <!-- Register Form -->
  <main class="w-100 mt-5" style="max-width: 400px;">
    <h3 class="mb-4 text-center">Register</h3>
    <form id="registerForm" action="register.php" method="post" novalidate>
      <!-- Name -->
      <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" class="form-control bg-dark text-light border-secondary" id="name" name="name" placeholder="John Doe" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
        <div class="error-message" id="nameError"></div>
      </div>

      <!-- Email -->
      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" class="form-control bg-dark text-light border-secondary" id="email" name="email" placeholder="name@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        <div class="error-message" id="emailError"></div>
      </div>

      <!-- Role -->
      <div class="mb-3">
        <label for="role" class="form-label">Role</label>
        <select class="form-select bg-dark text-light border-secondary" id="role" name="role">
          <option value="" selected disabled>Select role</option>
          <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
          <option value="teacher" <?php echo (isset($_POST['role']) && $_POST['role'] == 'teacher') ? 'selected' : ''; ?>>Teacher</option>
        </select>
        <div class="error-message" id="roleError"></div>
      </div>

      <!-- College -->
      <div class="mb-3">
        <label for="college" class="form-label">College</label>
        <select class="form-select bg-dark text-light border-secondary" id="college" name="college">
          <option value="" selected disabled>Select college</option>
          <?php foreach ($colleges as $id => $name): ?>
            <option value="<?php echo $id; ?>" <?php echo (isset($_POST['college']) && $_POST['college'] == $id) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($name); ?>
            </option>
          <?php endforeach; ?>
          <option value="other" <?php echo (isset($_POST['college']) && $_POST['college'] == 'other') ? 'selected' : ''; ?>>Other (Please specify)</option>
        </select>
        <div id="otherCollegeContainer">
          <input type="text" class="form-control bg-dark text-light border-secondary mt-2" id="otherCollege" name="other_college" placeholder="Enter college name" value="<?php echo isset($_POST['other_college']) ? htmlspecialchars($_POST['other_college']) : ''; ?>">
        </div>
        <div class="error-message" id="collegeError"></div>
      </div>

      <!-- Department -->
      <div class="mb-3">
        <label for="department" class="form-label">Department</label>
        <input type="text" class="form-control bg-dark text-light border-secondary" id="department" name="department" placeholder="Department Name" value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''; ?>">
        <div class="error-message" id="departmentError"></div>
      </div>

      <!-- Password -->
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control bg-dark text-light border-secondary" id="password" name="password" placeholder="Password">
        <div class="error-message" id="passwordError"></div>
      </div>

      <!-- Confirm Password -->
      <div class="mb-3">
        <label for="confirmPassword" class="form-label">Confirm Password</label>
        <input type="password" class="form-control bg-dark text-light border-secondary" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password">
        <div class="error-message" id="confirmPasswordError"></div>
      </div>

      <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>

    <p class="text-center mt-3">
      Already have an account? <a href="./login.php" class="link-info">Login</a>
    </p>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('registerForm');
      const collegeSelect = document.getElementById('college');
      const otherCollegeContainer = document.getElementById('otherCollegeContainer');
      const otherCollegeInput = document.getElementById('otherCollege');

      // Show/hide other college input based on selection
      collegeSelect.addEventListener('change', function() {
        if (this.value === 'other') {
          otherCollegeContainer.style.display = 'block';
        } else {
          otherCollegeContainer.style.display = 'none';
        }
      });

      // Initialize on page load
      if (collegeSelect.value === 'other') {
        otherCollegeContainer.style.display = 'block';
      }

      // Get all input fields
      const nameInput = document.getElementById('name');
      const emailInput = document.getElementById('email');
      const roleInput = document.getElementById('role');
      const collegeInput = document.getElementById('college');
      const departmentInput = document.getElementById('department');
      const passwordInput = document.getElementById('password');
      const confirmPasswordInput = document.getElementById('confirmPassword');

      // Add event listeners for real-time validation
      nameInput.addEventListener('blur', validateName);
      emailInput.addEventListener('blur', validateEmail);
      roleInput.addEventListener('change', validateRole);
      collegeInput.addEventListener('change', validateCollege);
      departmentInput.addEventListener('blur', validateDepartment);
      passwordInput.addEventListener('blur', validatePassword);
      confirmPasswordInput.addEventListener('blur', validateConfirmPassword);

      // Form submission handler
      form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate all fields
        const isNameValid = validateName();
        const isEmailValid = validateEmail();
        const isRoleValid = validateRole();
        const isCollegeValid = validateCollege();
        const isDepartmentValid = validateDepartment();
        const isPasswordValid = validatePassword();
        const isConfirmPasswordValid = validateConfirmPassword();

        // If all valid, submit the form
        if (isNameValid && isEmailValid && isRoleValid &&
          isCollegeValid && isDepartmentValid && isPasswordValid && isConfirmPasswordValid) {
          form.submit();
        }
      });

      // Validation functions
      function validateName() {
        const value = nameInput.value.trim();
        const errorElement = document.getElementById('nameError');

        if (value === '') {
          showError(nameInput, errorElement, 'Full name is required');
          return false;
        } else if (value.length < 2) {
          showError(nameInput, errorElement, 'Full name must be at least 2 characters');
          return false;
        } else if (!/^[a-zA-Z\s]+$/.test(value)) {
          showError(nameInput, errorElement, 'Full name can only contain letters and spaces');
          return false;
        } else {
          showSuccess(nameInput, errorElement);
          return true;
        }
      }

      function validateEmail() {
        const value = emailInput.value.trim();
        const errorElement = document.getElementById('emailError');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (value === '') {
          showError(emailInput, errorElement, 'Email is required');
          return false;
        } else if (!emailRegex.test(value)) {
          showError(emailInput, errorElement, 'Please enter a valid email address');
          return false;
        } else {
          showSuccess(emailInput, errorElement);
          return true;
        }
      }

      function validateRole() {
        const value = roleInput.value;
        const errorElement = document.getElementById('roleError');

        if (!value) {
          showError(roleInput, errorElement, 'Please select a role');
          return false;
        } else {
          showSuccess(roleInput, errorElement);
          return true;
        }
      }

      function validateCollege() {
        const value = collegeInput.value;
        const errorElement = document.getElementById('collegeError');

        if (!value) {
          showError(collegeInput, errorElement, 'Please select a college');
          return false;
        } else if (value === 'other' && otherCollegeInput.value.trim() === '') {
          showError(collegeInput, errorElement, 'Please specify your college name');
          return false;
        } else {
          showSuccess(collegeInput, errorElement);
          return true;
        }
      }

      function validateDepartment() {
        const value = departmentInput.value.trim();
        const errorElement = document.getElementById('departmentError');

        if (value === '') {
          showError(departmentInput, errorElement, 'Department name is required');
          return false;
        } else if (value.length < 3) {
          showError(departmentInput, errorElement, 'Department name must be at least 3 characters');
          return false;
        } else {
          showSuccess(departmentInput, errorElement);
          return true;
        }
      }

      function validatePassword() {
        const value = passwordInput.value;
        const errorElement = document.getElementById('passwordError');

        if (value === '') {
          showError(passwordInput, errorElement, 'Password is required');
          return false;
        } else if (value.length < 8) {
          showError(passwordInput, errorElement, 'Password must be at least 8 characters');
          return false;
        } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(value)) {
          showError(passwordInput, errorElement, 'Password must contain at least one uppercase letter, one lowercase letter, and one number');
          return false;
        } else {
          showSuccess(passwordInput, errorElement);
          return true;
        }
      }

      function validateConfirmPassword() {
        const value = confirmPasswordInput.value;
        const passwordValue = passwordInput.value;
        const errorElement = document.getElementById('confirmPasswordError');

        if (value === '') {
          showError(confirmPasswordInput, errorElement, 'Please confirm your password');
          return false;
        } else if (value !== passwordValue) {
          showError(confirmPasswordInput, errorElement, 'Passwords do not match');
          return false;
        } else {
          showSuccess(confirmPasswordInput, errorElement);
          return true;
        }
      }

      // Helper functions
      function showError(input, errorElement, message) {
        input.classList.remove('success');
        input.classList.add('error');
        errorElement.textContent = message;
      }

      function showSuccess(input, errorElement) {
        input.classList.remove('error');
        input.classList.add('success');
        errorElement.textContent = '';
      }
    });
  </script>
</body>

</html>