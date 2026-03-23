// ── UniCheck — Main JS ──

// Print / PDF
function printResult() {
  window.print();
}

// Auto-submit form when session/semester dropdowns change
document.addEventListener('DOMContentLoaded', () => {
  const sessionSel  = document.getElementById('session');
  const semesterSel = document.getElementById('semester');

  if (sessionSel)  sessionSel.addEventListener('change',  () => document.getElementById('searchForm').submit());
  if (semesterSel) semesterSel.addEventListener('change', () => document.getElementById('searchForm').submit());
});
