<?php
require 'include/funciones.php';
require_once('db.php');
$dev_id = isset($_GET['dev']) ? intval($_GET['dev']) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevSite - Contactar a un Programador</title>
    <link rel="icon" type="image/x-icon" href="imagenes/logo-devsite2.png">
    <link rel="stylesheet" href="recursos/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

   
    <style>
        .contact-logo-box {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            margin: .5rem 0 1.5rem;
        }
        .contact-logo-box::before {
            content: "";
            position: absolute;
            width: 75%;
            aspect-ratio: 1;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(2, 169, 255, .35), transparent 70%);
            filter: blur(32px);
        }
        .contact-logo {
            position: relative;
            width: min(280px, 70%);
            height: auto;
            border-radius: 50%;
            box-shadow: 0 0 0 1px rgba(255, 255, 255, .08), 0 0 60px rgba(2, 169, 255, .3);
        }
    </style>
</head>
<body>
<?php incluirTemplates('sidebar'); ?>
    <div class="layout-wrapper">
        <main class="contact-main-content">
            <div class="contact-header-title">
                <h1><i class="fa-solid fa-user-plus icon-title"></i> Contactar a un <span>Programador</span></h1>
                <p>Completa los datos para enviar la propuesta directamente al desarrollador.</p>
            </div>

            <div class="contact-content-grid">
                <form class="contact-form-box" action="enviar_propuesta.php" method="POST">
                    <?php if($dev_id): ?>
                        <input type="hidden" name="dev_id" value="<?php echo $dev_id; ?>">
                    <?php endif; ?>

                    <div class="field-group">
                        <label for="empresa"><i class="fa-regular fa-address-card"></i> Nombre de tu Empresa / Negocio:</label>
                        <input type="text" id="empresa" name="empresa" placeholder="Ej. Mi Tienda Online, Café Aurora, etc." required>
                    </div>

                    <div class="field-group">
                        <label for="email"><i class="fa-regular fa-envelope"></i> Tu Correo Electrónico:</label>
                        <input type="email" id="email" name="email" placeholder="ejemplo@correo.com" required>
                    </div>

                    <div class="field-group">
                        <label for="mensaje"><i class="fa-regular fa-pen-to-square"></i> Detalles del Proyecto o Mensaje:</label>
                        <textarea id="mensaje" name="mensaje" rows="6" placeholder="Cuéntanos qué necesitas, qué tipo de proyecto tienes o cualquier detalle adicional..." required></textarea>
                    </div>

                    <button type="submit" class="btn-submit-proposal">
                        <i class="fa-regular fa-paper-plane"></i> Enviar Propuesta
                    </button>
                </form>
                <div class="contact-info-panel">
                    
                    <div class="contact-logo-box">
                        <img class="contact-logo" src="imagenes/logo-devsite2.png" alt="Logo de DevSite" width="280" height="280">
                    </div>

                    <div class="features-list">
                        <div class="feature-card">
                            <i class="fa-solid fa-bolt feature-icon-blue"></i>
                            <div>
                                <h4>Conecta con talento</h4>
                                <p>Encuentra programadores que se adapten a tu proyecto.</p>
                            </div>
                        </div>

                        <div class="feature-card">
                            <i class="fa-solid fa-shield-halved feature-icon-blue"></i>
                            <div>
                                <h4>Comunicación directa</h4>
                                <p>Habla directamente con el desarrollador sin intermediarios.</p>
                            </div>
                        </div>

                        <div class="feature-card">
                            <i class="fa-solid fa-user-gear feature-icon-blue"></i>
                            <div>
                                <h4>Tu idea, en buenas manos</h4>
                                <p>Lleva tu proyecto al siguiente nivel con ayuda profesional.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
