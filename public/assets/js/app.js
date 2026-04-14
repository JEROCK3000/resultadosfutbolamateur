// public/assets/js/app.js — JavaScript global del sistema
// Resultados Fútbol | Vanilla JS

document.addEventListener('DOMContentLoaded', () => {

  // ── Menú hamburguesa (sidebar en móvil) ─────────────────────
  const btnMenu    = document.getElementById('btn-menu');
  const sidebar    = document.getElementById('sidebar');
  const overlay    = document.getElementById('sidebar-overlay');

  function openSidebar() {
    sidebar?.classList.add('is-open');
    overlay?.classList.add('is-open');
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    sidebar?.classList.remove('is-open');
    overlay?.classList.remove('is-open');
    document.body.style.overflow = '';
  }

  btnMenu?.addEventListener('click', () => {
    sidebar?.classList.contains('is-open') ? closeSidebar() : openSidebar();
  });
  overlay?.addEventListener('click', closeSidebar);

  // Cerrar con ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeSidebar();
  });

  // ── Marcar enlace activo en el sidebar ──────────────────────
  const currentPath = window.location.pathname;
  document.querySelectorAll('.nav-item a').forEach(link => {
    const linkPath = link.getAttribute('href');
    if (linkPath && currentPath.includes(linkPath) && linkPath !== '/') {
      link.classList.add('active');
    }
  });

  // ── Auto-ocultar alertas flash ───────────────────────────────
  const alerts = document.querySelectorAll('.alert[data-auto-hide]');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity .5s ease, max-height .5s ease';
      alert.style.opacity = '0';
      alert.style.maxHeight = '0';
      alert.style.overflow = 'hidden';
      setTimeout(() => alert.remove(), 500);
    }, 4000);
  });

  // ── Confirmación de eliminación ──────────────────────────────
  document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const msg = btn.dataset.confirm || '¿Está seguro de que desea eliminar este registro?';
      if (!confirm(msg)) {
        e.preventDefault();
      }
    });
  });

  // ── Selects encadenados: Liga → Equipos ──────────────────────
  // El controlador puede inyectar datos via data attributes
  const leagueSelect = document.getElementById('league_id');
  const homeTeam     = document.getElementById('home_team_id');
  const awayTeam     = document.getElementById('away_team_id');

  if (leagueSelect && homeTeam && awayTeam) {
    leagueSelect.addEventListener('change', function() {
      const leagueId = this.value;
      if (!leagueId) {
        [homeTeam, awayTeam].forEach(s => {
          s.innerHTML = '<option value="">— Seleccione equipo —</option>';
        });
        return;
      }
      fetch(`${window.BASE_URL}/equipos/por-liga/${leagueId}`)
        .then(r => r.json())
        .then(teams => {
          const opts = teams.map(t =>
            `<option value="${t.id}">${t.name}</option>`
          ).join('');
          const placeholder = '<option value="">— Seleccione equipo —</option>';
          homeTeam.innerHTML = placeholder + opts;
          awayTeam.innerHTML = placeholder + opts;
        })
        .catch(() => {});
    });
  }

});
