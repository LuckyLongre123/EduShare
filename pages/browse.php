<?php
include_once "../db/config.php";
session_start();

// Get all search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$semester = isset($_GET['semester']) ? (int)$_GET['semester'] : '';
$college = isset($_GET['college']) ? trim($_GET['college']) : '';
$tag = isset($_GET['tag']) ? $_GET['tag'] : []; // multiple tags as array

// Pagination setup
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;

// Build query
$where = "m.status = 'approved'";
$params = [];
$types = "";

if (!empty($search)) {
  $where .= " AND (m.title LIKE ? OR m.description LIKE ? OR m.subject LIKE ?)";
  $search_param = "%$search%";
  array_push($params, $search_param, $search_param, $search_param);
  $types .= "sss";
}

if (!empty($semester) && $semester > 0) {
  $where .= " AND m.semester = ?";
  array_push($params, $semester);
  $types .= "i";
}

if (!empty($college)) {
  $where .= " AND u.college LIKE ?";
  $college_param = "%$college%";
  array_push($params, $college_param);
  $types .= "s";
}

if (!empty($tag)) {
  foreach ($tag as $t) {
    $where .= " AND (m.tags LIKE ? OR m.subject LIKE ?)";
    $tag_param = "%$t%";
    array_push($params, $tag_param, $tag_param);
    $types .= "ss";
  }
}

// Count total notes
$count_sql = "SELECT COUNT(*) as total FROM materials m 
              JOIN users u ON m.uploaded_by = u.user_id 
              WHERE $where";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
  $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_notes = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_notes / $per_page);

// Fetch notes
$sql = "SELECT m.*, u.name as uploader_name, u.college as college 
        FROM materials m 
        JOIN users u ON m.uploaded_by = u.user_id 
        WHERE $where 
        ORDER BY m.upload_date DESC 
        LIMIT ? OFFSET ?";
$final_params = array_merge($params, [$per_page, $offset]);
$final_types = $types . "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($final_types, ...$final_params);
$stmt->execute();
$notes = $stmt->get_result();

// Get unique colleges
$colleges_query = "SELECT DISTINCT college FROM users WHERE college IS NOT NULL AND college != '' AND status = 'active' ORDER BY college";
$colleges_result = $conn->query($colleges_query);
$colleges = [];
while ($row = $colleges_result->fetch_assoc()) {
  $colleges[] = $row['college'];
}

// Get unique tags
$tags_query = "SELECT DISTINCT subject as tag FROM materials 
               UNION 
               SELECT DISTINCT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(tags, ',', n.n), ',', -1)) AS tag
               FROM materials 
               JOIN (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 
                     UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) n 
               ON n.n <= 1 + LENGTH(tags) - LENGTH(REPLACE(tags, ',', ''))
               WHERE tags IS NOT NULL AND tags != ''";
$tags_result = $conn->query($tags_query);
$tags = [];
while ($row = $tags_result->fetch_assoc()) {
  if (!empty($row['tag'])) {
    $tags[] = $row['tag'];
  }
}
$tags = array_unique($tags);
sort($tags);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Browse Notes - EduShare</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    /* Smooth toggle animation */
    #advanced-filters {
      display: none;
    }

    body {
      background-color: #121212;
      color: #eee;
    }

    .filter-section {
      background-color: #1e1e1e;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 25px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.6);
    }

    .filter-title {
      border-bottom: 1px solid #444;
      padding-bottom: 10px;
      margin-bottom: 15px;
      font-weight: 600;
      color: #fff;
    }

    .form-control,
    .form-select,
    .select2-container--default .select2-selection--multiple,
    .select2-container--default .select2-selection--single {
      background-color: #2a2a2a !important;
      color: #fff !important;
      border: 1px solid #444 !important;
    }

    .form-control::placeholder {
      color: #aaa;
    }

    /* Select2 custom dark theme */
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
      background-color: #2a2a2a !important;
      border: 1px solid #444 !important;
      color: #fff !important;
      border-radius: 6px !important;
      min-height: 40px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered,
    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
      color: #fff !important;
    }

    .select2-dropdown {
      background-color: #1e1e1e !important;
      border: 1px solid #444 !important;
      color: #fff !important;
    }

    .select2-search__field {
      background-color: #2a2a2a !important;
      color: #fff !important;
      border: 1px solid #555 !important;
    }

    .select2-results__option {
      color: #fff !important;
      background-color: #1e1e1e !important;
    }

    .select2-results__option--highlighted {
      background-color: #333 !important;
    }

    .select2-selection__choice {
      background-color: #333 !important;
      border: 1px solid #555 !important;
      color: #fff !important;
      padding: 2px 8px !important;
      border-radius: 4px !important;
    }

    .card {
      border-radius: 10px;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.7);
    }
  </style>
</head>

<body class="bg-dark">

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
            <a class="nav-link " aria-current="page" href="../index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="../pages/browse.php">Browse Notes</a>
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
                <li><a class="dropdown-item text-danger" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
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

  <!-- Page Header -->
  <header class="text-center py-4">
    <h2 class="text-primary">Browse Notes</h2>
    <p class="lead text-white">Explore notes uploaded by students and teachers from different colleges and departments.</p>
  </header>

  <!-- Advanced Filters -->
  <section class="container bg-dark filter-section">
    <h4 class="filter-title d-flex justify-content-between align-items-center">
      <span><i class="fas fa-search me-2"></i>Search Notes</span>
      <button type="button" id="toggleFilters" class="btn btn-sm btn-outline-light">
        <i class="fas fa-filter"></i> Filters
      </button>
    </h4>

    <form method="GET" action="browse.php">
      <div class="row g-3">
        <!-- Always visible search bar -->
        <div class="col-12">
          <input type="text" name="search" class="form-control" id="search"
            placeholder="Search by title, subject or keyword"
            value="<?php echo htmlspecialchars($search); ?>">
        </div>
      </div>

      <!-- Hidden Advanced Filters -->
      <div id="advanced-filters" class="mt-3">
        <div class="row g-3">
          <div class="col-md-3">
            <label for="semester" class="form-label">Semester</label>
            <select name="semester" id="semester" class="form-select">
              <option value="">All</option>
              <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?php echo $i; ?>" <?php echo $semester == $i ? 'selected' : ''; ?>>
                  Sem <?php echo $i; ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label for="college" class="form-label">College</label>
            <select name="college" id="college" class="form-select">
              <option value="">All Colleges</option>
              <?php foreach ($colleges as $col): ?>
                <option value="<?php echo htmlspecialchars($col); ?>" <?php echo $college == $col ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($col); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-5">
            <label for="tag" class="form-label">Tags</label>
            <select name="tag[]" id="tag" class="form-select" multiple="multiple">
              <?php foreach ($tags as $t): ?>
                <option value="<?php echo htmlspecialchars($t); ?>"
                  <?php echo (is_array($tag) && in_array($t, $tag)) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($t); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Buttons -->
      <div class="col-12 d-flex justify-content-end gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Apply</button>
        <a href="browse.php" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i> Reset</a>
      </div>
    </form>
  </section>

  <!-- Notes Section -->
  <main class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4>Available Notes (<?php echo $total_notes; ?> found)</h4>
    </div>

    <div class="row g-4">
      <?php if ($notes->num_rows > 0): ?>
        <?php while ($note = $notes->fetch_assoc()): ?>
          <div class="col-md-4">
            <div class="card bg-dark text-light border-secondary h-100">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?php echo htmlspecialchars($note['title']); ?></h5>
                <p class="card-text flex-grow-1"><?php echo htmlspecialchars(substr($note['description'], 0, 120)) . '...'; ?></p>
                <p class="mb-1"><small class="text-secondary">By: <?php echo htmlspecialchars($note['uploader_name']); ?></small></p>
                <p class="mb-1"><small class="text-secondary">College: <?php echo htmlspecialchars($note['college']); ?></small></p>
                <p class="mb-2"><small class="text-secondary"><?php echo htmlspecialchars($note['subject']); ?> | Sem <?php echo $note['semester']; ?></small></p>
                <a href="note-details.php?id=<?php echo $note['material_id']; ?>" class="btn btn-primary btn-sm mt-auto">View Details</a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12 text-center py-5">
          <i class="fas fa-file-search fa-3x mb-3 text-secondary"></i>
          <p>No notes found matching your criteria.</p>
          <a href="browse.php" class="btn btn-primary">Clear Filters</a>
        </div>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
      <nav class="mt-4">
        <ul class="pagination justify-content-center">
          <?php if ($page > 1): ?>
            <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a></li>
          <?php endif; ?>
          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
              <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($page < $total_pages): ?>
            <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </main>

  <footer class="text-center py-3 border-top border-secondary mt-5">
    <p class="mb-0">&copy; 2025 EduShare. All rights reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $(document).ready(function() {
      // Select2 setup
      $('#semester').select2({
        placeholder: "Select semester",
        allowClear: true,
        width: '100%'
      });
      $('#college').select2({
        placeholder: "Select college",
        allowClear: true,
        width: '100%'
      });
      $('#tag').select2({
        placeholder: "Search or select tags",
        allowClear: true,
        width: '100%',
        multiple: true
      });

      // Toggle advanced filters
      $("#toggleFilters").on("click", function() {
        $("#advanced-filters").slideToggle(300);
        $(this).find("i").toggleClass("fa-filter fa-times"); // icon change
      });
    });
  </script>
</body>

</html>