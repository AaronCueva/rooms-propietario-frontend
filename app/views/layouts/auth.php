<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APP-ROOMS | Propietarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <div class="auth-view active">
        <?php echo $content; ?>
        
        <div class="auth-visual">
            <div class="auth-visual-inner">
                <?php if (strpos($_SERVER['REQUEST_URI'], 'register') !== false): ?>
                <h2>Tu primera publicación puede estar activa hoy mismo</h2>
                <p>Miles de universitarios buscan cuarto cada semestre. Con APP-ROOMS tu propiedad llega a los inquilinos correctos.</p>
                <?php else: ?>
                <h2>Publica, gestiona y cobra — todo en APP-ROOMS</h2>
                <p>Miles de estudiantes buscan alojamiento cerca de su universidad. Ponemos tu propiedad frente a ellos.</p>
                <div class="auth-visual-stats">
                    <div class="av-stat"><div class="n">1,200+</div><div class="l">Alojamientos publicados</div></div>
                    <div class="av-stat"><div class="n">98%</div><div class="l">Tasa de ocupación</div></div>
                    <div class="av-stat"><div class="n">4.8</div><div class="l">Calificación media</div></div>
                    <div class="av-stat"><div class="n">3 días</div><div class="l">Tiempo medio de reserva</div></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php $flash = \App\Core\Controller::getFlash(); ?>
            <?php if ($flash): ?>
                Swal.fire({
                    icon: '<?php echo $flash['tipo']; ?>',
                    title: '<?php echo addslashes($flash['mensaje']); ?>',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>
