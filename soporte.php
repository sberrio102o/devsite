<?php
require 'include/funciones.php';
$WHATSAPP = '';   // hay que poner estas maricadas-
$CORREO   = '';   // Ej: 'soporte@devsite.com' tal vez debamos crearlo-
$RESPUESTA = '24 a 48 horas'; 

$temas = [
  'pagos'       => ['fa-regular fa-credit-card', 'Pagos y facturación', 'Problemas con pagos, reembolsos, facturas o métodos de pago.'],
  'cuenta'      => ['fa-regular fa-user',        'Cuenta y acceso',     'Recupera tu cuenta, restablece contraseña o actualiza tus datos.'],
  'proyectos'   => ['fa-regular fa-folder-open', 'Proyectos',           'Dudas sobre el estado de tus proyectos, entregas o requisitos.'],
  'programadores'=> ['fa-solid fa-code',         'Programadores',       'Reporta inconvenientes con programadores o la plataforma.'],
  'seguridad'   => ['fa-solid fa-shield-halved', 'Seguridad',           'Problemas de seguridad, reportes de usuarios o actividad sospechosa.'],
  'otros'       => ['fa-regular fa-comment-dots','Otros',               'Si tu duda no está en las opciones anteriores, escríbenos.'],
];
$faq = [
  '¿Cómo se hace un reembolso?',
  '¿Cuánto tarda en llegar un pago?',
  '¿Cómo recupero mi cuenta?',
  '¿Qué hago si hay un problema con mi proyecto?',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Soporte técnico · DevSite</title>
<link rel="icon" type="image/x-icon" href="imagenes/logo-devsite2.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--bg:#060a17;--panel:#0b1226;--line:#1a2447;--txt:#e8ecff;--mut:#8a94b8;--ac:#5865f2;--ac2:#8b94ff}
*{box-sizing:border-box;margin:0}
body{background:var(--bg);color:var(--txt);font:15px/1.5 "Segoe UI",system-ui,sans-serif;min-height:100vh}
a{color:inherit;text-decoration:none}
header{display:flex;align-items:center;gap:40px;padding:12px 28px;border-bottom:1px solid var(--line);height:72px}
.brand{display:flex;align-items:center;gap:12px;font-size:26px;font-weight:700}
.brand img{width:44px;height:44px;border-radius:50%}.brand b{color:#1ea8ff}
header nav{display:flex;gap:30px;margin-left:20px}
header nav a{padding:24px 0;border-bottom:3px solid transparent}
header nav a:hover{color:var(--ac2)}
header nav a.on{color:var(--ac2);border-color:var(--ac)}
main{display:grid;grid-template-columns:minmax(0,1.9fr) minmax(300px,1fr);gap:28px;padding:28px}
.card{background:var(--panel);border:1px solid var(--line);border-radius:18px;padding:26px}
.hero{display:flex;align-items:center;gap:28px;background:linear-gradient(135deg,#0d1636,#0a1126)}
.hero .ic{flex:none;width:130px;height:130px;border-radius:50%;display:grid;place-items:center;font-size:56px;color:#fff;background:linear-gradient(135deg,#4f58e8,#1ea8ff);box-shadow:0 0 50px rgba(30,168,255,.25)}
h1{font-size:40px;line-height:1.15}h1 span,h2 span{color:var(--ac)}
.hero p{color:#c9d1f2;font-size:18px;margin:10px 0 18px}
.search{position:relative}
.search i{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--mut)}
input,select,textarea{width:100%;background:#080e20;border:1px solid var(--line);border-radius:10px;padding:12px 14px;color:var(--txt);font:inherit}
.search input{padding-left:44px}
input:focus,select:focus,textarea:focus,button:focus-visible,a:focus-visible{outline:2px solid var(--ac2);outline-offset:1px}
h2{font-size:28px;margin:34px 0 4px}.lead{color:var(--mut);margin-bottom:18px}
.temas{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.tema{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:18px;text-align:left;color:var(--txt);font:inherit;cursor:pointer;display:block;position:relative;transition:.15s}
.tema:hover{border-color:var(--ac);transform:translateY(-2px)}
.tema .ti{width:42px;height:42px;border-radius:10px;background:#142052;color:#6f8bff;display:grid;place-items:center;font-size:20px;margin-bottom:12px}
.tema b{display:block;margin-bottom:4px}.tema small{color:var(--mut);font-size:13px;display:block}
.tema .ch{position:absolute;top:20px;right:18px;color:var(--mut);font-size:13px}
.info{display:flex;align-items:center;gap:16px;margin-top:24px;border:1px solid #28357a;background:#0a1230;border-radius:14px;padding:18px 22px}
.info>i{font-size:26px;color:var(--ac2)}.info div{flex:1}.info small{color:var(--ac2);display:block}
.btn-o{border:1px solid var(--ac);color:var(--ac2);border-radius:8px;padding:9px 14px;font-size:13px;white-space:nowrap;background:none;cursor:pointer;font-family:inherit}
.btn-o:hover{background:var(--ac);color:#fff}
.col{display:grid;gap:22px;align-content:start}
.col h3{font-size:20px;display:flex;align-items:center;gap:12px;margin-bottom:6px}.col h3 i{color:var(--ac2)}
.col p.s{color:var(--mut);font-size:13px;margin-bottom:16px}
label{display:block;font-size:13px;margin:14px 0 6px}
textarea{min-height:100px;resize:vertical}
.go{width:100%;margin-top:18px;background:var(--ac);color:#fff;border:0;border-radius:10px;padding:13px;font:inherit;font-weight:600;cursor:pointer;display:flex;justify-content:center;gap:10px;align-items:center}
.go:hover{background:#6c78ff}
.faq button{width:100%;display:flex;justify-content:space-between;align-items:center;background:none;border:0;border-bottom:1px solid var(--line);color:var(--txt);font:inherit;font-size:14px;padding:12px 2px;cursor:pointer;text-align:left}
.faq button:last-child{border:0}.faq button:hover{color:var(--ac2)}
.urg{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:14px}
.urg a{border:1px solid var(--line);border-radius:999px;padding:10px;display:flex;justify-content:center;gap:8px;align-items:center;font-size:14px}
.urg a:hover{border-color:var(--ac);background:#0d1635}
.vacio{display:none;color:var(--mut);padding:14px 2px}
@media(max-width:1100px){main{grid-template-columns:1fr}header nav{display:none}}
@media(max-width:700px){.temas{grid-template-columns:1fr 1fr}.hero{flex-direction:column;text-align:center}h1{font-size:30px}}
@media(max-width:480px){.temas{grid-template-columns:1fr}}
@media(prefers-reduced-motion:reduce){*{transition:none!important}}
</style>
</head>
<body>

<?php incluirTemplates('sidebar'); ?>

<!-- <header>
  <a class="brand" href="index.php"><img src="img/logo.png" alt="">Dev<b>Site</b></a>
  <nav>
    <a href="index.php">Inicio</a><a href="portafolio.php">Proyectos</a>
    <a href="buscar.php">Programadores</a><a href="pagos.php">Pagos</a><a class="on" href="soporte.php">Soporte</a>
  </nav>
</header> -->

<main>
<div>
  <section class="card hero">
    <div class="ic"><i class="fa-solid fa-headset"></i></div>
    <div style="flex:1">
      <h1>Centro de <span>Soporte</span></h1>
      <p>Encuentra respuestas a tus preguntas, resuelve problemas y recibe ayuda de nuestro equipo.</p>
      <div class="search"><i class="fa-solid fa-magnifying-glass"></i><input id="q" type="search" placeholder="Busca una pregunta o tema..." autocomplete="off" aria-label="Buscar en soporte"></div>
    </div>
  </section>

  <h2>¿En qué podemos <span>ayudarte?</span></h2>
  <p class="lead">Selecciona el tema que necesitas o contáctanos directamente.</p>

  <div class="temas" id="temas">
    <?php foreach ($temas as $k => [$ico, $tit, $desc]): ?>
      <button type="button" class="tema" data-tema="<?= $k ?>">
        <div class="ti"><i class="<?= $ico ?>"></i></div>
        <b><?= $tit ?></b><small><?= $desc ?></small>
        <i class="fa-solid fa-chevron-right ch"></i>
      </button>
    <?php endforeach; ?>
  </div>
  <p class="vacio" id="vacio">No encontramos resultados. Prueba con otra palabra o escríbenos desde el formulario.</p>

  <div class="info">
    <i class="fa-solid fa-circle-info"></i>
    <div>Nuestro equipo de soporte responde en un promedio de <?= htmlspecialchars($RESPUESTA) ?>.
      <small>Mientras tanto, puedes revisar nuestras preguntas frecuentes.</small></div>
    <a class="btn-o" href="#faq">Ver preguntas frecuentes <i class="fa-solid fa-arrow-right"></i></a>
  </div>
</div>

<div class="col">
  <section class="card" id="contacto">
    <h3><i class="fa-regular fa-paper-plane"></i> Contáctanos</h3>
    <p class="s">¿No encontraste lo que buscabas? Escríbenos y te ayudaremos lo antes posible.</p>
    <form action="enviar_soporte.php" method="POST" id="form">
      <label for="tipo">Tipo de consulta</label>
      <select id="tipo" name="tipo" required>
        <option value="">Selecciona una opción</option>
        <?php foreach ($temas as $k => $t): ?><option value="<?= $k ?>"><?= $t[1] ?></option><?php endforeach; ?>
      </select>
      <label for="asunto">Asunto</label>
      <input id="asunto" name="asunto" maxlength="120" placeholder="Escribe un asunto breve" required>
      <label for="mensaje">Mensaje</label>
      <textarea id="mensaje" name="mensaje" maxlength="2000" placeholder="Describe tu problema o duda..." required></textarea>
      <button class="go" type="submit"><i class="fa-regular fa-paper-plane"></i> Enviar mensaje</button>
    </form>
  </section>

  <section class="card faq" id="faq">
    <h3><i class="fa-solid fa-book-open"></i> Preguntas frecuentes</h3>
    <?php foreach ($faq as $f): ?>
      <button type="button" data-faq="<?= htmlspecialchars($f) ?>"><span><?= htmlspecialchars($f) ?></span><i class="fa-solid fa-chevron-right" style="font-size:12px;color:var(--mut)"></i></button>
    <?php endforeach; ?>
  </section>

<?php if ($WHATSAPP || $CORREO): ?>
  <section class="card">
    <h3><i class="fa-solid fa-bolt"></i> ¿Necesitas soporte urgente?</h3>
    <p class="s">Puedes escribirnos directamente por <?= $WHATSAPP ? 'WhatsApp' : '' ?><?= $WHATSAPP && $CORREO ? ' o ' : '' ?><?= $CORREO ? 'correo electrónico' : '' ?>.</p>
    <div class="urg">
      <?php if ($WHATSAPP): ?><a href="https://wa.me/<?= preg_replace('/\D/', '', $WHATSAPP) ?>" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a><?php endif; ?>
      <?php if ($CORREO): ?><a href="mailto:<?= htmlspecialchars($CORREO) ?>"><i class="fa-regular fa-envelope"></i> Correo</a><?php endif; ?>
    </div>
  </section>
<?php endif; ?>
</div>
</main>

<script>
const $ = s => document.querySelector(s), $$ = s => [...document.querySelectorAll(s)];
const norm = s => s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

$$('.tema').forEach(t => t.onclick = () => {
  $('#tipo').value = t.dataset.tema;
  $('#contacto').scrollIntoView({behavior: 'smooth', block: 'center'});
  $('#asunto').focus({preventScroll: true});
});

$$('[data-faq]').forEach(b => b.onclick = () => {
  $('#asunto').value = b.dataset.faq;
  $('#contacto').scrollIntoView({behavior: 'smooth', block: 'center'});
  $('#mensaje').focus({preventScroll: true});
});

$('#q').oninput = e => {
  const q = norm(e.target.value.trim());
  let n = 0;
  $$('.tema, [data-faq]').forEach(el => {
    const ok = !q || norm(el.textContent).includes(q);
    el.style.display = ok ? '' : 'none';
    if (ok && el.classList.contains('tema')) n++;
  });
  $('#vacio').style.display = q && n === 0 ? 'block' : 'none';
};
</script>
</body>
</html>