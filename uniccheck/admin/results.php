<?php
session_start();
require_once '../includes/config.php';
requireAdminLogin();

// Delete a single result entry
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM results WHERE id = $del_id");
    $redir = isset($_GET['matric']) ? '?matric=' . urlencode($_GET['matric']) : '';
    header("Location: results.php$redir&deleted=1");
    exit;
}

// Filter by matric if provided
$filter_matric = clean($conn, $_GET['matric'] ?? '');
$filter_session  = clean($conn, $_GET['session'] ?? '');
$filter_semester = clean($conn, $_GET['semester'] ?? '');

// Build query
$where = [];
$params = [];
$types  = '';

if ($filter_matric) {
    $where[] = 'r.matric_number = ?';
    $params[] = $filter_matric;
    $types   .= 's';
}
if ($filter_session) {
    $where[] = 'r.session = ?';
    $params[] = $filter_session;
    $types   .= 's';
}
if ($filter_semester) {
    $where[] = 'r.semester = ?';
    $params[] = $filter_semester;
    $types   .= 's';
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT r.*, s.full_name
        FROM results r
        JOIN students s ON r.matric_number = s.matric_number
        $whereSQL
        ORDER BY r.session DESC, r.semester ASC, r.matric_number ASC, r.course_code ASC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get distinct sessions and semesters for filter dropdowns
$sessions  = $conn->query("SELECT DISTINCT session  FROM results ORDER BY session DESC")->fetch_all(MYSQLI_ASSOC);
$semesters = $conn->query("SELECT DISTINCT semester FROM results ORDER BY semester ASC")->fetch_all(MYSQLI_ASSOC);
$students  = $conn->query("SELECT matric_number, full_name FROM students ORDER BY full_name ASC")->fetch_all(MYSQLI_ASSOC);

// Student name for heading
$studentName = '';
if ($filter_matric) {
    $sn = $conn->query("SELECT full_name FROM students WHERE matric_number = '" . $conn->real_escape_string($filter_matric) . "'")->fetch_assoc();
    if ($sn) $studentName = $sn['full_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Results — UniCheck Admin</title>
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
      <a href="students.php" class="nav-item">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        Students
      </a>
      <a href="results.php" class="nav-item active">
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
        <h1 class="page-title">
          Results
          <?php if ($studentName): ?>
            <span style="font-size:16px;font-weight:500;color:var(--text2)">— <?= htmlspecialchars($studentName) ?></span>
          <?php endif; ?>
        </h1>
        <p class="page-sub">View, filter, and delete result entries</p>
      </div>
      <a href="add_result.php" class="btn btn-primary">+ Add Result</a>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
      Result entry deleted successfully.
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card" style="margin-bottom:20px">
      <form method="GET" action="">
        <div class="search-row" style="align-items:flex-end;gap:14px;flex-wrap:wrap">
          <div class="form-group" style="flex:1;min-width:180px;margin-bottom:0">
            <label>Student</label>
            <select name="matric">
              <option value="">All Students</option>
              <?php foreach ($students as $s): ?>
              <option value="<?= htmlspecialchars($s['matric_number']) ?>"
                <?= $filter_matric === $s['matric_number'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['matric_number'] . ' — ' . $s['full_name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" style="min-width:150px;margin-bottom:0">
            <label>Session</label>
            <select name="session">
              <option value="">All Sessions</option>
              <?php foreach ($sessions as $ses): ?>
              <option value="<?= $ses['session'] ?>" <?= $filter_session === $ses['session'] ? 'selected' : '' ?>>
                <?= $ses['session'] ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" style="min-width:150px;margin-bottom:0">
            <label>Semester</label>
            <select name="semester">
              <option value="">All Semesters</option>
              <?php foreach ($semesters as $sem): ?>
              <option value="<?= $sem['semester'] ?>" <?= $filter_semester === $sem['semester'] ? 'selected' : '' ?>>
                <?= $sem['semester'] ?> Semester
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div style="margin-bottom:0;display:flex;gap:8px">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="results.php" class="btn btn-outline-sm" style="padding:10px 16px">Clear</a>
          </div>
        </div>
      </form>
    </div>

    <!-- Results Table -->
    <div class="card">
      <div class="table-header">
        <h2 class="table-title"><?= count($results) ?> Result<?= count($results) !== 1 ? 's' : '' ?> Found</h2>
      </div>

      <?php if (empty($results)): ?>
      <div class="alert alert-info" style="margin:0">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        No results found. Try adjusting your filters or <a href="add_result.php">add a result</a>.
      </div>
      <?php else: ?>
      <div class="table-wrap">
        <table class="result-table">
          <thead>
            <tr>
              <th>Matric No.</th>
              <th>Student Name</th>
              <th>Course Code</th>
              <th>Course Title</th>
              <th class="center">Units</th>
              <th class="center">Score</th>
              <th class="center">Grade</th>
              <th class="center">GP</th>
              <th>Semester</th>
              <th>Session</th>
              <th class="center">Level</th>
              <th class="center">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($results as $r): ?>
            <tr>
              <td><strong><?= htmlspecialchars($r['matric_number']) ?></strong></td>
              <td><?= htmlspecialchars($r['full_name']) ?></td>
              <td><?= htmlspecialchars($r['course_code']) ?></td>
              <td><?= htmlspecialchars($r['course_title']) ?></td>
              <td class="center"><?= $r['credit_unit'] ?></td>
              <td class="center"><?= $r['score'] ?></td>
              <td class="center">
                <span class="grade-badge grade-<?= strtolower($r['grade']) ?>">
                  <?= $r['grade'] ?>
                </span>
              </td>
              <td class="center"><?= number_format($r['grade_point'], 1) ?></td>
              <td><?= $r['semester'] ?></td>
              <td><?= $r['session'] ?></td>
              <td class="center"><?= $r['level'] ?></td>
              <td class="center">
                <?php
                  $delUrl = 'results.php?delete_id=' . $r['id'];
                  if ($filter_matric)  $delUrl .= '&matric='   . urlencode($filter_matric);
                  if ($filter_session) $delUrl .= '&session='  . urlencode($filter_session);
                  if ($filter_semester)$delUrl .= '&semester=' . urlencode($filter_semester);
                ?>
                <a href="<?= $delUrl ?>"
                   class="btn btn-danger-sm"
                   onclick="return confirm('Delete this result entry? This cannot be undone.')">
                  Delete
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

  </div><!-- /admin-main -->

</body>
</html>
