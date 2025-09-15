<?php
include_once "./db/config.php";
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>EduShare - Home</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-dark text-light">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="./index.php">EduShare</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
        data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
        aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="./index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="./pages/browse.php">Browse Notes</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="./pages/contact.php">Contact</a>
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
                <li><a class="dropdown-item" href="./auth/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                <li><a class="dropdown-item" href="./auth/upload-notes.php"><i class="fas fa-upload me-2"></i>Upload Notes</a></li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                  <li><a class="dropdown-item" href="./auth/admin.php"><i class="fas fa-cog me-2"></i>Admin Panel</a></li>
                <?php endif; ?>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item text-danger" href="./auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
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


  <!-- Hero Section -->
  <section class="text-center py-5 mt-4">
    <div class="container">
      <h1 class="display-5 fw-bold mb-3">Welcome to EduShare</h1>
      <a href="./pages/browse.php" class="btn btn-primary btn-lg me-2">Browse Notes</a>
      <?php if (!is_logged_in()): ?>

        <a href="pages/register.php" class="btn btn-outline-primary btn-lg me-2">Get Started</a>
      <?php else: ?>
        <a href="./auth/dashboard.php" class="btn btn-outline-primary btn-lg me-2">Dashboard</a>
      <?php endif; ?>
    </div>
  </section>

  <!-- Features Section -->
  <section class="py-5">
    <div class="container">
      <div class="row text-center g-4">
        <div class="col-md-4">
          <h5>Browse Notes</h5>
          <p>Access notes from different colleges, departments, and courses easily.</p>
        </div>
        <div class="col-md-4">
          <h5>Upload Notes</h5>
          <p>Share your study material with others and contribute to the community.</p>
        </div>
        <div class="col-md-4">
          <h5>Student & Teacher Friendly</h5>
          <p>Designed to suit both students and teachers for smooth collaboration.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="text-center py-3 border-top border-secondary mt-5">
    <p class="mb-0">&copy; 2025 EduShare. All rights reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>