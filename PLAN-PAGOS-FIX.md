# PLAN — Fix pagos (rooms-propietario-frontend)

> Problemas: (1) contratos finalizados siguen mostrando pagos pendientes al propietario; (2) `anularPagosPendientes` no anula nada porque usa el código equivocado; (3) el propietario nunca ve pagos como "Pagados" (usa `ESPA002` que no existe) y no puede confirmar un pago → "no es posible hacer los pagos".

Fecha: 2026-07-12

## Bugs encontrados

1. **`anularPagosPendientes` usa código inexistente** — filtra `estado_codigo='ESPA001'`, pero las cuotas del inquilino se crean con `ESPG001` (PENDIENTE real). Al finalizar un contrato, las cuotas pendientes nunca se anulan. (`app/models/Pago.php:87`)
2. **`generarCuotas` inserta con `ESPA001`** — debería ser `ESPG001` (catálogo real ESTADO_PAGO). (`app/models/Pago.php:34`)
3. **Propietario ve pendientes de contratos finalizados** — `obtenerIngresosPropietario()` no filtra por estado del contrato. (`app/models/Pago.php:69-72`)
4. **"Pagado" detectado con código inexistente** — `IngresoController` chequea `estado_codigo === 'ESPA002'` (no existe); el real es `ESPG003` (COMPLETADO). Por eso `total_recibido` siempre es 0 y el propietario nunca ve pagos completados aunque el inquilino haya pagado. (`app/controllers/IngresoController.php:44,142`)
5. **No hay flujo de confirmación de pago** — el propietario no puede marcar una cuota como recibida (para transferencia/Yape donde él verifica la operación). Falta endpoint + botón.

## Fixes

### F1 — `app/models/Pago.php`
- Agregar constantes `EST_PENDIENTE='ESPG001'`, `EST_COMPLETADO='ESPG003'`.
- `generarCuotas`: insertar `ESPG001` (no `ESPA001`).
- `anularPagosPendientes`: anular pendientes reales: `estado_codigo IN ('ESPG001','ESPA001')` (cubre datos viejos y nuevos).
- `obtenerIngresosPropietario`: agregar `AND c.estado_codigo = 'ESCO001'` (sólo contratos activos proyectan ingresos).
- Nuevo `confirmarPago($pago_id, $propietario_id)`: verifica ownership (pago→contrato→reserva→alojamiento.usuario_id) y marca `estado_codigo='ESPG003', fecha_pago=now()`. Devuelve bool.

### F2 — `app/controllers/IngresoController.php`
- `index()` y `exportar()`: usar `ESPG003` para "pagado".
- Nuevo `confirmar()` (POST, AJAX): recibe `pago_id`, llama `confirmarPago`, devuelve JSON.

### F3 — `app/views/propietario/ingresos/index.php`
- Para cuotas pendientes/retrasadas, agregar botón "Confirmar pago" → POST `/ingresos/confirmar` (AJAX + Swal).

### F4 — `index.php` (rutas)
- Agregar `$router->post('/ingresos/confirmar', 'IngresoController', 'confirmar');`

### F5 — Limpieza BD (one-time)
Anular cuotas pendientes de contratos finalizados/cancelados ya existentes:
```sql
UPDATE pago SET habilitado = false
WHERE estado_codigo IN ('ESPG001','ESPA001')
  AND contrato_id IN (SELECT contrato_id FROM contrato WHERE estado_codigo IN ('ESCO002','ESCO003'));
```
(Seguro: con el fix F1 del inquilino, no se regenerarán.)
