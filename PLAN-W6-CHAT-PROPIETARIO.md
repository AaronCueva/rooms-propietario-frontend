# Plan de Implementación — W6-PROPIETARIO: Chat / mensajería (lado propietario)

> **Oleada:** W6 (lado propietario) · **Ref. doc:** DF-NidoUniversitario §4.5.1 (chat) consumido por §4.x (panel propietario) · **Depende de:** que el lado inquilino ya haya creado hilos (W6 inquilino ✅) · **Desbloquea:** comunicación bidireccional real inquilino ↔ propietario
> **Estado:** ⬜ No iniciado · **Creado:** 2026-07-09
> **Audiencia:** este plan es **autocontenido** para entregarlo a otro equipo/repo (portal propietario). No asume acceso al código del portal inquilino.

---

## 1. Objetivo

Que el **propietario** pueda ver y responder los mensajes que los inquilinos le envían desde la ficha del alojamiento ("Contactar Anfitrión"). El propietario tiene un inbox `/mensajes` (o equivalente en su portal) con la lista de conversaciones abiertas, abre un hilo, lee el historial y responde; los mensajes nuevos llegan **en tiempo real vía Supabase Realtime** (sin recargar).

**Criterio de aceptación:**
> Un inquilino envía un mensaje desde su portal → el propietario lo ve llegar solo en su inbox (realtime) → el propietario responde → el inquilino lo recibe solo en su conversación (realtime). Ambos ven historial, burbujas propias/ajenas alineadas, y conteo de no leídos.

---

## 2. Contexto compartido (ya configurado desde el lado inquilino)

El chat es **transversal**: ambas partes (inquilino y propietario) operan sobre las **mismas tablas** de un único proyecto Supabase. El lado inquilino ya configuró:

- **Realtime** sobre la tabla `mensaje` (`alter publication supabase_realtime add table mensaje`).
- **RLS** + policy `anon select` sobre `mensaje` (para que el SDK JS reciba pushes con la `anon key`).
- Literales de estado de lectura `'ENVIADO'` (no leído) / `'LEIDO'` (no existe catálogo).

> **El portal propietario NO debe re-aplicar esos ALTERs** (ya están hechos). Solo debe **verificar** (§5.4) y usar la misma conexión + anon key.

### 2.1 Credenciales Supabase (mismo proyecto)
```
SUPABASE_URL      = https://lokjiueialuwrulybgut.supabase.co
SUPABASE_ANON_KEY = eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imxva2ppdWVpYWx1d3J1bHliZ3V0Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODAyODE1MjcsImV4cCI6MjA5NTg1NzUyN30.z5tcJAOMvqe-d8jJs6zC0eRbUPw4wnU23EO9yTrSnd8
```
Conexión PHP (pooler transaccional, rol postgres, bypassrls):
```
host=aws-1-us-east-2.pooler.supabase.com  port=6543  dbname=postgres
username=postgres.lokjiueialuwrulybgut  password=<la misma del portal inquilino>
```
> La `anon key` es **publishable** (va en el navegador). La password de postgres **no** va en el cliente; solo en el backend PHP.

---

## 3. Alcance (v1 propietario)

| # | Decisión | Opción |
|---|---|---|
| D1 | Tiempo real | Supabase Realtime (SDK JS v2 por CDN), suscripción a `INSERT` en `mensaje` filtrado por `chat_id`. |
| D2 | Tipos de mensaje | Solo texto (≤2000 chars) + emojis. Sin imágenes/PDF. |
| D3 | Entry points | Inbox en el panel/dashboard del propietario + badge en el nav. **No** tiene "Contactar anfitrión" (eso es del inquilino). |
| D4 | Búsqueda | Búsqueda por texto en el inbox (nombre del inquilino o contenido). Sin archivar. |

**Fuera de v1:** multimedia, archivar chats, estados granulares enviado/entregado (solo leído/no leído).

---

## 4. Arquitectura — archivos a crear (portal propietario)

> Asume un MVC PHP con autoload `App\`, PDO singleton, sesiones PHP, layouts. Si el portal propietario usa otra convención, adaptar rutas/namespaces manteniendo la lógica y el SQL.

### Crear
| Archivo | Rol |
|---|---|
| `app/config/supabase.php` | Constantes `SUPABASE_URL`, `SUPABASE_ANON_KEY` (§2.1). |
| `app/models/Chat.php` | Modelo: hilos, participantes, mensajes, enviar, leer, conteo no leídos. (§6) |
| `app/controllers/MensajeController.php` | Inbox, conversación, enviar (POST), nuevos (AJAX fallback). (§7) |
| `app/views/mensajes/index.php` | Inbox (lista hilos + panel conversación) con SDK Realtime. (§8) |

### Modificar
| Archivo | Cambio |
|---|---|
| `index.php` (o ruteador) | Registrar rutas `/mensajes`, `/mensajes/nuevo`, `/mensajes/enviar`. |
| Layout del panel propietario | Link "Mensajes" en nav con badge de no leídos. |

> **No** se necesita ruta `/mensajes/abrir?alojamiento=...` en el lado propietario (esa es del inquilino, que crea el hilo). El propietario solo abre hilos **ya existentes** donde es participante.

---

## 5. BD — schema y verificación

### 5.1 Tablas (schema `public`, ya existen)
```
chat
  chat_id            uuid PK default gen_random_uuid()
  fecha_creacion     timestamp null
  habilitado         boolean default true
  creado, creado_por, modificado, modificado_por   (auditoría)

chat_usuario
  chat_usuario_id    uuid PK default gen_random_uuid()
  chat_id            uuid → chat
  usuario_id         uuid → usuario
  habilitado         boolean null
  creado, creado_por, modificado, modificado_por

mensaje
  mensaje_id         uuid PK default gen_random_uuid()
  contenido          varchar null
  fecha_envio        timestamp null
  estado_lectura_codigo   varchar null   -- 'ENVIADO' | 'LEIDO'  (sin catálogo)
  chat_id            uuid → chat
  usuario_id         uuid → usuario  (EMISOR del mensaje)
  multimedia_id      uuid null
  habilitado         boolean default true
  creado, creado_por, modificado, modificado_por
```
`usuario`: `usuario_id, nombres, apellido_paterno, url_foto, ...`. El propietario tiene `rol_id` correspondiente a `PROPIETARIO` (o `OWNER`).

### 5.2 Realtime + RLS (ya aplicados — solo verificar)
```sql
-- Deben dar ON / YES / la policy listada:
SELECT relrowsecurity FROM pg_class WHERE relname='mensaje';                       -- true
SELECT tablename FROM pg_publication_tables WHERE pubname='supabase_realtime' AND tablename='mensaje';  -- mensaje
SELECT policyname FROM pg_policies WHERE tablename='mensaje';                      -- anon lee mensajes para realtime
```
Si algo falta, aplicar (idempotente):
```sql
alter publication supabase_realtime add table mensaje;
alter table mensaje enable row level security;
drop policy if exists "anon lee mensajes para realtime" on mensaje;
create policy "anon lee mensajes para realtime" on mensaje for select to anon using (true);
```

### 5.3 `estado_lectura_codigo`
No hay catálogo. Usar literales `'ENVIADO'` (no leído) y `'LEIDO'`. Mensaje nuevo → `'ENVIADO'`; al abrir la conversación, `UPDATE ... SET estado_lectura_codigo='LEIDO'` para los del otro.

### 5.4 Trade-off de seguridad (heredado)
La policy `anon using (true)` permite al rol anon leer **cualquier** `mensaje`. La app autentica por **sesión PHP** (no Supabase Auth), así que el control de acceso real lo hace el **backend PHP** en cada endpoint (valida participación en el chat). El SDK cliente solo se suscribe filtrando por `chat_id` (UUID difícil de adivinar). Riesgo: quien conozca un `chat_id` podría suscribirse a esa conversación. Aceptable v1; hardening futuro = integrar Supabase Auth para policies con `auth.uid()`.

---

## 6. Modelo `Chat` (PHP)

Namespace según el portal propietario (ej. `App\Models`). Usa PDO singleton. Mismos métodos que el lado inquilino (el chat es simétrico). **Firmas exactas:**

- `getChatsByUsuario(string $usuario_id, string $busqueda = ''): array`
- `esParticipante(string $chat_id, string $usuario_id): bool`
- `getMensajes(string $chat_id, string $usuario_id): array`
- `enviarMensaje(string $chat_id, string $usuario_id, string $contenido): ?array`
- `marcarLeido(string $chat_id, string $usuario_id): void`
- `contarNoLeidos(string $usuario_id): int`
- `getOtroParticipante(string $chat_id, string $usuario_id): ?array`

Constantes: `ESTADO_ENVIADO='ENVIADO'`, `ESTADO_LEIDO='LEIDO'`, `MAX_LEN=2000`.

### 6.1 SQL (implementación de referencia)

**`esParticipante`**
```sql
SELECT 1 FROM chat_usuario WHERE chat_id=:c AND usuario_id=:u AND habilitado=true LIMIT 1
```

**`getChatsByUsuario`** — hilos del usuario con el otro participante, último mensaje y no leídos.
```sql
SELECT cu.chat_id,
       u.usuario_id AS otro_id, u.nombres AS otro_nombre,
       u.apellido_paterno AS otro_apellido, u.url_foto AS otro_foto,
       lm.contenido AS ultimo_contenido, lm.fecha_envio AS ultimo_fecha,
       (SELECT COUNT(*) FROM mensaje m
          WHERE m.chat_id = cu.chat_id AND m.usuario_id <> :uid
            AND m.estado_lectura_codigo <> 'LEIDO' AND m.habilitado = true) AS no_leidos
FROM chat_usuario cu
JOIN chat ch ON cu.chat_id = ch.chat_id
JOIN chat_usuario cu2 ON cu2.chat_id = cu.chat_id AND cu2.usuario_id <> :uid
JOIN usuario u ON cu2.usuario_id = u.usuario_id
LEFT JOIN (
   SELECT DISTINCT ON (chat_id) chat_id, contenido, fecha_envio
   FROM mensaje WHERE habilitado = true
   ORDER BY chat_id, fecha_envio DESC
) lm ON lm.chat_id = cu.chat_id
WHERE cu.usuario_id = :uid AND cu.habilitado = true
ORDER BY ultimo_fecha DESC NULLS LAST
```
Filtro de búsqueda opcional (`$busqueda` no vacía, `:q = '%'.$busqueda.'%'`):
```sql
AND (u.nombres ILIKE :q OR u.apellido_paterno ILIKE :q
     OR EXISTS (SELECT 1 FROM mensaje m
                  WHERE m.chat_id = cu.chat_id AND m.contenido ILIKE :q))
```
Normalizar `no_leidos` a `int` en PHP.

**`getMensajes`** — valida participación; si no, devuelve `[]`.
```sql
SELECT mensaje_id, contenido, fecha_envio, usuario_id, (usuario_id = :u) AS es_mio
FROM mensaje
WHERE chat_id = :c AND habilitado = true
ORDER BY fecha_envio ASC, creado ASC
```
Normalizar `es_mio` a bool real en PHP (PDO pgsql puede devolver `'t'`/`'f'` o `true`/`false`):
```php
$r['es_mio'] = in_array($r['es_mio'], [true, 't', 'true', 1, '1'], true);
```

**`enviarMensaje`** — valida participación, `trim`, rechaza vacío o `> MAX_LEN` (usar `\mb_strlen` con fallback `strlen` — **llamarla con `\` global** si el modelo está en un namespace). Inserta y devuelve la fila.
```sql
INSERT INTO mensaje (contenido, fecha_envio, estado_lectura_codigo, chat_id, usuario_id, habilitado, creado_por)
VALUES (:c, now(), 'ENVIADO', :chat, :u, true, :upor)
RETURNING mensaje_id, contenido, fecha_envio, usuario_id
```
> **⚠️ Importante:** NO reuses el mismo bind para `usuario_id` (uuid) y `creado_por` (varchar). Usa `:u` para `usuario_id` y `:upor` para `creado_por` (ambos con el mismo valor `$usuario_id`). Reutilizar un bind entre columnas de tipo distinto produce `inconsistent types deduced for parameter` en Postgres. Devuelve la fila + `'es_mio' => true`.

**`marcarLeido`** — marca como leídos los mensajes del otro.
```sql
UPDATE mensaje
SET estado_lectura_codigo = 'LEIDO', modificado = now(), modificado_por = :upor
WHERE chat_id = :c AND usuario_id <> :u
  AND estado_lectura_codigo <> 'LEIDO' AND habilitado = true
```
(Mismo bind `:u` para `usuario_id` uuid y `:upor` para `modificado_por` varchar.)

**`contarNoLeidos`**
```sql
SELECT COUNT(*) FROM mensaje m
WHERE m.usuario_id <> :u AND m.estado_lectura_codigo <> 'LEIDO' AND m.habilitado = true
  AND EXISTS (SELECT 1 FROM chat_usuario cu
                WHERE cu.chat_id = m.chat_id AND cu.usuario_id = :u AND cu.habilitado = true)
```

**`getOtroParticipante`**
```sql
SELECT u.usuario_id, u.nombres, u.apellido_paterno, u.url_foto
FROM chat_usuario cu JOIN usuario u ON cu.usuario_id = u.usuario_id
WHERE cu.chat_id = :c AND cu.usuario_id <> :u AND cu.habilitado = true
LIMIT 1
```

---

## 7. Controller `MensajeController`

```php
class MensajeController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['usuario_id'])) $this->redirect('/login'); // o la ruta de login del portal propietario
    }

    public function index() {
        $uid = $_SESSION['usuario_id'];
        $chatActivo = isset($_GET['chat']) ? (string)$_GET['chat'] : null;
        $busqueda = trim($_GET['q'] ?? '');
        $cm = new Chat();
        $chats = $cm->getChatsByUsuario($uid, $busqueda);
        $mensajes = []; $otro = null;
        if ($chatActivo && $cm->esParticipante($chatActivo, $uid)) {
            $mensajes = $cm->getMensajes($chatActivo, $uid);
            $cm->marcarLeido($chatActivo, $uid);
            $otro = $cm->getOtroParticipante($chatActivo, $uid);
        }
        // Si chatActivo pero NO es participante → mensajes=[], otro=null (no exponer datos ajenos)
        $totalNoLeidos = $cm->contarNoLeidos($uid);
        require_once __DIR__ . '/../config/supabase.php';
        $this->render('mensajes/index', [
            'chats'=>$chats, 'chatActivo'=>$chatActivo, 'mensajes'=>$mensajes,
            'otro'=>$otro, 'busqueda'=>$busqueda, 'totalNoLeidos'=>$totalNoLeidos,
            'supabaseUrl'=>SUPABASE_URL, 'supabaseAnonKey'=>SUPABASE_ANON_KEY, 'uid'=>$uid,
        ], 'panel'); // layout del portal propietario
    }

    public function enviar() {
        $uid = $_SESSION['usuario_id'];
        $chatId = (string)($_POST['chat_id'] ?? '');
        $contenido = (string)($_POST['contenido'] ?? '');
        header('Content-Type: application/json; charset=utf-8');
        if ($chatId === '') { echo json_encode(['ok'=>false,'error'=>'chat inválido']); exit; }
        $cm = new Chat();
        if (!$cm->esParticipante($chatId, $uid)) { echo json_encode(['ok'=>false,'error'=>'no autorizado']); exit; }
        $msg = $cm->enviarMensaje($chatId, $uid, $contenido);
        if ($msg === null) { echo json_encode(['ok'=>false,'error'=>'mensaje inválido (vacío o >2000 chars)']); exit; }
        echo json_encode(['ok'=>true, 'mensaje'=>$msg]); exit;
    }

    public function nuevo() {
        // Fallback AJAX: /mensajes/nuevo?chat={id}&ultimo={mensaje_id}
        $uid = $_SESSION['usuario_id'];
        $chatId = (string)($_GET['chat'] ?? '');
        $ultimo = $_GET['ultimo'] ?? '';
        header('Content-Type: application/json; charset=utf-8');
        $cm = new Chat();
        if ($chatId === '' || !$cm->esParticipante($chatId, $uid)) { echo json_encode(['ok'=>false,'mensajes'=>[]]); exit; }
        $todos = $cm->getMensajes($chatId, $uid);
        if ($ultimo === '') { echo json_encode(['ok'=>true,'mensajes'=>$todos]); exit; }
        $nuevos = []; $encontrado = false;
        foreach ($todos as $m) {
            if ($encontrado) $nuevos[] = $m;
            elseif ($m['mensaje_id'] === $ultimo) $encontrado = true;
        }
        echo json_encode(['ok'=>true,'mensajes'=>$nuevos]); exit;
    }
}
```

### 7.1 Rutas
```
GET  /mensajes                 → MensajeController::index
GET  /mensajes/nuevo?chat=&ultimo=  → MensajeController::nuevo   (AJAX fallback)
POST /mensajes/enviar          → MensajeController::enviar      (JSON)
```
Todos requieren sesión (constructor → redirect a login).

---

## 8. Vista `mensajes/index.php` (layout del panel propietario)

Layout 2 paneles (igual que el lado inquilino — el chat es simétrico):

- **Izquierda (lista de hilos):** buscador `?q=`; cada hilo = avatar/inicial del **inquilino**, nombre, último mensaje, fecha, badge no leídos; hilo activo destacado; link `/mensajes?chat={id}`. Estado vacío: "No tienes conversaciones todavía. Cuando un inquilino te contacte desde la ficha de un alojamiento, la conversación aparecerá aquí."
- **Derecha (conversación):** header con nombre del inquilino; lista de mensajes (bubbles: propios a la derecha, ajenos a la izquierda); textarea + botón enviar; (opcional) plantillas rápidas: "Hola, claro, sigue disponible 😊", "¿Cuándo te gustaría visitar el lugar?", "Te paso más info por aquí".

Variables inyectadas: `$chats, $chatActivo, $mensajes, $otro, $busqueda, $totalNoLeidos, $supabaseUrl, $supabaseAnonKey, $uid`.

### 8.1 Realtime (SDK Supabase v2 por CDN, solo si hay `$chatActivo` y `$supabaseAnonKey !== ''`)
```html
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script>
const SUPA_URL = <?= json_encode($supabaseUrl) ?>;
const SUPA_KEY = <?= json_encode($supabaseAnonKey) ?>;
const chatId   = <?= json_encode($chatActivo) ?>;
const uid      = <?= json_encode($uid) ?>;
const sb = supabase.createClient(SUPA_URL, SUPA_KEY);
let conectado = false;
sb.channel('chat:' + chatId)
  .on('postgres_changes',
      { event:'INSERT', schema:'public', table:'mensaje', filter:'chat_id=eq.' + chatId },
      payload => { conectado = true; appendMensaje(payload.new); })
  .subscribe(status => { if (status === 'SUBSCRIBED') conectado = true; });
// Fallback: si a los 5s no conectó, polling cada 5s a /mensajes/nuevo
setTimeout(() => { if (!conectado) iniciarPolling(); }, 5000);
</script>
```
- `appendMensaje(m)`: `esMio = (m.usuario_id === uid)`; renderiza bubble; scroll al fondo; dedupe por `mensaje_id`.
- Envío: `fetch('/mensajes/enviar', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'chat_id='+encodeURIComponent(chatId)+'&contenido='+encodeURIComponent(texto) })` → on ok append local; on error Swal.
- `iniciarPolling()`: cada 5s `fetch('/mensajes/nuevo?chat='+chatId+'&ultimo='+ultimoId)` → append nuevos → actualizar `ultimoId`.
- Si `$supabaseAnonKey === ''`: arrancar polling directo + aviso discreto "Realtime deshabilitado".
- Escapar todo contenido/nombre con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` (emojis UTF-8 permitidos).

---

## 9. Integración con el panel propietario

- **Nav/layout:** link "Mensajes" → `/mensajes` con badge `$totalNoLeidos` si >0 (ver §10).
- **Dashboard:** si el dashboard del propietario tiene secciones, agregar "Mensajes recientes" (últimos 3-5 hilos) con link al inbox.
- **Login:** el portal propietario ya autentica propietarios (rol `PROPIETARIO`/`OWNER`). El controller solo requiere `$_SESSION['usuario_id']`; no necesita distinguir rol para el chat (el propietario es participante de los chats donde aparece en `chat_usuario`).

---

## 10. Badge de no leídos en el nav

En el layout del panel propietario, si hay sesión:
```php
$noLeidos = (new Chat())->contarNoLeidos($_SESSION['usuario_id']);
```
Mostrar link "Mensajes" + badge si `$noLeidos > 0`. (Agrega una query por página para usuarios logueados — aceptable v1.)

---

## 11. Riesgos / notas técnicas

- **Auth PHP vs Supabase Auth:** la app autentica por sesión PHP, no Supabase Auth → no se puede usar `auth.uid()` en RLS. v1 usa `anon using(true)` + filtro cliente por `chat_id`. Hardening futuro: Supabase Auth o JWT custom. Mientras tanto, quien conozca un `chat_id` (UUID) podría suscribirse. Riesgo bajo pero real.
- **`mb_strlen` en namespace:** llamarla como `\mb_strlen` (global) o fallback `strlen`. Sin la `\`, PHP busca `App\Models\mb_strlen` y falla.
- **Binds reusados entre tipos distintos:** NUNCA reuses un mismo placeholder para una columna uuid y una varchar en la misma sentencia (Postgres deduce tipos inconsistentes). Usa nombres separados (`:u` vs `:upor`).
- **`estado_lectura_codigo`:** literales `'ENVIADO'`/`'LEIDO'`. Sin catálogo.
- **`php -S` dev server:** el realtime va del SDK JS directo a Supabase; no toca el PHP server. Sin problema de single-worker.
- **Sin motor de plantillas:** escapar `htmlspecialchars` al renderizar `contenido`. Emojis: permitir (UTF-8).
- **Simetría:** el modelo/controller/vista son **idénticos en lógica** a los del lado inquilino. La única diferencia es el entry point (el propietario no crea hilos; los recibe) y el layout. Si el portal propietario y el inquilino compartieran código (monorepo), se podría reusar el mismo `Chat` y `MensajeController`. Como son repos separados, se replica.

---

## 12. Tareas finas (checklist)

- [ ] **12.1** `app/config/supabase.php` con URL + anon key (§2.1).
- [ ] **12.2** Verificar Realtime + RLS + policy sobre `mensaje` (§5.2); aplicar solo si falta.
- [ ] **12.3** Modelo `Chat` (§6) con los 7 métodos y constantes; ojo `mb_strlen` global y binds `:u`/`:upor`.
- [ ] **12.4** Controller `MensajeController` (index, enviar, nuevo) (§7).
- [ ] **12.5** Rutas `/mensajes`, `/mensajes/nuevo`, `/mensajes/enviar` (§7.1).
- [ ] **12.6** Vista `mensajes/index.php` (inbox + conversación) (§8).
- [ ] **12.7** SDK Realtime + fallback polling (§8.1).
- [ ] **12.8** Link "Mensajes" + badge en nav del panel propietario (§9, §10).
- [ ] **12.9** Pruebas manuales bidireccionales (§13).

---

## 13. Criterios de aceptación / pruebas manuales

1. Un inquilino (portal inquilino) envía "Hola, ¿sigue disponible?" desde la ficha → el propietario ve el hilo **aparecer** en su inbox (o el mensaje llegar vía realtime si ya tenía el inbox abierto).
2. El propietario abre el hilo → ve el historial con la burbuja del inquilino a la izquierda.
3. El propietario responde → su burbuja aparece a la derecha al instante; el inquilino la recibe **sola** vía realtime en su conversación.
4. Marcar leído: al abrir un hilo con mensajes no leídos, el badge del nav y del hilo se actualizan.
5. Inbox: lista de hilos con último mensaje y conteo no leídos, ordenados por reciente.
6. Búsqueda por texto filtra hilos (nombre del inquilino o contenido).
7. No logueado → redirect a login en cualquier endpoint.
8. Validación: contenido vacío o >2000 chars rechazado (`{ok:false}`).
9. Fallback: si realtime no conecta, el polling trae los mensajes nuevos.
10. Un usuario que **no** es participante de un chat no puede verlo (`esParticipante` bloquea; no expone datos ajenos).
11. Sin errores PHP.

---

## 14. Estado global

| Tarea | Estado |
|---|---|
| 12.1 Config supabase | ⬜ |
| 12.2 Verificar Realtime + RLS | ⬜ |
| 12.3 Modelo Chat | ⬜ |
| 12.4 Controller Mensaje | ⬜ |
| 12.5 Rutas | ⬜ |
| 12.6 Vista mensajes | ⬜ |
| 12.7 Realtime SDK + fallback | ⬜ |
| 12.8 Nav + badge | ⬜ |
| 12.9 Pruebas | ⬜ |

Leyenda: ⬜ pendiente · 🟡 parcial · ✅ done

---

## 15. Notas para el implementador

- **El lado inquilino ya está hecho y verificado** (en otro repo). Los hilos se crean cuando un inquilino hace `GET /mensajes/abrir?alojamiento={id}` en su portal; eso inserta en `chat` + `chat_usuario` (dos filas: inquilino y propietario). Tu portal propietario **solo lee y responde** — no necesitas crear hilos.
- **Mismo proyecto Supabase, mismas tablas.** No crees tablas nuevas ni re-apliques los `ALTER` de realtime/RLS (ya hechos). Solo verifica (§5.2).
- **El `usuario_id` del propietario** es el mismo que el `usuario_id` de `alojamiento.usuario_id` (el propietario del alojamiento). Cuando el inquilino abre chat con un alojamiento, el `chat_usuario` del propietario usa ese id. Tu sesión propietario debe tener `$_SESSION['usuario_id']` = ese uuid.
- **Valida con una prueba real:** pide al equipo inquilino que envíe un mensaje a un alojamiento cuyo propietario sea un usuario de prueba que tú controles; abre tu inbox propietario y verifica que llega.
