<?php
require_once 'includes/config.php';

$error   = '';
$student = null;
$results = [];
$semesters = [];
$sessions  = [];

$sel_semester = '';
$sel_session  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matric = clean($conn, $_POST['matric_number'] ?? '');

    if (empty($matric)) {
        $error = 'Please enter your matric number.';
    } else {
        // Fetch student
        $stmt = $conn->prepare("SELECT * FROM students WHERE matric_number = ?");
        $stmt->bind_param('s', $matric);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            $error = 'No student found with matric number <strong>' . htmlspecialchars($matric) . '</strong>. Please check and try again.';
        } else {
            $student = $res->fetch_assoc();

            // Get available sessions & semesters for this student
            $meta = $conn->query("SELECT DISTINCT session, semester FROM results WHERE matric_number = '" . $conn->real_escape_string($matric) . "' ORDER BY session DESC, semester ASC");
            while ($row = $meta->fetch_assoc()) {
                if (!in_array($row['session'], $sessions))   $sessions[]  = $row['session'];
                if (!in_array($row['semester'], $semesters)) $semesters[] = $row['semester'];
            }

            $sel_session  = clean($conn, $_POST['session']  ?? ($sessions[0]  ?? ''));
            $sel_semester = clean($conn, $_POST['semester'] ?? ($semesters[0] ?? ''));

            // Fetch results
            $rStmt = $conn->prepare("SELECT * FROM results WHERE matric_number = ? AND session = ? AND semester = ? ORDER BY course_code ASC");
            $rStmt->bind_param('sss', $matric, $sel_session, $sel_semester);
            $rStmt->execute();
            $results = $rStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
    }
}

$gpa    = $student ? calculateGPA($results) : 0;
$remark = $student ? getGPARemark($gpa) : ['', ''];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>UniCheck — Student Result Portal</title>
  <link rel="stylesheet" href="assets/css/style.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

  <!-- HEADER -->
  <header class="header">
    <div class="container header-inner">
      <div class="logo">
        <div class="logo-icon">U</div>
        <div>
          <span class="logo-name">UniCheck</span>
          <span class="logo-sub">Result Portal</span>
        </div>
      </div>
      <a href="admin/login.php" class="btn btn-outline-sm">Admin Login</a>
    </div>
  </header>

  <main class="main">
    <div class="container">

      <!-- HERO -->
      <div class="portal-hero">
        <h1 class="portal-title">Student Result Checker</h1>
        <p class="portal-sub">Enter your matric number to view your academic results, GPA, and semester performance.</p>
      </div>

      <!-- SEARCH FORM -->
      <div class="card search-card">
        <form method="POST" action="" id="searchForm">
          <div class="search-row">
            <div class="form-group" style="flex:1">
              <label for="matric_number">Matric Number</label>
              <input
                type="text"
                id="matric_number"
                name="matric_number"
                placeholder="e.g. AHU/2021/001"
                value="<?= htmlspecialchars($_POST['matric_number'] ?? '') ?>"
                required
                autocomplete="off"
              />
            </div>

            <?php if ($student && count($sessions) > 1): ?>
            <div class="form-group">
              <label for="session">Session</label>
              <select name="session" id="session">
                <?php foreach ($sessions as $s): ?>
                  <option value="<?= $s ?>" <?= $s === $sel_session ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php endif; ?>

            <?php if ($student && count($semesters) > 1): ?>
            <div class="form-group">
              <label for="semester">Semester</label>
              <select name="semester" id="semester">
                <?php foreach ($semesters as $sem): ?>
                  <option value="<?= $sem ?>" <?= $sem === $sel_semester ? 'selected' : '' ?>><?= $sem ?> Semester</option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php endif; ?>

            <div class="form-group form-group--btn">
              <label>&nbsp;</label>
              <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                Check Result
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- ERROR -->
      <?php if ($error): ?>
      <div class="alert alert-error">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span><?= $error ?></span>
      </div>
      <?php endif; ?>

      <!-- RESULTS -->
      <?php if ($student && !empty($results)): ?>

        <!-- Student Info -->
        <div class="student-info-bar card">
          <div class="student-info-grid">
            <div class="info-item">
              <span class="info-label">Full Name</span>
              <span class="info-value"><?= htmlspecialchars($student['full_name']) ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Matric Number</span>
              <span class="info-value"><?= htmlspecialchars($student['matric_number']) ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Department</span>
              <span class="info-value"><?= htmlspecialchars($student['department']) ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Level</span>
              <span class="info-value"><?= $student['level'] ?> Level</span>
            </div>
            <div class="info-item">
              <span class="info-label">Session</span>
              <span class="info-value"><?= $sel_session ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Semester</span>
              <span class="info-value"><?= $sel_semester ?> Semester</span>
            </div>
          </div>
        </div>

        <!-- GPA Summary -->
        <div class="gpa-banner card">
          <div class="gpa-main">
            <span class="gpa-label">Semester GPA</span>
            <span class="gpa-value"><?= number_format($gpa, 2) ?></span>
            <span class="gpa-max">/ 5.00</span>
          </div>
          <div class="gpa-divider"></div>
          <div class="gpa-remark" style="color:<?= $remark[1] ?>">
            <span class="remark-dot" style="background:<?= $remark[1] ?>"></span>
            <?= $remark[0] ?>
          </div>
          <div class="gpa-units">
            <?= array_sum(array_column($results, 'credit_unit')) ?> Credit Units
          </div>
        </div>

        <!-- Results Table -->
        <div class="card" id="resultOutput">
          <div class="table-header">
            <h2 class="table-title">
              <?= $sel_semester ?> Semester Result — <?= $sel_session ?>
            </h2>
            <button onclick="printResult()" class="btn btn-outline-sm no-print">
              <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
              Print / Download PDF
            </button>
          </div>

          <div class="table-wrap">
            <table class="result-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Course Code</th>
                  <th>Course Title</th>
                  <th class="center">Credit Units</th>
                  <th class="center">Score (%)</th>
                  <th class="center">Grade</th>
                  <th class="center">Grade Point</th>
                  <th class="center">Quality Point</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($results as $i => $r): ?>
                <tr>
                  <td class="muted"><?= $i + 1 ?></td>
                  <td><strong><?= htmlspecialchars($r['course_code']) ?></strong></td>
                  <td><?= htmlspecialchars($r['course_title']) ?></td>
                  <td class="center"><?= $r['credit_unit'] ?></td>
                  <td class="center"><?= $r['score'] ?></td>
                  <td class="center">
                    <span class="grade-badge grade-<?= strtolower($r['grade']) ?>">
                      <?= $r['grade'] ?>
                    </span>
                  </td>
                  <td class="center"><?= number_format($r['grade_point'], 1) ?></td>
                  <td class="center"><?= number_format($r['grade_point'] * $r['credit_unit'], 1) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr class="tfoot-row">
                  <td colspan="3"><strong>Total / GPA</strong></td>
                  <td class="center"><strong><?= array_sum(array_column($results, 'credit_unit')) ?></strong></td>
                  <td colspan="2"></td>
                  <td class="center"><strong><?= number_format($gpa, 2) ?></strong></td>
                  <td class="center"><strong><?= number_format(array_sum(array_map(fn($r) => $r['grade_point'] * $r['credit_unit'], $results)), 1) ?></strong></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Grade Key -->
          <div class="grade-key no-print">
            <span class="key-label">Grade Key:</span>
            <span class="grade-badge grade-a">A — 70–100 (5.0)</span>
            <span class="grade-badge grade-b">B — 60–69 (4.0)</span>
            <span class="grade-badge grade-c">C — 50–59 (3.0)</span>
            <span class="grade-badge grade-d">D — 45–49 (2.0)</span>
            <span class="grade-badge grade-e">E — 40–44 (1.0)</span>
            <span class="grade-badge grade-f">F — 0–39 (0.0)</span>
          </div>
        </div>

      <?php elseif ($student && empty($results)): ?>
        <div class="alert alert-info">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          No results found for <strong><?= $sel_semester ?> Semester, <?= $sel_session ?></strong>. Results may not have been uploaded yet.
        </div>
      <?php endif; ?>

    </div>
  </main>

  <footer class="footer">
    <div class="container">
      <span>© <?= date('Y') ?> UniCheck &nbsp;·&nbsp; Built by <a href="https://github.com/khaleedolawale" target="_blank">Khaleed Olawale</a></span>
    </div>
  </footer>

  <script src="assets/js/main.js"></script>
</body>
</html>
