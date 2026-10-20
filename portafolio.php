<?php
require_once('db.php');
require 'include/funciones.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DevSite - Portafolios</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" type="image/x-icon" href="imagenes/logo-devsite2.png">
  <link rel="stylesheet" href="recursos/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              dark: '#070a14',
              rail: '#0a0e1a',
              card: '#0d1322',
              border: '#1a2338',
              input: '#111a2e',
              accent: '#6366f1',
              violet: '#a78bfa'
            }
          },
          fontFamily: { sans: ['Inter', 'sans-serif'] }
        }
      }
    }
  </script>
  <style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { scrollbar-width: none; }

    .chip {
      padding: .4rem 1rem; border-radius: 9999px; font-size: .8rem; font-weight: 500;
      background: #0d1322; border: 1px solid #1a2338; color: #94a3b8; transition: all .15s;
      white-space: nowrap;
    }
    .chip:hover { color: #fff; border-color: #475569; }
    .chip.active { background: #6366f1; border-color: #6366f1; color: #fff; box-shadow: 0 4px 14px rgba(99,102,241,.35); }

    
    .tech-chip {
      padding: .2rem .6rem; border-radius: .375rem; font-size: .7rem; font-family: ui-monospace, monospace;
      background: #1e293b; color: #cbd5e1; border: 1px solid #334155; transition: all .15s;
    }
    .tech-chip:hover { border-color: #6366f1; }
    .tech-chip.active { background: rgba(99,102,241,.2); border-color: #6366f1; color: #c7d2fe; }

    
    .rail-link {
      width: 2.75rem; height: 2.75rem; display: flex; align-items: center; justify-content: center;
      border-radius: .85rem; color: #8b97b3; font-size: 1.05rem; transition: all .15s; position: relative;
    }
    .rail-link:hover { color: #fff; background: rgba(255,255,255,.06); }
    .rail-link.active { background: #6366f1; color: #fff; box-shadow: 0 6px 18px rgba(99,102,241,.4); }
    .rail-link::after {
      content: attr(data-label); position: absolute; left: calc(100% + .6rem); top: 50%; transform: translateY(-50%);
      background: #111a2e; border: 1px solid #1a2338; color: #e2e8f0; font-size: .75rem; padding: .25rem .6rem;
      border-radius: .5rem; white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity .15s; z-index: 60;
    }
    .rail-link:hover::after, .rail-link:focus-visible::after { opacity: 1; }
    .rail-link:focus-visible, .chip:focus-visible, .tech-chip:focus-visible { outline: 2px solid #a78bfa; outline-offset: 2px; }

    @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
  </style>

    <?php incluirTemplates('header1', 'b'); ?>

    <main class="flex-1 p-5 lg:p-8 w-full max-w-[1400px] mx-auto">

      <section class="relative overflow-hidden rounded-3xl border border-brand-border bg-gradient-to-br from-[#0d1226] via-[#0a0f1f] to-[#0b1022] mb-8">
        <div class="pointer-events-none absolute -right-24 top-1/2 -translate-y-1/2 w-[28rem] h-[28rem] rounded-full bg-indigo-600/20 blur-3xl"></div>

        <div class="relative grid lg:grid-cols-2 items-center gap-6 px-8 py-12 lg:px-14 lg:py-16">
          <div>
            <h1 class="text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-[1.1] mb-5">
              Explora los <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-400">portafolios</span> de nuestra comunidad
            </h1>
            <p class="text-slate-400 text-sm md:text-base max-w-lg leading-relaxed">
              Descubre el talento de programadores independientes, revisa sus proyectos y encuentra al profesional ideal para tu idea.
            </p>
          </div>

    
          <div class="hidden lg:flex justify-center">
            <img src="imagenes/logo-devsite2.png" alt="Logo de DevSite" width="320" height="320"
                 class="w-full max-w-[300px] h-auto rounded-full ring-1 ring-white/10 shadow-[0_0_70px_rgba(2,169,255,0.3)]">
          </div>
        </div>
      </section>

      
      <section class="mb-8">
        <div class="flex flex-col xl:flex-row gap-4 xl:items-center xl:justify-between">

          <div class="relative w-full xl:max-w-xl">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
            <input id="searchInput" type="text" autocomplete="off"
              placeholder="Buscar por nombre, tecnología o palabra clave..."
              class="w-full pl-11 pr-4 py-3 bg-brand-input/70 border border-brand-border rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-brand-accent transition">
          </div>

          <div class="flex items-center gap-2 flex-wrap xl:flex-nowrap">
           
            <div class="relative">
              <button id="filtersBtn" type="button" aria-expanded="false" aria-controls="filtersPanel"
                class="flex items-center gap-2 px-4 py-2 bg-brand-card border border-brand-border rounded-xl text-sm font-medium text-slate-300 hover:text-white transition">
                <i class="fa-solid fa-sliders text-xs"></i>
                <span>Filtros</span>
                <span id="filtersBadge" class="hidden min-w-[1.25rem] h-5 px-1 rounded-full bg-brand-accent text-white text-[11px] items-center justify-center">0</span>
                <i id="filtersChevron" class="fa-solid fa-chevron-down text-xs text-slate-500 transition-transform"></i>
              </button>

              <div id="filtersPanel" class="hidden absolute left-0 xl:left-auto xl:right-0 top-full mt-2 w-80 max-w-[90vw] bg-brand-card border border-brand-border rounded-2xl p-5 shadow-2xl shadow-black/50 z-40">
                <p class="text-xs font-semibold text-slate-300 mb-2">Ordenar por</p>
                <div class="flex gap-2 mb-5" id="sortGroup">
                  <button type="button" data-sort="relevance" class="chip active">Destacados</button>
                  <button type="button" data-sort="views" class="chip">Más vistos</button>
                  <button type="button" data-sort="recent" class="chip">Recientes</button>
                </div>

                <p class="text-xs font-semibold text-slate-300 mb-2">Tecnologías</p>
                <div id="techGroup" class="flex flex-wrap gap-1.5 mb-5"></div>

                <button id="clearFilters" type="button" class="text-xs text-indigo-300 hover:text-white transition">Limpiar filtros</button>
              </div>
            </div>

            
            <div id="categoryGroup" class="flex items-center gap-2 overflow-x-auto no-scrollbar">
              <button type="button" data-cat="todos" class="chip active">Todos</button>
              <button type="button" data-cat="web" class="chip">Web</button>
              <button type="button" data-cat="mobile" class="chip">Mobile</button>
              <button type="button" data-cat="backend" class="chip">Backend</button>
              <button type="button" data-cat="diseno" class="chip">Diseño</button>
              <button type="button" data-cat="otros" class="chip">Otros</button>
            </div>
          </div>
        </div>
      </section>

      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2">
          <i class="fa-solid fa-layer-group text-indigo-400 text-sm"></i>
          <h2 class="text-lg font-semibold text-white">Portafolios destacados</h2>
          <span id="resultCount" class="text-xs text-slate-500 ml-1"></span>
        </div>
        <button id="seeAll" type="button" class="text-xs text-indigo-300 hover:text-white transition flex items-center gap-1">
          <span>Ver todos</span>
          <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </button>
      </div>

      <div id="grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"></div>

      
      <div id="emptyState" class="hidden text-center py-16 border border-dashed border-brand-border rounded-2xl">
        <p class="text-white font-semibold mb-1">No hay portafolios con esos filtros</p>
        <p class="text-sm text-slate-400 mb-4">Prueba con otra palabra o quita alguna tecnología.</p>
        <button id="emptyClear" type="button" class="px-4 py-2 rounded-lg bg-brand-accent hover:bg-indigo-500 text-white text-sm transition">Limpiar filtros</button>
      </div>

    </main>
  </div>
</div>

<script>
  //datos esos que despues tengo que reemplazazar
  const portfolios = [
    { initials:'DA', color:'bg-slate-700 text-white border-brand-border', name:'Daniel Acosta', role:'Desarrollador Web',
      project:'TravelGo', desc:'Plataforma de viajes con recomendaciones personalizadas y mapa interactivo.',
      tech:['HTML','CSS','JavaScript','API'], cats:['web'], views:152, days:14, ago:'Hace 2 semanas' },
    { initials:'CR', color:'bg-slate-700 text-white border-brand-border', name:'Camila Rodríguez', role:'Desarrolladora Full Stack',
      project:'GestorPro', desc:'Sistema de gestión de proyectos con autenticación de usuarios y panel administrativo.',
      tech:['React','Node.js','MySQL','Tailwind'], cats:['web','backend'], views:230, days:30, ago:'Hace 1 mes' },
    { initials:'LM', color:'bg-purple-900/60 text-purple-300 border-purple-800/40', name:'Lucas Martínez', role:'Backend & APIs',
      project:'API de Biblioteca', desc:'API REST para la gestión de libros, autores y usuarios con documentación integrada.',
      tech:['Python','Django','PostgreSQL','JWT'], cats:['backend'], views:98, days:14, ago:'Hace 2 semanas' },
    { initials:'SG', color:'bg-emerald-900/60 text-emerald-300 border-emerald-800/40', name:'Sofía Gómez', role:'FrontEnd & UI/UX',
      project:'CreativeHub', desc:'Landing page para artistas y creadores de contenido con diseño moderno y responsivo.',
      tech:['HTML','CSS','JavaScript','Figma'], cats:['web','diseno'], views:187, days:60, ago:'Hace 2 meses' },
    { initials:'VP', color:'bg-pink-900/60 text-pink-300 border-pink-800/40', name:'Valentina Pérez', role:'Full Stack Junior',
      project:'TrackFit', desc:'App móvil para el seguimiento de hábitos y rutinas de ejercicio.',
      tech:['React Native','Firebase','JavaScript','UI/UX'], cats:['mobile','diseno'], views:76, days:30, ago:'Hace 1 mes' },
    { initials:'MT', color:'bg-blue-900/60 text-blue-300 border-blue-800/40', name:'Mateo Torres', role:'QA & Optimización',
      project:'EcoMarket', desc:'Tienda en línea con enfoque en productos sostenibles y experiencia de usuario optimizada.',
      tech:['PHP','MySQL','JavaScript','Bootstrap'], cats:['web','otros'], views:121, days:21, ago:'Hace 3 semanas' }
  ];

  const state = { q:'', cat:'todos', sort:'relevance', techs:new Set() };

  const $ = id => document.getElementById(id);
  const grid = $('grid'), emptyState = $('emptyState'), resultCount = $('resultCount');
  const norm = s => s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

  
  function cardHTML(p) {
    return `
    <article class="bg-brand-card border border-brand-border rounded-2xl p-5 hover:border-slate-600 transition flex flex-col justify-between group">
      <div>
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full ${p.color} flex items-center justify-center font-bold text-sm border">${p.initials}</div>
            <div>
              <h3 class="text-sm font-semibold text-white group-hover:text-indigo-300 transition">${p.name}</h3>
              <p class="text-xs text-slate-400">${p.role}</p>
            </div>
          </div>
          <a href="#" class="text-xs text-indigo-300 hover:underline flex items-center gap-1">
            <span>Ver perfil</span><i class="fa-solid fa-arrow-right text-[10px]"></i>
          </a>
        </div>
        <div class="bg-brand-input rounded-xl p-4 border border-brand-border/60 mb-4">
          <div class="text-xs font-semibold text-slate-300 mb-1">${p.project}</div>
          <p class="text-xs text-slate-400 leading-relaxed line-clamp-2">${p.desc}</p>
        </div>
        <div class="flex flex-wrap gap-1.5 mb-4">
          ${p.tech.map(t => `<span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 text-[11px] font-mono border border-slate-700/50">${t}</span>`).join('')}
        </div>
      </div>
      <div class="flex items-center justify-between text-[11px] text-slate-500 pt-3 border-t border-brand-border/40">
        <div class="flex items-center gap-1"><i class="fa-regular fa-eye"></i><span>${p.views}</span></div>
        <div class="flex items-center gap-1"><i class="fa-regular fa-clock"></i><span>${p.ago}</span></div>
      </div>
    </article>`;
  }


  function apply() {
    const q = norm(state.q.trim());
    let list = portfolios.filter(p => {
      if (state.cat !== 'todos' && !p.cats.includes(state.cat)) return false;
      if (state.techs.size && ![...state.techs].every(t => p.tech.includes(t))) return false;
      if (q) {
        const hay = norm([p.name, p.role, p.project, p.desc, ...p.tech].join(' '));
        if (!hay.includes(q)) return false;
      }
      return true;
    });

    if (state.sort === 'views')  list = [...list].sort((a, b) => b.views - a.views);
    if (state.sort === 'recent') list = [...list].sort((a, b) => a.days - b.days);

    grid.innerHTML = list.map(cardHTML).join('');
    emptyState.classList.toggle('hidden', list.length > 0);
    resultCount.textContent = `(${list.length})`;

    
    const n = state.techs.size + (state.sort !== 'relevance' ? 1 : 0);
    const badge = $('filtersBadge');
    badge.textContent = n;
    badge.classList.toggle('hidden', n === 0);
    badge.classList.toggle('inline-flex', n > 0);
  }

 
  $('searchInput').addEventListener('input', e => { state.q = e.target.value; apply(); });

  $('categoryGroup').addEventListener('click', e => {
    const btn = e.target.closest('[data-cat]'); if (!btn) return;
    state.cat = btn.dataset.cat;
    document.querySelectorAll('#categoryGroup .chip').forEach(c => c.classList.toggle('active', c === btn));
    apply();
  });

  $('sortGroup').addEventListener('click', e => {
    const btn = e.target.closest('[data-sort]'); if (!btn) return;
    state.sort = btn.dataset.sort;
    document.querySelectorAll('#sortGroup .chip').forEach(c => c.classList.toggle('active', c === btn));
    apply();
  });


  const allTechs = [...new Set(portfolios.flatMap(p => p.tech))].sort((a, b) => a.localeCompare(b));
  $('techGroup').innerHTML = allTechs.map(t => `<button type="button" class="tech-chip" data-tech="${t}">${t}</button>`).join('');
  $('techGroup').addEventListener('click', e => {
    const btn = e.target.closest('[data-tech]'); if (!btn) return;
    const t = btn.dataset.tech;
    state.techs.has(t) ? state.techs.delete(t) : state.techs.add(t);
    btn.classList.toggle('active', state.techs.has(t));
    apply();
  });


  const panel = $('filtersPanel'), btn = $('filtersBtn');
  function togglePanel(open) {
    const show = open ?? panel.classList.contains('hidden');
    panel.classList.toggle('hidden', !show);
    btn.setAttribute('aria-expanded', show);
    $('filtersChevron').classList.toggle('rotate-180', show);
  }
  btn.addEventListener('click', e => { e.stopPropagation(); togglePanel(); });
  document.addEventListener('click', e => { if (!panel.contains(e.target)) togglePanel(false); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') togglePanel(false); });

 
  function resetAll() {
    state.q = ''; state.cat = 'todos'; state.sort = 'relevance'; state.techs.clear();
    $('searchInput').value = '';
    document.querySelectorAll('#categoryGroup .chip').forEach(c => c.classList.toggle('active', c.dataset.cat === 'todos'));
    document.querySelectorAll('#sortGroup .chip').forEach(c => c.classList.toggle('active', c.dataset.sort === 'relevance'));
    document.querySelectorAll('.tech-chip').forEach(c => c.classList.remove('active'));
    apply();
  }
  $('clearFilters').addEventListener('click', resetAll);
  $('emptyClear').addEventListener('click', resetAll);
  $('seeAll').addEventListener('click', resetAll);

  apply();
</script>
</body>
</html>