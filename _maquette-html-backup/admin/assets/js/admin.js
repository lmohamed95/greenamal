/* GreenAmal Admin — interactions */

document.addEventListener('DOMContentLoaded', () => {
  /* Toolbar tabs */
  document.querySelectorAll('.toolbar-tabs').forEach(group => {
    group.querySelectorAll('.toolbar-tab').forEach(tab => {
      tab.addEventListener('click', () => {
        group.querySelectorAll('.toolbar-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
      });
    });
  });

  /* Select-all checkbox */
  document.querySelectorAll('[data-select-all]').forEach(master => {
    const tableId = master.dataset.selectAll;
    const table = document.getElementById(tableId);
    if (!table) return;
    master.addEventListener('change', () => {
      table.querySelectorAll('tbody input[type="checkbox"]').forEach(cb => cb.checked = master.checked);
    });
  });

  /* Sidebar mobile toggle (placeholder for later) */

  /* Topbar search keyboard shortcut */
  const search = document.querySelector('.topbar-search input');
  if (search) {
    document.addEventListener('keydown', (e) => {
      if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        search.focus();
      }
    });
  }
});
