// POS System — app.js

// Live clock
function updateClock() {
    const el = document.getElementById('clock');
    if (!el) return;
    const now = new Date();
    el.textContent = now.toLocaleDateString('en-PH', { weekday: 'short', month: 'short', day: 'numeric' })
        + ' ' + now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}
updateClock();
setInterval(updateClock, 1000);

// Auto-dismiss flash messages
const flash = document.getElementById('flashMsg');
if (flash) setTimeout(() => flash.style.opacity = '0', 4000);

// Sidebar toggle (mobile)
const toggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
if (toggle && sidebar) {
    toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
}
