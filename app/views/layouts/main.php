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
    <?php
    $current_uri = $_SERVER['REQUEST_URI'];
    $rol_id = $_SESSION['rol_id'] ?? null;
    
    // Obtener menú dinámico
    $menuModel = new \App\Models\MenuMaestro();
    $menu_items = $menuModel->obtenerMenuPorRol($rol_id);
    
    $foto_usuario = $_SESSION['url_foto'] ?? null;
    $nombre_completo = $_SESSION['nombres'] . ' ' . $_SESSION['apellidos'];
    $avatar_letras = strtoupper(substr($_SESSION['nombres'], 0, 1) . substr($_SESSION['apellidos'], 0, 1));

    // Contar solicitudes pendientes para badge del sidebar
    $reservaModel = new \App\Models\Reserva();
    $pendientes_count = $reservaModel->contarPendientes($rol_id ? $_SESSION['usuario_id'] : 0);
    ?>

    <div class="app-shell active" id="app-shell">
        <!-- SIDEBAR (ESTILO PROPIETARIO) -->
        <aside class="sidebar">
            <div class="sb-logo">
                <div class="logo-box"><i class="fas fa-building text-white"></i></div>
                <div class="wm">APP-<span>ROOMS</span></div>
            </div>
            <div class="sb-role"><div class="dot"></div><div class="rl">Portal Propietario</div></div>

            <div class="mt-2">
            <?php foreach ($menu_items as $seccion): ?>
                <?php if (empty($seccion['url'])): // Es una etiqueta de sección ?>
                    <div class="sb-section"><?php echo htmlspecialchars($seccion['nombre']); ?></div>
                    <?php foreach ($seccion['hijos'] as $hijo): ?>
                        <a class="sb-link <?php echo (strpos($current_uri, $hijo['url']) !== false) ? 'is-active' : ''; ?>" 
                           href="<?php echo htmlspecialchars($hijo['url']); ?>">
                            <?php if (!empty($hijo['icono'])): ?>
                                <i class="<?php echo htmlspecialchars($hijo['icono']); ?> fa-fw"></i>
                            <?php else: ?>
                                <i class="fas fa-circle fa-fw"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($hijo['nombre']); ?>
                            <?php if ($hijo['url'] === '/solicitudes' && $pendientes_count > 0): ?>
                                <span class="sb-badge"><?php echo $pendientes_count > 99 ? '99+' : $pendientes_count; ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php else: // Es un enlace principal sin sección superior ?>
                    <a class="sb-link <?php echo (strpos($current_uri, $seccion['url']) !== false) ? 'is-active' : ''; ?>" 
                       href="<?php echo htmlspecialchars($seccion['url']); ?>">
                        <?php if (!empty($seccion['icono'])): ?>
                            <i class="<?php echo htmlspecialchars($seccion['icono']); ?> fa-fw"></i>
                        <?php else: ?>
                            <i class="fas fa-circle fa-fw"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($seccion['nombre']); ?>
                        <?php if ($seccion['url'] === '/solicitudes' && $pendientes_count > 0): ?>
                            <span class="sb-badge"><?php echo $pendientes_count > 99 ? '99+' : $pendientes_count; ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
            </div>

            <div class="sb-bottom">
                <div class="sb-user" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                    <div class="sb-avatar"><?php echo $avatar_letras; ?></div>
                    <div class="sb-user-meta">
                        <div class="u-name"><?php echo htmlspecialchars($_SESSION['nombres']); ?></div>
                        <div class="u-role">Propietario verificado</div>
                    </div>
                </div>
                <ul class="dropdown-menu dropdown-menu-start shadow animated--grow-in">
                    <li><a class="dropdown-item" href="/perfil"><i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i> Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i> Cerrar sesión</a></li>
                </ul>
            </div>
        </aside>

        <!-- MAIN AREA -->
        <div class="main-area">
            <div class="topbar">
                <div class="tb-left">
                    <button class="btn btn-link d-md-none rounded-circle me-3 text-dark" onclick="document.querySelector('.sidebar').classList.toggle('d-none')">
                        <i class="fa fa-bars"></i>
                    </button>
                    <h2 id="topbar-title"><?php echo htmlspecialchars($titulo ?? 'Centro de operaciones'); ?></h2>
                </div>
                <div class="topbar-actions">
                    <div class="icon-btn"><i class="fas fa-question-circle"></i></div>
                    <div class="icon-btn"><i class="fas fa-bell"></i><span class="dot"></span></div>
                    <a href="/alojamientos/nuevo" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Publicar cuarto</a>
                </div>
            </div>

            <!-- CONTENIDO PRINCIPAL -->
            <?php echo $content; ?>
            
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
