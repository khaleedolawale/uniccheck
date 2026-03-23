<?php
session_start();
require_once '../includes/config.php';
requireAdminLogin();

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matric   = clean($conn, $_POST['matric_number'] ?? '');
    $code     = strtoupper(clean($conn, $_POST['course_code'] ?? ''));
    $title    = clean($conn, $_POST['course_title'] ?? '');
    $unit     = (int)($_POST['credit_unit'] ?? 0);
    $score    = (int)($_POST['score'] ?? 0);
    $semester = clean($conn, $_POST['semester'] ?? '');
    $session  = clean($conn, $_POST['session'] ?? '');
    $level    = clean($conn, $_POST['level'] ?? '');

    if (!$matric || !$code || !$title || !$unit || $score < 0 || !$semester || !$session || !$level) {
        $error = 'Please fill in all fields correctly.';
    } elseif ($score > 100) {
        $error = 'Score cannot exceed 100.';
    } else {
        list($grade, $gp) = getGrade($score);

        $chk = $conn->prepare("SELECT id FROM results WHERE matric_number=? AND course_code=? AND semester=? AND session=?");
        $chk->bind_param('ssss', $matric, $code, $semester, $session);
        $chk->execute();

        if ($chk->get_result()->num_rows > 0) {
            $error = "A result for <strong>$code</strong> already exists for this student in $semester Semester $session.";
        } else {
            $sql = "INSERT INTO results (matric_number,course_code,course_title,credit_unit,score,grade,grade_point,semester,session,level) VALUES (?,?,?,?,?,?,?,?,?,?)";
            $ins = $conn->prepare($sql);
            $ins->bind_param('sssiisd' . 'sss', $matric, $code, $title, $unit, $score, $grade, $gp, $semester, $session, $level);
            if ($ins->execute()) {
                $success = "Result for <strong>$code</strong> saved successfully.";
            } else {
                $error = 'Database error: ' . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Result — UniCheck Admin</title>
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
      <a href="results.php" class="nav-item">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Results
      </a>
      <a href="add_result.php" class="nav-item active">
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
        <h1 class="page-title">Add Result</h1>
        <p class="page-sub">Upload a course result for a student</p>
      </div>
      <a href="dashboard.php" class="btn btn-outline-sm">← Dashboard</a>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
      <?= $success ?> &nbsp;—&nbsp; <a href="add_result.php">Add another</a>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-error">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= $error ?>
    </div>
    <?php endif; ?>

    <div class="card" style="max-width:700px">
      <form method="POST" action="">

        <div class="form-row-2">
          <div class="form-group">
            <label>Student</label>
            <select name="matric_number" required>
              <option value="">— Select student —</option>
              <?php
              $studentsDD = $conn->query("SELECT matric_number, full_name FROM students ORDER BY full_name ASC");
              while ($s = $studentsDD->fetch_assoc()):
              ?>
              <option value="<?= htmlspecialchars($s['matric_number']) ?>"
                <?= ($_POST['matric_number'] ?? '') === $s['matric_number'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['matric_number'] . ' — ' . $s['full_name']) ?>
              </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Level</label>
            <select name="level" required>
              <?php foreach (['100','200','300','400','500'] as $lvl): ?>
              <option value="<?= $lvl ?>" <?= ($_POST['level'] ?? '300') === $lvl ? 'selected' : '' ?>>
                <?= $lvl ?> Level
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label>Course Code</label>
            <input type="text" name="course_code" placeholder="e.g. CSC301"
              value="<?= htmlspecialchars($_POST['course_code'] ?? '') ?>" required/>
          </div>
          <div class="form-group">
            <label>Credit Units</label>
            <select name="credit_unit" required>
              <?php foreach ([1,2,3,4,6] as $u): ?>
              <option value="<?= $u ?>" <?= ($_POST['credit_unit'] ?? 3) == $u ? 'selected' : '' ?>>
                <?= $u ?> Unit<?= $u > 1 ? 's' : '' ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label>Course Title</label>
          <input type="text" name="course_title"
            placeholder="e.g. Data Structures & Algorithms"
            value="<?= htmlspecialchars($_POST['course_title'] ?? '') ?>" required/>
        </div>

        <div class="form-row-3">
          <div class="form-group">
            <label>Score (%)</label>
            <input type="number" name="score" min="0" max="100" placeholder="0 – 100"
              value="<?= htmlspecialchars($_POST['score'] ?? '') ?>" required/>
          </div>
          <div class="form-group">
            <label>Semester</label>
            <select name="semester" required>
              <option value="First"  <?= ($_POST['semester'] ?? '') === 'First'  ? 'selected' : '' ?>>First Semester</option>
              <option value="Second" <?= ($_POST['semester'] ?? '') === 'Second' ? 'selected' : '' ?>>Second Semester</option>
            </select>
          </div>
          <div class="form-group">
            <label>Session</label>
            <input type="text" name="session" placeholder="e.g. 2023/2024"
              value="<?= htmlspecialchars($_POST['session'] ?? '') ?>" required/>
          </div>
        </div>

        <p style="font-size:12px;color:var(--text3);margin-bottom:16px">
          Grade and grade point are calculated automatically from the score.
        </p>

        <button type="submit" class="btn btn-primary">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          Save Result
        </button>
      </form>
    </div>
  </div>
</body>
</html>
