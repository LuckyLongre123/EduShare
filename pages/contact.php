<?php
include_once "../db/config.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = sanitize_input($_POST['name']);
  $email = sanitize_input($_POST['email']);
  $subject = sanitize_input($_POST['subject']);
  $message = sanitize_input($_POST['message']);

  // ✅ Update: Correct table name & column names
  $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, status) VALUES (?, ?, ?, ?, 'unread')");
  $stmt->bind_param("ssss", $name, $email, $subject, $message);

  if ($stmt->execute()) {
    $success = "Thank you for your message! We'll get back to you soon.";
    $_POST = array(); // Clear form fields
  } else {
    $error = "Sorry, there was an error sending your message. Please try again.";
  }
  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Contact</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    .error-message {
      color: #dc3545;
      font-size: 0.875rem;
      margin-top: 0.25rem;
    }

    .form-control.error,
    .form-select.error {
      border-color: #dc3545;
    }

    .form-control.success,
    .form-select.success {
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

<body class="bg-dark text-light">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="../index.php">EduShare</a>
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
        aria-controls="navbarNav"
        aria-expanded="false"
        aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link" aria-current="page" href="../index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="./browse.php">Browse Notes</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="./contact.php">Contact</a>
          </li>
        </ul>

        <!-- Auth Section -->
        <div class="d-flex gap-2">
          <?php if (is_logged_in()): ?>
            <div class="nav-item dropdown">
              <a
                class="btn btn-outline-light btn-sm dropdown-toggle"
                href="#"
                role="button"
                data-bs-toggle="dropdown">
                <i class="fas fa-user"></i>
                <span class="ms-1">
                  <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User'; ?>
                </span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item" href="../auth/dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="../auth/upload-notes.php">
                    <i class="fas fa-upload me-2"></i>Upload Notes
                  </a>
                </li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                  <li>
                    <a class="dropdown-item" href="../auth/admin.php">
                      <i class="fas fa-cog me-2"></i>Admin Panel
                    </a>
                  </li>
                <?php endif; ?>
                <li>
                  <hr class="dropdown-divider" />
                </li>
                <li>
                  <a class="dropdown-item text-danger" href="../auth/logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                  </a>
                </li>
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

  <!-- Contact Form -->
<main class="mt-5 d-flex justify-content-center px-2">
  <div class="w-100" style="max-width: 500px">
    <h3 class="mb-4 text-center">Contact Us</h3>

    <!-- Server-side messages -->
    <?php if (!empty($error)): ?>
      <div class="server-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="server-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form id="contactForm" action="contact.php" method="post" novalidate>
      <div class="mb-3">
        <label for="contactName" class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control bg-dark text-light border-secondary" id="contactName" placeholder="Your Name" />
        <div class="error-message" id="nameError"></div>
      </div>

      <div class="mb-3">
        <label for="contactEmail" class="form-label">Email address</label>
        <input type="email" name="email" class="form-control bg-dark text-light border-secondary" id="contactEmail" placeholder="name@example.com" />
        <div class="error-message" id="emailError"></div>
      </div>

      <div class="mb-3">
        <label for="contactSubject" class="form-label">Subject</label>
        <input type="text" name="subject" class="form-control bg-dark text-light border-secondary" id="contactSubject" placeholder="Subject" />
        <div class="error-message" id="subjectError"></div>
      </div>

      <div class="mb-3">
        <label for="contactMessage" class="form-label">Message</label>
        <textarea name="message" class="form-control bg-dark text-light border-secondary" id="contactMessage" rows="5" placeholder="Your message"></textarea>
        <div class="error-message" id="messageError"></div>
      </div>

      <button type="submit" class="btn btn-primary w-100">Send Message</button>
    </form>
  </div>
</main>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("contactForm");

    // Get all input fields
    const nameInput = document.getElementById("contactName");
    const emailInput = document.getElementById("contactEmail");
    const subjectInput = document.getElementById("contactSubject");
    const messageInput = document.getElementById("contactMessage");

    // Add event listeners for real-time validation
    nameInput.addEventListener("blur", validateName);
    emailInput.addEventListener("blur", validateEmail);
    subjectInput.addEventListener("blur", validateSubject);
    messageInput.addEventListener("blur", validateMessage);

    // Form submission handler
    form.addEventListener("submit", function(e) {
      e.preventDefault();

      // Validate all fields
      const isNameValid = validateName();
      const isEmailValid = validateEmail();
      const isSubjectValid = validateSubject();
      const isMessageValid = validateMessage();

      // If all valid, submit the form
      if (isNameValid && isEmailValid && isSubjectValid && isMessageValid) {
        form.submit();
      }
    });

    // Validation functions
    function validateName() {
      const value = nameInput.value.trim();
      const errorElement = document.getElementById("nameError");

      if (value === "") {
        showError(nameInput, errorElement, "Name is required");
        return false;
      } else if (value.length < 2) {
        showError(nameInput, errorElement, "Name must be at least 2 characters");
        return false;
      } else if (!/^[a-zA-Z\s]+$/.test(value)) {
        showError(nameInput, errorElement, "Name can only contain letters and spaces");
        return false;
      } else {
        showSuccess(nameInput, errorElement);
        return true;
      }
    }

    function validateEmail() {
      const value = emailInput.value.trim();
      const errorElement = document.getElementById("emailError");
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (value === "") {
        showError(emailInput, errorElement, "Email is required");
        return false;
      } else if (!emailRegex.test(value)) {
        showError(emailInput, errorElement, "Please enter a valid email address");
        return false;
      } else {
        showSuccess(emailInput, errorElement);
        return true;
      }
    }

    function validateSubject() {
      const value = subjectInput.value.trim();
      const errorElement = document.getElementById("subjectError");

      if (value === "") {
        showError(subjectInput, errorElement, "Subject is required");
        return false;
      } else if (value.length < 5) {
        showError(subjectInput, errorElement, "Subject must be at least 5 characters");
        return false;
      } else if (value.length > 100) {
        showError(subjectInput, errorElement, "Subject must be less than 100 characters");
        return false;
      } else {
        showSuccess(subjectInput, errorElement);
        return true;
      }
    }

    function validateMessage() {
      const value = messageInput.value.trim();
      const errorElement = document.getElementById("messageError");

      if (value === "") {
        showError(messageInput, errorElement, "Message is required");
        return false;
      } else if (value.length < 10) {
        showError(messageInput, errorElement, "Message must be at least 10 characters");
        return false;
      } else if (value.length > 1000) {
        showError(messageInput, errorElement, "Message must be less than 1000 characters");
        return false;
      } else {
        showSuccess(messageInput, errorElement);
        return true;
      }
    }

    // Helper functions
    function showError(input, errorElement, message) {
      input.classList.remove("success");
      input.classList.add("error");
      errorElement.textContent = message;
    }

    function showSuccess(input, errorElement) {
      input.classList.remove("error");
      input.classList.add("success");
      errorElement.textContent = "";
    }
  });
</script>

</html>