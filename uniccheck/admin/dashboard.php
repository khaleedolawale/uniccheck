<?php
session_start();
require_once '../includes/config.php';
requireAdminLogin();

// Stats
$totalStudents = $conn->query("SELECT COUNT(*) AS c FROM students")->fetch_assoc()['c'];
$totalResults  = $conn->query("SELECT COUNT(*) AS c FROM results")->fetch_assoc()['c'];
$totalSessions = $conn->query("SELECT COUNT(DISTINCT session) AS c FROM results")->fetch_assoc()['c'];

// Recent students
$recent = $conn->query("SELECT * FROM students ORDER BY created_at DESC LIMIT 8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard — UniCheck Admin</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon">U</div>
      <div>
        <span class="logo-name">UniCheck</span>
        <span class="logo-sub">Admin</span>
      </div>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.php" class="nav-item active">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Dashboard
      </a>
      <a href="students.php" class="nav-item">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Students
      </a>
      <a href="results.php" class="nav-item">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
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

  <!-- MAIN -->
  <div class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-sub">Welcome back, <?= htmlspecialchars($_SESSION['admin_name']) ?></p>
      </div>
      <a href="add_result.php" class="btn btn-primary">+ Add Result</a>
    </div>

    <!-- STAT CARDS -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon stat-icon--blue">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div>
          <div class="stat-num"><?= $totalStudents ?></div>
          <div class="stat-label">Total Students</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon--green">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div>
          <div class="stat-num"><?= $totalResults ?></div>
          <div class="stat-label">Result Entries</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon--purple">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div>
          <div class="stat-num"><?= $totalSessions ?></div>
          <div class="stat-label">Academic Sessions</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon--orange">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <div>
          <div class="stat-num"><?= $totalStudents > 0 ? round($totalResults / $totalStudents, 1) : 0 ?></div>
          <div class="stat-label">Avg Courses / Student</div>
        </div>
      </div>
    </div>

    <!-- RECENT STUDENTS -->
    <div class="card" style="margin-top:28px">
      <div class="table-header">
        <h2 class="table-title">Recent Students</h2>
        <a href="students.php" class="btn btn-outline-sm">View All</a>
      </div>
      <div class="table-wrap">
        <table class="result-table">
          <thead>
            <tr>
              <th>Matric Number</th>
              <th>Full Name</th>
              <th>Department</th>
              <th class="center">Level</th>
              <th class="center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($s = $recent->fetch_assoc()): ?>
            <tr>
              <td><strong><?= htmlspecialchars($s['matric_number']) ?></strong></td>
              <td><?= htmlspecialchars($s['full_name']) ?></td>
              <td><?= htmlspecialchars($s['department']) ?></td>
              <td class="center"><?= $s['level'] ?> Level</td>
              <td class="center">
                <a href="results.php?matric=<?= urlencode($s['matric_number']) ?>" class="btn btn-outline-sm">View Results</a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /admin-main -->

</body>
</html>
