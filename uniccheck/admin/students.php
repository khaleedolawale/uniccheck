<?php
session_start();
require_once '../includes/config.php';
requireAdminLogin();

$success = $error = '';

// Add student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $matric = strtoupper(clean($conn, $_POST['matric_number'] ?? ''));
    $name   = clean($conn, $_POST['full_name'] ?? '');
    $level  = clean($conn, $_POST['level'] ?? '');
    $dept   = clean($conn, $_POST['department'] ?? '');
    $fac    = clean($conn, $_POST['faculty'] ?? '');

    if (!$matric || !$name || !$level || !$dept || !$fac) {
        $error = 'All fields are required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO students (matric_number, full_name, level, department, faculty) VALUES (?,?,?,?,?)");
        $stmt->bind_param('sssss', $matric, $name, $level, $dept, $fac);
        if ($stmt->execute()) {
            $success = "Student <strong>$name</strong> added successfully.";
        } else {
            $error = 'Matric number already exists or database error.';
        }
    }
}

// Delete student
if (isset($_GET['delete'])) {
    $del = clean($conn, $_GET['delete']);
    $conn->query("DELETE FROM students WHERE matric_number = '$del'");
    header('Location: students.php?deleted=1');
    exit;
}

$students = $conn->query("SELECT s.*, COUNT(r.id) AS result_count FROM students s LEFT JOIN results r ON s.matric_number = r.matric_number GROUP BY s.matric_number ORDER BY s.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Students — UniCheck Admin</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon">U</div>
      <div><span class="logo-name">UniCheck</span><span class="logo-sub">Admin</span></div>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.php" class="nav-item">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Dashboard
      </a>
      <a href="students.php" class="nav-item active">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        Students
      </a>
      <a href="results.php" class="nav-item">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Results
      </a>
      <a href="add_result.php" class="nav-item">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        Add Result
      </a>
    </nav>
    <a href="logout.php" class="sidebar-logout">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Logout
    </a>
  </aside>

  <div class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1 class="page-title">Students</h1>
        <p class="page-sub">Manage registered students</p>
      </div>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Student deleted successfully.</div><?php endif; ?>

    <!-- Add Student Form -->
    <div class="card" style="margin-bottom:28px">
      <h2 class="table-title" style="margin-bottom:20px">Add New Student</h2>
      <form method="POST">
        <input type="hidden" name="add_student" value="1">
        <div class="form-row-2">
          <div class="form-group">
            <label>Matric Number</label>
            <input type="text" name="matric_number" placeholder="e.g. AHU/2024/001" required/>
          </div>
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" placeholder="Student's full name" required/>
          </div>
        </div>
        <div class="form-row-3">
          <div class="form-group">
            <label>Level</label>
            <select name="level" required>
              <?php foreach (['100','200','300','400','500'] as $l): ?>
              <option value="<?= $l ?>"><?= $l ?> Level</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Department</label>
            <input type="text" name="department" placeholder="e.g. Computer Science" required/>
          </div>
          <div class="form-group">
            <label>Faculty</label>
            <input type="text" name="faculty" placeholder="e.g. Computing & Applied Sciences" required/>
          </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Student</button>
      </form>
    </div>

    <!-- Students Table -->
    <div class="card">
      <div class="table-header">
        <h2 class="table-title">All Students</h2>
      </div>
      <div class="table-wrap">
        <table class="result-table">
          <thead>
            <tr>
              <th>Matric Number</th>
              <th>Full Name</th>
              <th>Department</th>
              <th class="center">Level</th>
              <th class="center">Results</th>
              <th class="center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($s = $students->fetch_assoc()): ?>
            <tr>
              <td><strong><?= htmlspecialchars($s['matric_number']) ?></strong></td>
              <td><?= htmlspecialchars($s['full_name']) ?></td>
              <td><?= htmlspecialchars($s['department']) ?></td>
              <td class="center"><?= $s['level'] ?> Level</td>
              <td class="center"><?= $s['result_count'] ?> entries</td>
              <td class="center" style="display:flex;gap:8px;justify-content:center">
                <a href="results.php?matric=<?= urlencode($s['matric_number']) ?>" class="btn btn-outline-sm">Results</a>
                <a href="students.php?delete=<?= urlencode($s['matric_number']) ?>" class="btn btn-danger-sm" onclick="return confirm('Delete this student and all their results?')">Delete</a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
