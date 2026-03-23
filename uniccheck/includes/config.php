<?php
// ── UniCheck Database Configuration ──
// Update these values to match your local XAMPP setup

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // your MySQL username
define('DB_PASS', '');           // your MySQL password (empty by default in XAMPP)
define('DB_NAME', 'uniccheck');
define('APP_NAME', 'UniCheck');
define('APP_VERSION', '1.0');

// Connect
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;text-align:center;">
        <h2 style="color:#c0392b">Database Connection Failed</h2>
        <p>' . $conn->connect_error . '</p>
        <p style="color:#666">Check your DB credentials in <code>includes/config.php</code></p>
    </div>');
}

$conn->set_charset('utf8mb4');

// Helper: sanitize input
function clean($conn, $val) {
    return $conn->real_escape_string(htmlspecialchars(trim($val)));
}

// Helper: grade from score
function getGrade($score) {
    if ($score >= 70) return ['A', 5.0];
    if ($score >= 60) return ['B', 4.0];
    if ($score >= 50) return ['C', 3.0];
    if ($score >= 45) return ['D', 2.0];
    if ($score >= 40) return ['E', 1.0];
    return ['F', 0.0];
}

// Helper: calculate GPA
function calculateGPA($results) {
    $totalPoints = 0;
    $totalUnits  = 0;
    foreach ($results as $r) {
        $totalPoints += $r['grade_point'] * $r['credit_unit'];
        $totalUnits  += $r['credit_unit'];
    }
    return $totalUnits > 0 ? round($totalPoints / $totalUnits, 2) : 0;
}

// Helper: GPA remark
function getGPARemark($gpa) {
    if ($gpa >= 4.50) return ['First Class', '#16a34a'];
    if ($gpa >= 3.50) return ['Second Class Upper', '#2563eb'];
    if ($gpa >= 2.40) return ['Second Class Lower', '#7c3aed'];
    if ($gpa >= 1.50) return ['Third Class', '#d97706'];
    if ($gpa >= 1.00) return ['Pass', '#64748b'];
    return ['Fail', '#dc2626'];
}

// Session check helper
function requireAdminLogin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: ../admin/login.php');
        exit;
    }
}
?>
