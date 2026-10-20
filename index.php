<?php
require_once('db.php');
require 'include/funciones.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="imagenes/logo-devsite2.png">
    <title>DevSite - Conecta con Programadores</title>
    <link rel="stylesheet" href="recursos/styles.css">

   
    <style>
       
        .hero-logo-wrap {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }
        .hero-logo-wrap::before {
            content: "";
            position: absolute;
            width: 75%;
            aspect-ratio: 1;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(2, 169, 255, .38), transparent 70%);
            filter: blur(34px);
        }
        .hero-logo {
            position: relative;
            width: min(380px, 80%);
            height: auto;
            border-radius: 50%;
            box-shadow: 0 0 0 1px rgba(255, 255, 255, .08), 0 0 70px rgba(2, 169, 255, .3);
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .section-header .link-all {
            color: #60a5fa;
            text-decoration: none;
            font-size: .9rem;
            white-space: nowrap;
        }
        .section-header .link-all:hover { color: #fff; }

        /* ---------- Carrusel giratorio no puedo dejar que se me pierda otra vez ---------- */
        .tc-section { margin-top: 3.5rem; }
        .tc-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .tc-header h2 { margin: 0; font-size: 1.4rem; color: #fff; }
        .tc-controls { display: flex; gap: .5rem; }
        .tc-btn {
            width: 2.5rem; height: 2.5rem;
            border-radius: 50%;
            border: 1px solid #1f2a44;
            background: #111a2e;
            color: #cbd5e1;
            font-size: 1rem;
            cursor: pointer;
            transition: background .15s, border-color .15s, color .15s;
        }
        .tc-btn:hover { background: #3b82f6; border-color: #3b82f6; color: #fff; }
        .tc-btn:focus-visible { outline: 2px solid #93c5fd; outline-offset: 2px; }

        .tc-viewport { overflow: hidden; padding: .25rem 0; }
        .tc-track {
            display: flex;
            gap: 1.25rem;
            transition: transform .7s cubic-bezier(.22, .61, .36, 1);
            will-change: transform;
        }
        .tc-card {
            flex: 0 0 220px;
            box-sizing: border-box;
            padding: 1.5rem 1.25rem;
            text-align: center;
            background: #0f1626;
            border: 1px solid #1a2338;
            border-radius: 16px;
            transition: border-color .15s, transform .15s;
        }
        .tc-card:hover { border-color: #3b82f6; transform: translateY(-3px); }
        .tc-avatar {
            width: 62px; height: 62px;
            margin: 0 auto .9rem;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; color: #fff; font-size: 1.05rem;
        }
        .tc-avatar.pink   { background: #ec4899; }
        .tc-avatar.orange { background: #f97316; }
        .tc-avatar.cyan   { background: #06b6d4; }
        .tc-avatar.purple { background: #6366f1; }
        .tc-avatar.teal   { background: #14b8a6; }
        .tc-card h4 { margin: 0 0 .25rem; color: #fff; font-size: 1rem; }
        .tc-card p  { margin: 0 0 .9rem; color: #94a3b8; font-size: .875rem; }
        .tc-card a  { color: #60a5fa; text-decoration: none; font-size: .875rem; }
        .tc-card a:hover { color: #fff; }

        @media (max-width: 640px) {
            .tc-card { flex-basis: 190px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .tc-track { transition: none; }
        }
    </style>
<script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
    <?php incluirTemplates('header1', 'a'); ?>
    <section class="hero-banner">
        <div class="hero-container">
            <div class="hero-text-content">
                <h1>Programadores a precios accesibles para pequeños negocios que buscan mejorar</h1>
                <p>Encuentra talento independiente verificado y contrata directamente sin comisiones excesivas.</p>
                <div class="hero-actions">
                    <a href="buscar.php" class="btn-search-pill">Buscar programador</a>
                </div>
            </div>

            
            <div class="hero-illustration">
                <div class="hero-logo-wrap">
                    <img class="hero-logo" src="imagenes/logo-devsite2.png" alt="Logo" width="380" height="380">
                </div>
            </div>
        </div>
    </section>

    <main class="main-container">
        <div class="section-header">
            <h2>Programadores Destacados</h2>
            <a href="portafolio.html" class="link-all">Ver todos &rarr;</a>
        </div>

        <div class="cards-grid-wide">

            <article class="dev-card">
                <div class="dev-header">
                    <div class="dev-avatar purple">JS</div>
                    <div>
                        <h3>Jeremy Saavedra</h3>
                        <p class="role">Frontend & UI/UX</p>
                    </div>
                </div>
                <p class="dev-bio">Especializado en landing pages optimizadas para conversión y maquetación accesible.</p>
                <div class="tech-stack">
                    <span>HTML5</span><span>CSS3</span><span>JavaScript</span><span>UI/UX</span>
                </div>
                <div class="card-actions">
                    <a href="perfil.php?id=1" class="btn-secondary">Ver Perfil</a>
                    <a href="portafolio.html?id=1" class="btn-secondary">Portafolio</a>
                    <a href="contacto.php?dev=1" class="btn-primary">Contactar</a>
                </div>
            </article>


            <article class="dev-card">
                <div class="dev-header">
                    <div class="dev-avatar blue">AM</div>
                    <div>
                        <h3>Andrés Mendoza</h3>
                        <p class="role">Backend & Databases</p>
                    </div>
                </div>
                <p class="dev-bio">Experto en PHP, SQLite/MySQL y desarrollo de APIs para sistemas de gestión interna.</p>
                <div class="tech-stack">
                    <span>PHP</span><span>SQLite</span><span>MySQL</span><span>APIs</span>
                </div>
                <div class="card-actions">
                    <a href="perfil.php?id=2" class="btn-secondary">Ver Perfil</a>
                    <a href="portafolio.html?id=2" class="btn-secondary">Portafolio</a>
                    <a href="contacto.php?dev=2" class="btn-primary">Contactar</a>
                </div>
            </article>

            <article class="dev-card">
                <div class="dev-header">
                    <div class="dev-avatar green">SG</div>
                    <div>
                        <h3>Sofía Gómez</h3>
                        <p class="role">Python & Automatización</p>
                    </div>
                </div>
                <p class="dev-bio">Creación de scripts de automatización de tareas y scrapers de datos para negocios.</p>
                <div class="tech-stack">
                    <span>Python</span><span>Django</span><span>Pandas</span><span>APIs</span>
                </div>
                <div class="card-actions">
                    <a href="perfil.php?id=3" class="btn-secondary">Ver Perfil</a>
                    <a href="portafolio.html?id=3" class="btn-secondary">Portafolio</a>
                    <a href="contacto.php?dev=3" class="btn-primary">Contactar</a>
                </div>
            </article>
        </div>

        
        <section class="tc-section" aria-label="Más talentos disponibles">
            <div class="tc-header">
                <h2>★ Más Talentos Disponibles</h2>
                <div class="tc-controls">
                    <button type="button" class="tc-btn" id="prevBtn" aria-label="Anterior">&#10094;</button>
                    <button type="button" class="tc-btn" id="nextBtn" aria-label="Siguiente">&#10095;</button>
                </div>
            </div>

            <div class="tc-viewport">
                <div class="tc-track" id="carousel">
                    <div class="tc-card">
                        <div class="tc-avatar pink">DA</div>
                        <h4>Daniel Acosta</h4>
                        <p>Diseñador Web</p>
                        <a href="perfil.php?id=4">Ver Perfil &rarr;</a>
                    </div>
                    <div class="tc-card">
                        <div class="tc-avatar orange">CR</div>
                        <h4>Camila Rodríguez</h4>
                        <p>Dev WordPress</p>
                        <a href="perfil.php?id=5">Ver Perfil &rarr;</a>
                    </div>
                    <div class="tc-card">
                        <div class="tc-avatar cyan">LM</div>
                        <h4>Lucas Martínez</h4>
                        <p>E-commerce / Shopify</p>
                        <a href="perfil.php?id=6">Ver Perfil &rarr;</a>
                    </div>
                    <div class="tc-card">
                        <div class="tc-avatar purple">VP</div>
                        <h4>Valentina Pérez</h4>
                        <p>Full Stack Junior</p>
                        <a href="perfil.php?id=7">Ver Perfil &rarr;</a>
                    </div>
                    <div class="tc-card">
                        <div class="tc-avatar teal">MT</div>
                        <h4>Mateo Torres</h4>
                        <p>QA & Optimization</p>
                        <a href="perfil.php?id=8">Ver Perfil &rarr;</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
    (function () {
        const track = document.getElementById('carousel');
        const originals = Array.from(track.children);
        const n = originals.length;
        const AUTOPLAY_MS = 3000;

        function clone(el) {
            const c = el.cloneNode(true);
            c.setAttribute('aria-hidden', 'true');
            c.querySelectorAll('a').forEach(a => a.tabIndex = -1);
            return c;
        }
        originals.forEach(el => track.insertBefore(clone(el), originals[0]));
        for (let i = 0; i < 3; i++) originals.forEach(el => track.appendChild(clone(el)));

        let index = n;  
        let step = 0;
        let timer = null;

        function measure() {
            const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
            step = track.children[0].offsetWidth + gap;
        }
        function render(instant) {
            if (instant) track.style.transition = 'none';
            track.style.transform = 'translateX(' + (-index * step) + 'px)';
            if (instant) { void track.offsetWidth; track.style.transition = ''; }
        }
        function move(dir) {
            index += dir;
            render(false);
        }

  
        track.addEventListener('transitionend', function (e) {
            if (e.target !== track) return;
            let jumped = false;
            while (index >= 2 * n) { index -= n; jumped = true; }
            while (index < n)      { index += n; jumped = true; }
            if (jumped) render(true);
        });

        
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        function start() { if (!reduceMotion && !timer) timer = setInterval(() => move(1), AUTOPLAY_MS); }
        function stop()  { clearInterval(timer); timer = null; }

        document.getElementById('nextBtn').addEventListener('click', () => { move(1); stop(); start(); });
        document.getElementById('prevBtn').addEventListener('click', () => { move(-1); stop(); start(); });

        const section = track.closest('.tc-section');
        section.addEventListener('mouseenter', stop);
        section.addEventListener('mouseleave', start);
        section.addEventListener('focusin', stop);
        section.addEventListener('focusout', start);
        document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());

        window.addEventListener('resize', () => { measure(); render(true); });

        measure();
        render(true);
        start();
    })();
    </script>
</body>
</html>