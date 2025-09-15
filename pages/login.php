<?php
session_start();
require_once '../db/config.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = sanitize_input($_POST['email']);
  $password = $_POST['password'];

  if (empty($email) || empty($password)) {
    $error = 'Please fill in all fields.';
  } else {
    $stmt = $conn->prepare("SELECT user_id, name, email, password, role,status,college FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
      $user = $result->fetch_assoc();

      if ($user['status'] == 'inactive') {
        $error = 'Your account has been deactivated. Please contact admin.';
      } elseif (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['college'] = $user['college'];

        redirect('../auth/dashboard.php');
      } else {
        $error = 'Invalid email or password.';
      }
    } else {
      $error = 'Invalid email or password.';
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
  <title>Login</title>
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

    .server-error {
      color: #dc3545;
      font-size: 1rem;
      text-align: center;
      margin-bottom: 1rem;
      padding: 0.5rem;
      border: 1px solid #dc3545;
      border-radius: 0.375rem;
      background-color: rgba(220, 53, 69, 0.1);
    }

    .server-success {
      color: #198754;
      font-size: 1rem;
      text-align: center;
      margin-bottom: 1rem;
      padding: 0.5rem;
      border: 1px solid #198754;
      border-radius: 0.375rem;
      background-color: rgba(25, 135, 84, 0.1);
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

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
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

  <!-- Login Form -->
  <main class="w-100 mt-5" style="max-width: 360px;">
    <h3 class="mb-4 text-center">Login</h3>

    <!-- Display server-side error messages -->
    <?php if (!empty($error)): ?>
      <div class="server-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="server-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form id="loginForm" action="login.php" method="post" novalidate>
      <!-- Email Input -->
      <div class="mb-3">
        <label for="loginEmail" class="form-label">Email address</label>
        <input type="email" class="form-control bg-dark text-light border-secondary"
          id="loginEmail" name="email" placeholder="name@example.com">
        <div class="error-message" id="emailError"></div>
      </div>

      <!-- Password Input -->
      <div class="mb-3">
        <label for="loginPassword" class="form-label">Password</label>
        <input type="password" class="form-control bg-dark text-light border-secondary"
          id="loginPassword" name="password" placeholder="Password">
        <div class="error-message" id="passwordError"></div>
      </div>

      <!-- Remember + Forgot -->
      <!-- <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="rememberMe">
          <label class="form-check-label" for="rememberMe">Remember me</label>
        </div>
        <a href="./login.php" class="link-info">Forgot password?</a>
      </div> -->

      <!-- Submit -->
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <p class="text-center mt-3">
      Don't have an account? <a href="./register.php" class="link-info">Register</a>
    </p>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('loginForm');

      // Get all input fields
      const emailInput = document.getElementById('loginEmail');
      const passwordInput = document.getElementById('loginPassword');

      // Add event listeners for real-time validation
      emailInput.addEventListener('blur', validateEmail);
      passwordInput.addEventListener('blur', validatePassword);

      // Form submission handler
      form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate all fields
        const isEmailValid = validateEmail();
        const isPasswordValid = validatePassword();

        // If all valid, submit the form
        if (isEmailValid && isPasswordValid) {
          form.submit();
        }
      });

      // Validation functions
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

      function validatePassword() {
        const value = passwordInput.value;
        const errorElement = document.getElementById('passwordError');

        if (value === '') {
          showError(passwordInput, errorElement, 'Password is required');
          return false;
        } else if (value.length < 6) {
          showError(passwordInput, errorElement, 'Password must be at least 6 characters');
          return false;
        } else {
          showSuccess(passwordInput, errorElement);
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