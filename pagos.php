<?php
require 'include/funciones.php';
?>
<?php
$COMISION = 0.05; // 5% de la plataforma...creo 

$proyectos = [];
$id = isset($_GET['proyecto']) ? (int)$_GET['proyecto'] : 0;
$p = $proyectos[$id] ?? null;

$precio   = $p['precio'] ?? 0;
$comision = (int)round($precio * $COMISION);
$total    = $precio + $comision;
$cop = fn($n) => '$ ' . number_format($n, 0, ',', '.');
$referencia = 'DS-' . strtoupper(substr(md5($id . date('Ymd')), 0, 8));

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/x-icon" href="imagenes/logo-devsite2.png">
<title>Realizar pago · DevSite</title>
<link rel="icon" href="img/logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--bg:#060a17;--panel:#0b1226;--line:#1a2447;--txt:#e8ecff;--mut:#8a94b8;--ac:#5865f2;--ac2:#7a86ff}
*{box-sizing:border-box;margin:0}
body{background:var(--bg);color:var(--txt);font:15px/1.5 "Segoe UI",system-ui,sans-serif;min-height:100vh}
svg.i{width:22px;height:22px;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}
header{display:flex;align-items:center;gap:40px;padding:12px 28px;border-bottom:1px solid var(--line);height:72px}
.brand{display:flex;align-items:center;gap:12px;font-size:26px;font-weight:700;text-decoration:none;color:var(--txt)}
.brand img{width:44px;height:44px;border-radius:50%}
.brand b{color:#1ea8ff}
header nav{display:flex;gap:30px;flex:1;margin-left:20px}
header nav a{color:var(--txt);text-decoration:none;padding:24px 0;border-bottom:3px solid transparent}
header nav a.on{color:var(--ac2);border-color:var(--ac)}
main{display:grid;grid-template-columns:minmax(0,1.9fr) minmax(0,1fr);gap:28px;padding:28px}
.card{background:var(--panel);border:1px solid var(--line);border-radius:18px;padding:28px}
h1{font-size:28px;display:flex;align-items:center;gap:14px}
h1 a{color:var(--ac2);display:grid}
.sub{color:var(--mut);margin:6px 0 22px 38px;max-width:520px}
.steps{display:flex;align-items:flex-start;margin-bottom:28px}
.st{display:flex;flex-direction:column;align-items:center;gap:6px;font-size:13px;color:var(--mut);width:90px}
.st i{width:32px;height:32px;border-radius:50%;background:#18224a;display:grid;place-items:center;font-style:normal;color:#cdd3f5;font-weight:600}
.st.on i{background:var(--ac);color:#fff}.st.on{color:var(--txt)}
.ln{flex:1;height:2px;background:var(--line);margin-top:15px}.ln.on{background:var(--ac)}
h2{font-size:18px;margin:6px 0 14px}
.met{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:26px}
.met button{background:var(--panel);border:1px solid var(--line);border-radius:12px;color:var(--txt);padding:16px 8px;display:flex;flex-direction:column;align-items:center;gap:10px;font:inherit;font-size:13px;cursor:pointer;position:relative}
.met button.on{border:2px solid var(--ac);background:#0d1635}
.met button.on::after{content:"";position:absolute;top:10px;right:10px;width:12px;height:12px;border-radius:50%;background:var(--ac2)}
.box{border:1px solid var(--line);border-radius:14px;padding:18px}
.g{display:grid;grid-template-columns:1fr 1fr;gap:16px}.f{margin-bottom:16px}.f.full{grid-column:1/-1}
label{display:block;font-size:13px;margin-bottom:6px}
input{width:100%;background:#080e20;border:1px solid var(--line);border-radius:8px;padding:11px 14px;color:var(--txt);font:inherit}
input:focus,button:focus-visible{outline:2px solid var(--ac2);outline-offset:1px}
input.err{border-color:#ff6b7a}
.tipos{display:flex;gap:12px}
.tipos span{flex:1;height:46px;border:1px solid var(--line);border-radius:8px;display:grid;place-items:center;font-weight:800;font-size:15px;color:var(--mut)}
.tipos span.on{border:2px solid var(--ac);color:#fff}
.go{width:100%;margin-top:22px;background:var(--ac);color:#fff;border:0;border-radius:10px;padding:15px;font:inherit;font-weight:600;cursor:pointer;display:flex;justify-content:center;gap:10px;align-items:center}
.go:hover{background:var(--ac2)}
.ssl{text-align:center;font-size:12px;color:var(--mut);margin-top:12px}
.pane{display:none}.pane.on{display:block}
.msg{color:var(--mut);padding:6px 2px}
.msg b{color:var(--txt)}
.proj{display:flex;gap:16px;padding-bottom:20px;border-bottom:1px solid var(--line);margin-bottom:18px}
.proj img{width:84px;height:84px;border-radius:14px;border:1px solid var(--line);padding:6px;background:#080e20}
.proj h3{font-size:16px;line-height:1.3}.proj small{color:var(--mut);display:block;margin-top:6px}
.proj em{color:var(--ac2);font-style:normal}
.row{display:flex;justify-content:space-between;padding:7px 0}
.tot{display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--line);margin-top:12px;padding-top:18px;font-size:18px;font-weight:600}
.tot b{font-size:26px;color:var(--ac2)}
.safe{border:1px solid var(--ac);border-radius:12px;padding:16px;margin-top:22px;background:#0a1230}
.safe p{color:var(--mut);font-size:13px;margin-top:4px}
.ok{display:grid;place-items:center;padding:30px 0 0}
.ok img{width:150px;opacity:.95}
.comp{text-align:center;padding:10px 0}
.comp .chk{width:64px;height:64px;border-radius:50%;background:var(--ac);display:grid;place-items:center;margin:0 auto 14px}
@media(max-width:1000px){main{grid-template-columns:1fr}header nav{display:none}.met{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.g{grid-template-columns:1fr}.sub{margin-left:0}}
</style>
</head>
<body>

<?php incluirTemplates('sidebar'); ?>

<main>
<section class="card">
  <h1><a href="javascript:history.back()" aria-label="Volver"><svg class="i" viewBox="0 0 24 24"><path d="M19 12H5M11 6l-6 6 6 6"/></svg></a>Realizar pago</h1>
  <p class="sub">Elige un método de pago y completa la información para continuar con el pago de tu proyecto.</p>

  <div class="steps">
    <div class="st on" data-s="1"><i>1</i>Método de pago</div><div class="ln" id="l1"></div>
    <div class="st" data-s="2"><i>2</i>Confirmación</div><div class="ln" id="l2"></div>
    <div class="st" data-s="3"><i>3</i>Comprobante</div>
  </div>

  <!-- PASO 1 -->
  <div class="paso" id="p1">
    <h2>Método de pago</h2>
    <div class="met" id="met">
      <button type="button" class="on" data-m="tarjeta"><svg class="i" viewBox="0 0 24 24"><path d="M3 6h18a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1zM2 10h20"/></svg>Tarjeta de crédito o débito</button>
      <button type="button" data-m="nequi"><svg class="i" viewBox="0 0 24 24"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M11 18h2"/></svg>Nequi / Daviplata</button>
      <button type="button" data-m="transferencia"><svg class="i" viewBox="0 0 24 24"><path d="M3 10l9-6 9 6M5 10v8M9 10v8M15 10v8M19 10v8M3 21h18"/></svg>Transferencia bancaria</button>
      <button type="button" data-m="paypal"><svg class="i" viewBox="0 0 24 24"><path d="M7 21l2-14h6a4 4 0 0 1 0 8H10"/></svg>PayPal</button>
    </div>

    <div class="pane on" id="m-tarjeta">
      <h2>Información de la tarjeta</h2>
      <div class="box">
        <div class="f"><label for="num">Número de tarjeta</label><input id="num" inputmode="numeric" placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="off"></div>
        <div class="g">
          <div class="f"><label for="nom">Nombre en la tarjeta</label><input id="nom" placeholder="Como aparece en la tarjeta" autocomplete="off"></div>
          <div class="f"><label for="exp">Fecha de expiración</label><input id="exp" placeholder="MM / AA" maxlength="7" inputmode="numeric" autocomplete="off"></div>
          <div class="f"><label for="cvv">CVV</label><input id="cvv" type="password" placeholder="123" maxlength="4" inputmode="numeric" autocomplete="off"></div>
          <div class="f"><label>Tipo de tarjeta</label>
            <div class="tipos"><span data-t="visa">VISA</span><span data-t="mc">MC</span><span data-t="amex">AMEX</span></div></div>
        </div>
      </div>
    </div>
    <div class="pane" id="m-nequi"><div class="box"><div class="f"><label for="cel">Número de celular</label><input id="cel" inputmode="numeric" maxlength="10" placeholder="3001234567"></div><p class="msg">Recibirás una notificación en tu app para aprobar el pago.</p></div></div>
    <div class="pane" id="m-transferencia"><div class="box"><p class="msg">Al continuar verás los datos de la cuenta para transferir<?= $p ? ' <b>' . $cop($total) . '</b>' : '' ?>. Usa como referencia <b><?= $referencia ?></b>.</p></div></div>
    <div class="pane" id="m-paypal"><div class="box"><p class="msg">Serás redirigido a PayPal para completar el pago de forma segura.</p></div></div>

    <button class="go" id="sig" type="button"><svg class="i" viewBox="0 0 24 24" style="width:18px"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>Continuar al siguiente paso</button>
    <p class="ssl">Tu información está protegida con tecnología de encriptación SSL.</p>
  </div>


  <div class="paso" id="p2" hidden>
    <h2>Confirma tu pago</h2>
    <div class="box">
<?php if ($p): ?>
      <div class="row"><span>Proyecto</span><b><?= htmlspecialchars($p['titulo']) ?></b></div>
<?php endif; ?>
      <div class="row"><span>Método</span><b id="c-met"></b></div>
      <div class="row"><span>Referencia</span><b><?= $referencia ?></b></div>
<?php if ($p): ?>
      <div class="row"><span>Total</span><b><?= $cop($total) ?></b></div>
<?php endif; ?>
    </div>
    <button class="go" id="pagar" type="button">Pagar<?= $p ? ' ' . $cop($total) : '' ?></button>
    <button class="go" id="atras" type="button" style="background:transparent;border:1px solid var(--line)">Volver</button>
  </div>

  <div class="paso" id="p3" hidden>
    <div class="comp">
      <div class="chk"><svg class="i" viewBox="0 0 24 24" style="width:32px;stroke-width:2.5"><path d="M5 12l5 5 9-10"/></svg></div>
      <h2>Pago registrado</h2>
      <p class="msg">Referencia <b><?= $referencia ?></b><br>El dinero queda retenido y se libera al programador cuando confirmes la entrega.</p>
      <button class="go" type="button" onclick="window.print()" style="max-width:260px;margin:18px auto 0">Descargar comprobante</button>
    </div>
  </div>
</section>

<section class="card">
  <h2 style="font-size:22px;margin-bottom:20px">Resumen del pago</h2>
<?php if ($p): ?>
  <div class="proj">
    <img src="img/logo.png" alt="">
    <div><h3><?= htmlspecialchars($p['titulo']) ?></h3>
      <small>Por: <em><?= htmlspecialchars($p['programador']) ?></em></small>
      <small>★ <?= number_format($p['rating'], 1) ?> (<?= $p['resenas'] ?> reseñas)</small></div>
  </div>
  <div class="row"><span>Precio del proyecto</span><span><?= $cop($precio) ?></span></div>
  <div class="row"><span>Comisión de la plataforma (<?= $COMISION * 100 ?>%)</span><span><?= $cop($comision) ?></span></div>
  <div class="tot"><span>Total a pagar</span><b><?= $cop($total) ?></b></div>
<?php else: ?>
  <p class="msg">Aquí aparecerá el detalle del proyecto y el total a pagar.</p>
<?php endif; ?>
  <div class="safe"><b>Pago seguro</b><p>Tus datos están protegidos y el pago se libera al programador solo cuando confirmas la entrega del proyecto.</p></div>
  <div class="ok"><img src="img/logo.png" alt="DevSide"></div>
</section>
</main>

<script>
const $ = s => document.querySelector(s), $$ = s => [...document.querySelectorAll(s)];
let metodo = 'tarjeta';

$$('#met button').forEach(b => b.onclick = () => {
  metodo = b.dataset.m;
  $$('#met button').forEach(x => x.classList.toggle('on', x === b));
  $$('.pane').forEach(x => x.classList.toggle('on', x.id === 'm-' + metodo));
});

const num = $('#num'), exp = $('#exp');
num.oninput = () => {
  const v = num.value.replace(/\D/g, '').slice(0, 16);
  num.value = v.replace(/(.{4})/g, '$1 ').trim();
  const t = /^4/.test(v) ? 'visa' : /^(5[1-5]|2[2-7])/.test(v) ? 'mc' : /^3[47]/.test(v) ? 'amex' : '';
  $$('.tipos span').forEach(s => s.classList.toggle('on', s.dataset.t === t));
};
exp.oninput = () => {
  let v = exp.value.replace(/\D/g, '').slice(0, 4);
  exp.value = v.length > 2 ? v.slice(0, 2) + ' / ' + v.slice(2) : v;
};

const luhn = n => { let s = 0, d = false;
  for (let i = n.length - 1; i >= 0; i--) { let x = +n[i]; if (d && (x *= 2) > 9) x -= 9; s += x; d = !d; }
  return n.length >= 13 && s % 10 === 0; };

function valida() {
  if (metodo === 'tarjeta') {
    const checks = [
      [num, luhn(num.value.replace(/\s/g, ''))],
      [$('#nom'), $('#nom').value.trim().length > 3],
      [exp, /^(0[1-9]|1[0-2]) \/ \d{2}$/.test(exp.value)],
      [$('#cvv'), /^\d{3,4}$/.test($('#cvv').value)],
    ];
    checks.forEach(([el, ok]) => el.classList.toggle('err', !ok));
    return checks.every(c => c[1]);
  }
  if (metodo === 'nequi') { const ok = /^3\d{9}$/.test($('#cel').value); $('#cel').classList.toggle('err', !ok); return ok; }
  return true;
}

function paso(n) {
  $$('.paso').forEach(x => x.hidden = x.id !== 'p' + n);
  $$('.st').forEach(s => s.classList.toggle('on', +s.dataset.s <= n));
  $('#l1').classList.toggle('on', n >= 2); $('#l2').classList.toggle('on', n >= 3);
}

$('#sig').onclick = () => {
  if (!valida()) return;
  $('#c-met').textContent = $('#met .on').textContent.trim();
  paso(2);
};
$('#atras').onclick = () => paso(1);
$('#pagar').onclick = () => {

  fetch('crear_pago.php', {method:'POST', body: JSON.stringify({proyecto: <?= $id ?>, metodo})})
  
  paso(3);
};
</script>
</body>
</html>