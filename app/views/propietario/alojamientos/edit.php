<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<style>
    .edit-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 24px;
        border-bottom: 1.5px solid var(--line);
        padding-bottom: 10px;
    }

    .et-btn {
        font-size: 14px;
        font-weight: 700;
        color: var(--ink-faint);
        padding: 8px 16px;
        cursor: pointer;
        border-radius: 8px;
        transition: .15s;
    }

    .et-btn:hover {
        background: var(--line-soft);
        color: var(--ink);
    }

    .et-btn.active {
        background: var(--red-wash);
        color: var(--red);
    }

    .et-pane {
        display: none;
    }

    .et-pane.active {
        display: block;
    }

    .list-item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border: 1px solid var(--line);
        border-radius: 10px;
        margin-bottom: 10px;
        background: #fff;
    }

    .list-item-title {
        font-weight: 700;
        font-size: 14px;
    }

    .list-item-desc {
        font-size: 12.5px;
        color: var(--ink-soft);
    }
</style>

<div class="page-content">
    <div class="page-head">
        <div>
            <div class="eyebrow">Edición</div>
            <h1>Editar alojamiento</h1>
            <div class="sub"><?php echo htmlspecialchars($alojamiento['titulo']); ?></div>
        </div>
        <a href="/alojamientos" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Volver al listado</a>
    </div>

    <!-- TABS -->
    <div class="edit-tabs">
        <div class="et-btn active" onclick="switchTab('datos', this)">Datos Generales</div>
        <div class="et-btn" onclick="switchTab('fotos', this)">Fotos</div>
        <div class="et-btn" onclick="switchTab('servicios', this)">Servicios</div>
        <div class="et-btn" onclick="switchTab('politicas', this)">Políticas de Convivencia</div>
        <div class="et-btn" onclick="switchTab('descuentos', this)">Descuentos</div>
        <div class="et-btn" onclick="switchTab('beneficios', this)">Beneficios</div>
        <div class="et-btn" onclick="switchTab('resenas', this)">Reseñas</div>
    </div>

    <!-- TAB: DATOS GENERALES -->
    <div id="pane-datos" class="et-pane active">
        <form action="/alojamientos/actualizar" method="POST">
            <input type="hidden" name="alojamiento_id" value="<?php echo $alojamiento['alojamiento_id']; ?>">

            <div class="card form-section">
                <div class="form-section-head">
                    <div class="fsh-icon" style="background:var(--red-wash);"><i class="fas fa-home"
                            style="color:var(--red);"></i></div>
                    <div>
                        <h3>Información básica</h3>
                        <p class="text-muted">Datos generales de tu cuarto.</p>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="field" style="grid-column: 1 / -1;">
                        <label>Título del anuncio <span class="req">*</span></label>
                        <input type="text" name="titulo" value="<?php echo htmlspecialchars($alojamiento['titulo']); ?>"
                            required>
                    </div>
                    <div class="field">
                        <label>Tipo de alojamiento</label>
                        <select name="tipo_codigo">
                            <?php foreach ($tipos_alojamiento as $tipo): ?>
                                <option value="<?php echo htmlspecialchars($tipo['codigo']); ?>" <?php echo $alojamiento['tipo_codigo'] == $tipo['codigo'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tipo['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Género exclusivo</label>
                        <select name="genero_exclusivo_codigo">
                            <?php foreach ($generos as $gen): ?>
                                <option value="<?php echo htmlspecialchars($gen['codigo']); ?>" <?php echo $alojamiento['genero_exclusivo_codigo'] == $gen['codigo'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($gen['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field" style="grid-column: 1 / -1;">
                        <label>Descripción</label>
                        <textarea name="descripcion" rows="4"
                            style="width:100%;padding:12px 14px;border-radius:11px;border:1.5px solid var(--line);background:#FBFAF8;font-size:14px;font-family:var(--font-body);color:var(--ink);resize:vertical;"><?php echo htmlspecialchars($alojamiento['descripcion'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card form-section">
                <div class="form-section-head">
                    <div class="fsh-icon" style="background:var(--blue-wash);"><i class="fas fa-ruler-combined"
                            style="color:var(--blue);"></i></div>
                    <div>
                        <h3>Características</h3>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="field"><label>Habitaciones</label><input type="number" name="numero_habitaciones"
                            value="<?php echo $alojamiento['numero_habitaciones']; ?>"></div>
                    <div class="field"><label>Baños</label><input type="number" name="numero_banos"
                            value="<?php echo $alojamiento['numero_banos']; ?>"></div>
                    <div class="field"><label>Tamaño (m²)</label><input type="number" name="tamano_m2"
                            value="<?php echo $alojamiento['tamano_m2']; ?>"></div>
                    <div class="field"><label>Mínimo de meses</label><input type="number" name="duracion_minima_meses"
                            value="<?php echo $alojamiento['duracion_minima_meses']; ?>"></div>
                </div>
                <div class="checkbox-row">
                    <label class="check-item"><input type="checkbox" name="bano_privado" value="1" <?php echo $alojamiento['bano_privado'] ? 'checked' : ''; ?>> <span>Baño privado</span></label>
                    <label class="check-item"><input type="checkbox" name="amoblado" value="1" <?php echo $alojamiento['amoblado'] ? 'checked' : ''; ?>> <span>Amoblado</span></label>
                    <label class="check-item"><input type="checkbox" name="mascotas_permitidas" value="1" <?php echo $alojamiento['mascotas_permitidas'] ? 'checked' : ''; ?>> <span>Mascotas
                            permitidas</span></label>
                    <label class="check-item"><input type="checkbox" name="fumadores_permitidos" value="1" <?php echo $alojamiento['fumadores_permitidos'] ? 'checked' : ''; ?>> <span>Fumadores
                            permitidos</span></label>
                </div>
            </div>

            <div class="card form-section">
                <div class="form-section-head">
                    <div class="fsh-icon" style="background:var(--gold-wash);"><i class="fas fa-tag"
                            style="color:var(--gold);"></i></div>
                    <div>
                        <h3>Precio y disponibilidad</h3>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="field"><label>Precio mensual</label><input type="number" name="precio_mensual"
                            value="<?php echo $alojamiento['precio_mensual']; ?>" required></div>
                    <div class="field"><label>Garantía</label><input type="number" name="garantia"
                            value="<?php echo $alojamiento['garantia']; ?>"></div>
                    <div class="field"><label>Moneda</label>
                        <select name="moneda_codigo">
                            <?php foreach ($monedas as $mon): ?>
                                <option value="<?php echo htmlspecialchars($mon['codigo']); ?>" <?php echo $alojamiento['moneda_codigo'] == $mon['codigo'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($mon['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card form-section">
                <div class="form-grid">
                    <div class="field" style="grid-column: 1 / -1;">
                        <label>Dirección</label>
                        <input type="text" name="direccion"
                            value="<?php echo htmlspecialchars($alojamiento['direccion'] ?? ''); ?>">
                    </div>
                    <div class="field" style="grid-column: 1 / -1;">
                        <label>Ubicación exacta (Mapa)</label>
                        <div
                            style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                            <p class="text-muted" style="font-size: 12px; margin: 0;">Mueve el marcador para ajustar la
                                ubicación exacta.</p>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="ubicarMiPosicion()"
                                style="font-size: 12px; padding: 4px 8px;"><i class="fas fa-crosshairs"></i> Usar mi
                                ubicación actual</button>
                        </div>
                        <div id="map" style="height: 300px; border-radius: 11px; z-index: 1;"></div>
                        <input type="hidden" name="latitud" id="latitud"
                            value="<?php echo htmlspecialchars($alojamiento['latitud'] ?? '-12.046374'); ?>">
                        <input type="hidden" name="longitud" id="longitud"
                            value="<?php echo htmlspecialchars($alojamiento['longitud'] ?? '-77.042793'); ?>">
                    </div>
                </div>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Guardar cambios generales</button>
            </div>
        </form>
    </div>

    <!-- TAB: FOTOS -->
    <div id="pane-fotos" class="et-pane">
        <div class="card form-section">
            <h3 style="margin-bottom:20px;">Fotos publicadas</h3>
            <?php if (!empty($fotos)): ?>
                <p class="text-muted" style="font-size:13px; margin-bottom:16px;">La foto principal es la que se muestra en
                    el listado de tus alojamientos. Puedes elegir cuál con el botón <strong>"Marcar principal"</strong>.</p>
                <div style="display:flex; flex-wrap:wrap; gap:16px;">
                    <?php foreach ($fotos as $foto): ?>
                        <div
                            style="position:relative; width:160px; border-radius:8px; overflow:hidden; border:1px solid var(--line); background:#fff;">
                            <div style="position:relative; width:100%; height:120px;">
                                <img src="<?php echo htmlspecialchars($foto['url']); ?>"
                                    style="width:100%; height:100%; object-fit:cover; display:block;">
                                <?php if ($foto['orden'] == 1): ?>
                                    <span
                                        style="position:absolute; top:5px; left:5px; background:var(--blue); color:#fff; font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px;"><i
                                            class="fas fa-star"></i> Principal</span>
                                <?php endif; ?>
                                <form method="POST" action="/alojamientos/fotos/eliminar"
                                    style="position:absolute; top:4px; right:4px;">
                                    <input type="hidden" name="alojamiento_id"
                                        value="<?php echo $alojamiento['alojamiento_id']; ?>">
                                    <input type="hidden" name="multimedia_id" value="<?php echo $foto['multimedia_id']; ?>">
                                    <button type="submit"
                                        style="background:var(--red); color:#fff; border:none; border-radius:50%; width:22px; height:22px; cursor:pointer;"
                                        title="Eliminar foto"><i class="fas fa-times"></i></button>
                                </form>
                            </div>
                            <?php if ($foto['orden'] == 1): ?>
                                <div
                                    style="padding:6px; text-align:center; background:var(--blue-wash); color:var(--blue); font-size:11.5px; font-weight:600;">
                                    <i class="fas fa-star"></i> Foto principal
                                </div>
                            <?php else: ?>
                                <form method="POST" action="/alojamientos/fotos/principal" style="padding:6px;">
                                    <input type="hidden" name="alojamiento_id"
                                        value="<?php echo $alojamiento['alojamiento_id']; ?>">
                                    <input type="hidden" name="multimedia_id" value="<?php echo $foto['multimedia_id']; ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm"
                                        style="width:100%; border:1px solid var(--line); color:var(--ink); font-weight:600; font-size:11.5px;"
                                        title="Marcar como principal">
                                        <i class="fas fa-star"></i> Marcar principal
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">No hay fotos publicadas para este cuarto.</p>
            <?php endif; ?>

            <!-- Para subir nuevas fotos (simplificado para que lo actualices en una futura versión) -->
            <form method="POST" action="/alojamientos/fotos/agregar" enctype="multipart/form-data"
                style="margin-top:24px; padding-top:24px; border-top:1px dashed var(--line);">
                <input type="hidden" name="alojamiento_id" value="<?php echo $alojamiento['alojamiento_id']; ?>">
                <div style="display:flex; gap:10px; align-items:flex-end;">
                    <div class="field" style="margin-bottom:0; flex:1;">
                        <label>Subir nuevas fotos (Max 5)</label>
                        <input type="file" name="nuevas_fotos[]" multiple accept="image/*"
                            style="border:1px solid var(--line); border-radius:8px; padding:10px; width:100%;">
                    </div>
                    <button class="btn btn-dark" style="padding:13px 20px;">Subir fotos</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB: SERVICIOS -->
    <div id="pane-servicios" class="et-pane">
        <div class="card form-section">
            <h3 style="margin-bottom:20px;">Servicios incluidos o extras</h3>

            <!-- Listado actuales -->
            <?php foreach ($servicios_asignados as $srv): ?>
                <div class="list-item-row">
                    <div>
                        <div class="list-item-title"><i class="fas fa-check-circle text-success me-2"></i>
                            <?php echo htmlspecialchars($srv['nombre']); ?></div>
                        <div class="list-item-desc">
                            <?php echo $srv['precio'] > 0 ? '+ S/ ' . number_format($srv['precio'], 2) . ' / mes' : 'Incluido gratuitamente'; ?>
                        </div>
                    </div>
                    <form method="POST" action="/alojamientos/servicios">
                        <input type="hidden" name="accion" value="remover">
                        <input type="hidden" name="alojamiento_id" value="<?php echo $alojamiento['alojamiento_id']; ?>">
                        <input type="hidden" name="servicio_id" value="<?php echo $srv['servicio_id']; ?>">
                        <button class="btn btn-ghost btn-sm" style="color:var(--red);border:none;">Remover</button>
                    </form>
                </div>
            <?php endforeach; ?>

            <!-- Añadir nuevo -->
            <form method="POST" action="/alojamientos/servicios"
                style="margin-top:24px; padding-top:24px; border-top:1px dashed var(--line);">
                <input type="hidden" name="accion" value="agregar">
                <input type="hidden" name="alojamiento_id" value="<?php echo $alojamiento['alojamiento_id']; ?>">
                <div style="display:flex; gap:10px; align-items:flex-end;">
                    <div class="field" style="margin-bottom:0; flex:2;">
                        <label>Asignar Servicio</label>
                        <select name="servicio_id" required>
                            <option value="" disabled selected>Seleccione un servicio...</option>
                            <?php foreach ($servicios_disponibles as $sd): ?>
                                <option value="<?php echo $sd['servicio_id']; ?>">
                                    <?php echo htmlspecialchars($sd['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field" style="margin-bottom:0; flex:1;">
                        <label>Precio extra (S/)</label>
                        <input type="number" name="precio" value="0" min="0" step="5">
                    </div>
                    <button class="btn btn-dark" style="padding:13px 20px;">Añadir</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB: POLITICAS -->
    <div id="pane-politicas" class="et-pane">
        <div class="card form-section">
            <h3 style="margin-bottom:20px;">Reglas y Políticas de Convivencia</h3>

            <?php foreach ($politicas_asignadas as $pol): ?>
                <div class="list-item-row">
                    <div>
                        <div class="list-item-title"><i class="fas fa-exclamation-circle text-warning me-2"></i>
                            <?php echo htmlspecialchars($pol['nombre']); ?></div>
                    </div>
                    <form method="POST" action="/alojamientos/politicas">
                        <input type="hidden" name="accion" value="remover">
                        <input type="hidden" name="alojamiento_id" value="<?php echo $alojamiento['alojamiento_id']; ?>">
                        <input type="hidden" name="politica_casa_id" value="<?php echo $pol['politica_casa_id']; ?>">
                        <button class="btn btn-ghost btn-sm" style="color:var(--red);border:none;">Remover</button>
                    </form>
                </div>
            <?php endforeach; ?>

            <form method="POST" action="/alojamientos/politicas"
                style="margin-top:24px; padding-top:24px; border-top:1px dashed var(--line);">
                <input type="hidden" name="accion" value="agregar">
                <input type="hidden" name="alojamiento_id" value="<?php echo $alojamiento['alojamiento_id']; ?>">
                <div style="display:flex; gap:10px; align-items:flex-end;">
                    <div class="field" style="margin-bottom:0; flex:1;">
                        <label>Asignar Regla Global</label>
                        <select name="politica_casa_id" required>
                            <option value="" disabled selected>Seleccione...</option>
                            <?php foreach ($politicas_disponibles as $pd): ?>
                                <option value="<?php echo $pd['politica_casa_id']; ?>">
                                    <?php echo htmlspecialchars($pd['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-dark" style="padding:13px 20px;">Añadir</button>
                </div>
            </form>

            <form method="POST" action="/alojamientos/politicas"
                style="margin-top:24px; padding-top:24px; border-top:1px dashed var(--line);">
                <input type="hidden" name="accion" value="crear">
                <input type="hidden" name="alojamiento_id" value="<?php echo $alojamiento['alojamiento_id']; ?>">
                <div style="display:flex; gap:10px; align-items:flex-end;">
                    <div class="field" style="margin-bottom:0; flex:1;">
                        <label>¿No encuentras la regla? Escribe una nueva</label>
                        <input type="text" name="nueva_politica"
                            placeholder="Ej: Prohibido ingresar bicicletas a la habitación" required>
                    </div>
                    <button class="btn btn-primary" style="padding:13px 20px;">Crear y asignar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB: DESCUENTOS -->
    <div id="pane-descuentos" class="et-pane">
        <div class="card form-section">
            <h3 style="margin-bottom:20px;">Descuentos Aplicables</h3>

            <?php foreach ($descuentos as $desc): ?>
                <div class="list-item-row" style="align-items:flex-start;">
                    <div>
                        <div class="list-item-title"><i class="fas fa-tag text-success me-2"></i>
                            <?php echo htmlspecialchars($desc['nombre']); ?> <span
                                style="color:var(--green); font-family:var(--font-mono); font-weight:700;">(-S/
                                <?php echo number_format($desc['monto'], 2); ?>)</span></div>
                        <div class="list-item-desc" style="margin-top:4px;">
                            <?php if (!empty($desc['motivo'])): ?>
                                <div><strong>Motivo:</strong> <?php echo htmlspecialchars($desc['motivo']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($desc['inicio']) || !empty($desc['fin'])): ?>
                                <div style="margin-top:2px;">
                                    <strong>Vigencia:</strong>
                                    <?php echo !empty($desc['inicio']) ? date('d/m/Y', strtotime($desc['inicio'])) : ''; ?>
                                    -
                                    <?php echo !empty($desc['fin']) ? date('d/m/Y', strtotime($desc['fin'])) : ''; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <form method="POST" action="/alojamientos/descuentos/eliminar" style="margin-top: 4px;">
                        <input type="hidden" name="alojamiento_id" value="<?php echo $alojamiento['alojamiento_id']; ?>">
                        <input type="hidden" name="descuento_id" value="<?php echo $desc['descuento_id']; ?>">
                        <button class="btn btn-ghost btn-sm" style="color:var(--red);border:none;">Remover</button>
                    </form>
                </div>
            <?php endforeach; ?>

            <form method="POST" action="/alojamientos/descuentos/agregar"
                style="margin-top:24px; padding-top:24px; border-top:1px dashed var(--line);">
                <input type="hidden" name="alojamiento_id" value="<?php echo $alojamiento['alojamiento_id']; ?>">
                <h6 style="font-weight:700; margin-bottom:16px;">Agregar nuevo descuento</h6>
                <div class="form-grid">
                    <div class="field" style="grid-column:1 / -1;">
                        <label>Nombre del descuento (Ej: Descuento de Verano) <span class="req">*</span></label>
                        <input type="text" name="nombre" required>
                    </div>
                    <div class="field">
                        <label>Monto a descontar (S/) <span class="req">*</span></label>
                        <input type="number" name="monto" min="1" step="0.5" required>
                    </div>
                    <div class="field">
                        <label>Motivo / Condición</label>
                        <input type="text" name="motivo" placeholder="Ej: Para estudiantes con carnet">
                    </div>
                    <div class="field">
                        <label>Fecha inicio vigencia</label>
                        <input type="date" name="inicio">
                    </div>
                    <div class="field">
                        <label>Fecha fin vigencia</label>
                        <input type="date" name="fin">
                    </div>
                </div>
                <div style="text-align:right;">
                    <button class="btn btn-dark" style="padding:10px 20px;">Guardar Descuento</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB: BENEFICIOS -->
    <div id="pane-beneficios" class="et-pane">
        <div class="card form-section">
            <h3 style="margin-bottom:20px;">Beneficios Extras</h3>
            <p class="text-muted mb-4" style="font-size:14px;">Los beneficios son ventajas intangibles o extras que no
                tienen precio (ej: Limpieza quincenal, Zona compartida de parrilla, etc).</p>

            <?php foreach ($beneficios as $ben): ?>
                <div class="list-item-row" style="align-items:flex-start;">
                    <div>
                        <div class="list-item-title"><i class="fas fa-gift text-purple me-2" style="color:#7C3AED;"></i>
                            <?php echo htmlspecialchars($ben['nombre']); ?></div>
                        <?php if (!empty($ben['descripcion'])): ?>
                            <div class="list-item-desc" style="margin-top:4px;">
                                <?php echo htmlspecialchars($ben['descripcion']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <form method="POST" action="/alojamientos/beneficios/eliminar" style="margin-top: 4px;">
                        <input type="hidden" name="alojamiento_id" value="<?php echo $alojamiento['alojamiento_id']; ?>">
                        <input type="hidden" name="beneficio_id" value="<?php echo $ben['beneficio_id']; ?>">
                        <button class="btn btn-ghost btn-sm" style="color:var(--red);border:none;">Remover</button>
                    </form>
                </div>
            <?php endforeach; ?>

            <form method="POST" action="/alojamientos/beneficios/agregar"
                style="margin-top:24px; padding-top:24px; border-top:1px dashed var(--line);">
                <input type="hidden" name="alojamiento_id" value="<?php echo $alojamiento['alojamiento_id']; ?>">
                <h6 style="font-weight:700; margin-bottom:16px;">Agregar nuevo beneficio</h6>
                <div class="form-grid">
                    <div class="field" style="grid-column:1 / -1;">
                        <label>Nombre del beneficio <span class="req">*</span></label>
                        <input type="text" name="nombre" placeholder="Ej: Uso de lavandería sin costo" required>
                    </div>
                    <div class="field" style="grid-column:1 / -1;">
                        <label>Descripción detallada</label>
                        <textarea name="descripcion" rows="2"
                            style="width:100%;padding:12px 14px;border-radius:11px;border:1.5px solid var(--line);background:#FBFAF8;font-size:14px;font-family:var(--font-body);color:var(--ink);resize:vertical;"></textarea>
                    </div>
                </div>
                <div style="text-align:right;">
                    <button class="btn btn-dark" style="padding:10px 20px;">Guardar Beneficio</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB: RESEÑAS -->
    <div id="pane-resenas" class="et-pane">
        <div class="card form-section">
            <h3 style="margin-bottom:6px;">Reseñas del alojamiento</h3>
            <p class="text-muted" style="font-size:13.5px; margin-bottom:20px;">Opiniones que los inquilinos han dejado
                sobre este cuarto. Puedes responder a cada una.</p>

            <?php if (!empty($universidades_cercanas)): ?>
                <div class="list-item-row" style="background:var(--blue-wash); border-color:var(--blue);">
                    <div>
                        <div class="list-item-title"><i class="fas fa-university me-2" style="color:var(--blue);"></i>
                            Universidades cercanas (alojamiento_universidad)</div>
                        <div class="list-item-desc">
                            <?php foreach ($universidades_cercanas as $uc): ?>
                                <span class="badge bg-white text-dark me-1 mb-1"
                                    style="border:1px solid var(--line); font-weight:600;">
                                    <?php echo htmlspecialchars($uc['universidad_nombre']); ?> ·
                                    <?php echo number_format($uc['distancia_km'], 2); ?> km
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($resenas)): ?>
                <?php foreach ($resenas as $res): ?>
                    <?php
                    $nombre_estudiante = htmlspecialchars(trim(($res['nombres'] ?? '') . ' ' . ($res['apellido_paterno'] ?? '')));
                    ?>
                    <div class="list-item-row" style="align-items:flex-start; flex-direction:column; gap:10px;">
                        <div style="width:100%; display:flex; justify-content:space-between; align-items:flex-start;">
                            <div>
                                <div class="list-item-title">
                                    <?php echo $nombre_estudiante; ?>
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <i class="fas fa-star"
                                            style="font-size:12px; color:<?php echo $s <= (int) $res['calificacion'] ? '#F59E0B' : '#D1D5DB'; ?>;"></i>
                                    <?php endfor; ?>
                                </div>
                                <div class="list-item-desc"><?php echo htmlspecialchars($res['comentario'] ?? ''); ?></div>
                                <div class="list-item-desc" style="margin-top:2px;">
                                    <i class="fas fa-clock"></i>
                                    <?php echo !empty($res['creado']) ? date('d/m/Y', strtotime($res['creado'])) : ''; ?>
                                    <?php if (!empty($res['estado_nombre'])): ?>
                                        · <span
                                            class="badge badge-faint"><?php echo htmlspecialchars($res['estado_nombre']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($res['respuesta_propietario'])): ?>
                            <div
                                style="width:100%; background:var(--green-wash); border-radius:8px; padding:10px 14px; border-left:3px solid var(--green);">
                                <div style="font-size:12px; font-weight:700; color:var(--green); margin-bottom:2px;"><i
                                        class="fas fa-reply me-1"></i> Tu respuesta</div>
                                <div style="font-size:13.5px; color:var(--ink);">
                                    <?php echo htmlspecialchars($res['respuesta_propietario']); ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="/alojamientos/resenas/responder" style="width:100%;">
                                <input type="hidden" name="alojamiento_id" value="<?php echo $alojamiento['alojamiento_id']; ?>">
                                <input type="hidden" name="resenia_alojamiento_id"
                                    value="<?php echo $res['resenia_alojamiento_id']; ?>">
                                <div class="field" style="margin-bottom:8px;">
                                    <label>Responder a esta reseña</label>
                                    <textarea name="respuesta" rows="2" required
                                        style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--line);font-size:13.5px;resize:vertical;"
                                        placeholder="Escribe tu respuesta..."></textarea>
                                </div>
                                <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-paper-plane me-1"></i>
                                    Publicar respuesta</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">Aún no hay reseñas para este alojamiento.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    function switchTab(tabId, element) {
        document.querySelectorAll('.et-pane').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.et-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('pane-' + tabId).classList.add('active');
        element.classList.add('active');
        if (tabId === 'datos') { setTimeout(() => map.invalidateSize(), 100); }
    }

    // MAPA LEAFLET
    let initialLat = document.getElementById('latitud').value || -12.046374;
    let initialLng = document.getElementById('longitud').value || -77.042793;
    let map = L.map('map').setView([initialLat, initialLng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    let marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

    marker.on('dragend', function (event) {
        let position = marker.getLatLng();
        document.getElementById('latitud').value = position.lat;
        document.getElementById('longitud').value = position.lng;
    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        document.getElementById('latitud').value = e.latlng.lat;
        document.getElementById('longitud').value = e.latlng.lng;
    });

    // Geolocalización
    function ubicarMiPosicion() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                let lat = position.coords.latitude;
                let lng = position.coords.longitude;
                let latlng = new L.LatLng(lat, lng);
                marker.setLatLng(latlng);
                map.setView(latlng, 15);
                document.getElementById('latitud').value = lat;
                document.getElementById('longitud').value = lng;
            }, function (error) {
                alert("No pudimos obtener tu ubicación actual. Asegúrate de tener el GPS activado y dar permisos al navegador.");
            });
        } else {
            alert("Geolocalización no es soportada por tu navegador.");
        }
    }
</script>