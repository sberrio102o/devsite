<?php

$ds_menu = [
  ['index.php',     'Inicio',                     'fa-solid fa-house'],
  ['registro.php',  'Crear cuenta',               'fa-regular fa-file-lines'],
  ['portafolio.php','Portafolio',                 'fa-solid fa-user-group'],
  ['contacto.php',  'Contactar a un programador', 'fa-regular fa-comments'],
  ['soporte.php',   'Soporte técnico',            'fa-solid fa-user-gear'],
  ['pagos.php',     'Pagos',                      'fa-regular fa-credit-card'],
];
   $ds_actual = $ds_activo ?? ($_GET['activo'] ?? basename($_SERVER['PHP_SELF']));
?>
<style>
body{padding-left:78px}
.ds-side{position:fixed;top:0;left:0;bottom:0;width:78px;z-index:60;background:#0a0e1a;border-right:1px solid #18223a;display:flex;flex-direction:column;align-items:center;padding:12px 0}
.ds-logo img{display:block;width:48px;height:48px;border-radius:50%}
.ds-nav{display:flex;flex-direction:column;align-items:center;gap:1.1rem;margin-top:1.6rem}
.ds-link{position:relative;width:2.75rem;height:2.75rem;display:flex;align-items:center;justify-content:center;border-radius:.85rem;color:#8b97b3;font-size:1.05rem;text-decoration:none;transition:all .15s}
.ds-link:hover{color:#fff;background:rgba(255,255,255,.06)}
.ds-link.active{background:#6366f1;color:#fff;box-shadow:0 6px 18px rgba(99,102,241,.4)}
.ds-link:focus-visible{outline:2px solid #a78bfa;outline-offset:2px}
.ds-link::after{content:attr(data-label);position:absolute;left:calc(100% + .6rem);top:50%;transform:translateY(-50%);background:#111a2e;border:1px solid #18223a;color:#e2e8f0;font:.75rem/1.2 system-ui,sans-serif;padding:.3rem .6rem;border-radius:.5rem;white-space:nowrap;opacity:0;pointer-events:none;transition:opacity .15s}
.ds-link:hover::after,.ds-link:focus-visible::after{opacity:1}
.ds-end{margin-top:auto;width:52px;height:52px;border-radius:50%;opacity:.9}
@media (max-width:767px){.ds-side{display:none}body{padding-left:0}}
@media (prefers-reduced-motion:reduce){.ds-link{transition:none}}
</style>
<aside class="ds-side" aria-label="Navegación lateral">
  <a class="ds-logo" href="index.php" aria-label="DevSide - Inicio"><img src="imagenes/logo-devsite2.png" alt="DevSide"></a>
  <nav class="ds-nav">
    <?php foreach ($ds_menu as [$href, $label, $icono]): ?>
      <a href="<?= $href ?>" class="ds-link<?= $href === $ds_actual ? ' active' : '' ?>" data-label="<?= $label ?>" aria-label="<?= $label ?>"<?= $href === $ds_actual ? ' aria-current="page"' : '' ?>><i class="<?= $icono ?>"></i></a>
    <?php endforeach; ?>
  </nav>
  <!-- <img class="ds-end" src="imagenes/logo-devsite2.png" alt="logo-devsite"> -->
</aside>

