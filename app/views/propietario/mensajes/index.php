<?php
/** @var array $chats */
/** @var string|null $chatActivo */
/** @var array $mensajes */
/** @var array|null $otro */
/** @var string $busqueda */
/** @var int $totalNoLeidos */
/** @var string $supabaseUrl */
/** @var string $supabaseAnonKey */
/** @var string $uid */

$e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$fmtFecha = function ($f) {
    if (!$f) return '';
    $ts = strtotime($f);
    if ($ts === false) return '';
    $hoy = strtotime('today');
    if ($ts >= $hoy) return date('H:i', $ts);
    if ($ts >= $hoy - 86400) return 'Ayer ' . date('H:i', $ts);
    return date('d/m/Y H:i', $ts);
};
$iniciales = function ($nombre) {
    $parts = preg_split('/\s+/', trim((string)$nombre));
    $ini = '';
    $firstChar = function_exists('mb_substr') ? 'mb_substr' : 'substr';
    foreach (array_slice($parts, 0, 2) as $p) {
        if ($p !== '') $ini .= strtoupper($firstChar($p, 0, 1));
    }
    return $ini ?: '?';
};
?>
<style>
.chat-wrap { display:grid; grid-template-columns: 340px 1fr; gap:1rem; height: calc(100vh - 140px); min-height: 480px; }
@media (max-width: 767.98px){ .chat-wrap { grid-template-columns: 1fr; height:auto; } .chat-conversacion { display: <?= $chatActivo ? 'block' : 'none' ?>; } .chat-lista { display: <?= $chatActivo ? 'none' : 'block' ?>; } }
.chat-lista, .chat-conversacion { background:#fff; border:1px solid var(--line, #e5e7eb); border-radius:14px; overflow:hidden; display:flex; flex-direction:column; }
.chat-lista .cl-head { padding:.75rem 1rem; border-bottom:1px solid var(--line,#e5e7eb); }
.chat-lista .cl-body { overflow-y:auto; flex:1; }
.hilo { display:flex; gap:.65rem; padding:.7rem 1rem; border-bottom:1px solid #f1f3f5; text-decoration:none; color:inherit; align-items:center; }
.hilo:hover { background:#f8f9fa; }
.hilo.is-active { background:#eef4ff; }
.hilo .avatar { width:42px; height:42px; border-radius:50%; background:#e2e8f0; display:flex; align-items:center; justify-content:center; font-weight:700; color:#475569; flex:none; background-size:cover; background-position:center; }
.hilo .meta { flex:1; min-width:0; }
.hilo .meta .top { display:flex; justify-content:space-between; gap:.5rem; }
.hilo .meta .nombre { font-weight:600; font-size:.92rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.hilo .meta .hora { font-size:.72rem; color:#94a3b8; flex:none; }
.hilo .meta .ult { font-size:.8rem; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.hilo .badge { background:#ef4444; color:#fff; border-radius:999px; font-size:.7rem; min-width:18px; height:18px; display:inline-flex; align-items:center; justify-content:center; padding:0 5px; flex:none; }
.chat-conversacion .cc-head { padding:.75rem 1rem; border-bottom:1px solid var(--line,#e5e7eb); display:flex; align-items:center; gap:.65rem; }
.chat-conversacion .cc-body { flex:1; overflow-y:auto; padding:1rem; background:#f8fafc; display:flex; flex-direction:column; gap:.4rem; }
.chat-conversacion .cc-foot { padding:.75rem 1rem; border-top:1px solid var(--line,#e5e7eb); }
.bubble { max-width:72%; padding:.55rem .8rem; border-radius:14px; font-size:.9rem; line-height:1.35; word-wrap:break-word; white-space:pre-wrap; }
.bubble.mia { align-self:flex-end; background:#2563eb; color:#fff; border-bottom-right-radius:4px; }
.bubble.suya { align-self:flex-start; background:#fff; border:1px solid #e2e8f0; border-bottom-left-radius:4px; }
.bubble .bh { font-size:.66rem; opacity:.7; margin-top:.2rem; display:block; }
.cc-empty { flex:1; display:flex; align-items:center; justify-content:center; color:#94a3b8; text-align:center; padding:2rem; }
.plantillas { display:flex; gap:.4rem; flex-wrap:wrap; margin-bottom:.5rem; }
.plantillas .pl { font-size:.78rem; padding:.25rem .55rem; border:1px solid #cbd5e1; border-radius:999px; background:#f8fafc; cursor:pointer; }
.plantillas .pl:hover { background:#eef4ff; }
</style>

<div class="chat-wrap">

    <!-- IZQUIERDA: lista de hilos -->
    <div class="chat-lista">
        <div class="cl-head">
            <form method="get" action="/mensajes" class="d-flex gap-2" role="search">
                <input type="text" name="q" value="<?= $e($busqueda) ?>" class="form-control form-control-sm" placeholder="Buscar conversación...">
                <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="cl-body">
            <?php if (empty($chats)): ?>
                <div class="cc-empty">
                    <div>
                        <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                        No tienes conversaciones todavía.<br>
                        <small>Cuando un inquilino te contacte desde la ficha de un alojamiento, la conversación aparecerá aquí.</small>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($chats as $h): ?>
                    <?php $activo = ($chatActivo === $h['chat_id']); ?>
                    <a class="hilo <?= $activo ? 'is-active' : '' ?>" href="/mensajes?chat=<?= $e($h['chat_id']) ?>">
                        <div class="avatar" <?= !empty($h['otro_foto']) ? 'style="background-image:url(\'' . $e($h['otro_foto']) . '\')"' : '' ?>><?= empty($h['otro_foto']) ? $e($iniciales(($h['otro_nombre'] ?? '') . ' ' . ($h['otro_apellido'] ?? ''))) : '' ?></div>
                        <div class="meta">
                            <div class="top">
                                <span class="nombre"><?= $e(trim(($h['otro_nombre'] ?? '') . ' ' . ($h['otro_apellido'] ?? ''))) ?></span>
                                <span class="hora"><?= $e($fmtFecha($h['ultimo_fecha'] ?? null)) ?></span>
                            </div>
                            <div class="ult"><?= $e($h['ultimo_contenido'] ?? '') ?></div>
                        </div>
                        <?php if ((int)($h['no_leidos'] ?? 0) > 0): ?>
                            <span class="badge"><?= (int)$h['no_leidos'] > 99 ? '99+' : (int)$h['no_leidos'] ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- DERECHA: conversación -->
    <div class="chat-conversacion">
        <?php if ($chatActivo && $otro): ?>
            <div class="cc-head">
                <div class="avatar" style="width:40px;height:40px;border-radius:50%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;font-weight:700;color:#475569;<?= !empty($otro['url_foto']) ? 'background-image:url(\'' . $e($otro['url_foto']) . '\');background-size:cover;background-position:center;' : '' ?>"><?= empty($otro['url_foto']) ? $e($iniciales(($otro['nombres'] ?? '') . ' ' . ($otro['apellido_paterno'] ?? ''))) : '' ?></div>
                <div>
                    <div style="font-weight:600"><?= $e(trim(($otro['nombres'] ?? '') . ' ' . ($otro['apellido_paterno'] ?? ''))) ?></div>
                    <div style="font-size:.75rem;color:#94a3b8">Inquilino</div>
                </div>
            </div>
            <div class="cc-body" id="cc-body">
                <?php foreach ($mensajes as $m): ?>
                    <div class="bubble <?= !empty($m['es_mio']) ? 'mia' : 'suya' ?>" data-id="<?= $e($m['mensaje_id']) ?>">
                        <?= $e($m['contenido'] ?? '') ?>
                        <span class="bh"><?= $e($fmtFecha($m['fecha_envio'] ?? null)) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="cc-foot">
                <div class="plantillas">
                    <span class="pl" onclick="usarPlantilla('Hola, claro, sigue disponible 😊')">Hola, claro, sigue disponible 😊</span>
                    <span class="pl" onclick="usarPlantilla('¿Cuándo te gustaría visitar el lugar?')">¿Cuándo te gustaría visitar el lugar?</span>
                    <span class="pl" onclick="usarPlantilla('Te paso más info por aquí')">Te paso más info por aquí</span>
                </div>
                <form id="form-enviar" onsubmit="return enviarMensaje(event)" class="d-flex gap-2">
                    <textarea id="contenido" class="form-control form-control-sm" rows="2" maxlength="2000" placeholder="Escribe un mensaje... (máx 2000 caracteres)" required></textarea>
                    <button class="btn btn-primary btn-sm align-self-end" type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        <?php else: ?>
            <div class="cc-empty">
                <div>
                    <i class="fas fa-comments fa-2x mb-3 d-block"></i>
                    Selecciona una conversación<br>
                    <small>Elige un hilo de la izquierda para ver los mensajes.</small>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php if ($chatActivo && $otro && $supabaseAnonKey !== ''): ?>
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script>
const SUPA_URL = <?= json_encode($supabaseUrl) ?>;
const SUPA_KEY = <?= json_encode($supabaseAnonKey) ?>;
const chatId   = <?= json_encode($chatActivo) ?>;
const uid      = <?= json_encode($uid) ?>;

const body = document.getElementById('cc-body');
const textarea = document.getElementById('contenido');
let ultimoId = <?= !empty($mensajes) ? json_encode($mensajes[count($mensajes)-1]['mensaje_id']) : json_encode('') ?>;
let conectado = false;
let pollTimer = null;

function scrollAbajo() { if (body) body.scrollTop = body.scrollHeight; }
scrollAbajo();

function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
function fmtHora(f) {
    if (!f) return '';
    const ts = new Date(f.replace(' ', 'T')).getTime();
    if (isNaN(ts)) return '';
    const d = new Date(ts);
    return d.toLocaleTimeString('es-PE', {hour:'2-digit', minute:'2-digit'});
}

function appendMensaje(m) {
    if (!m || !m.mensaje_id) return;
    if (body.querySelector('.bubble[data-id="' + m.mensaje_id + '"]')) return; // dedupe
    const esMio = (m.usuario_id === uid);
    const div = document.createElement('div');
    div.className = 'bubble ' + (esMio ? 'mia' : 'suya');
    div.setAttribute('data-id', m.mensaje_id);
    div.innerHTML = escapeHtml(m.contenido || '') + '<span class="bh">' + escapeHtml(fmtHora(m.fecha_envio)) + '</span>';
    body.appendChild(div);
    ultimoId = m.mensaje_id;
    scrollAbajo();
}

function usarPlantilla(txt) { if (textarea) { textarea.value = txt; textarea.focus(); } }

async function enviarMensaje(ev) {
    ev.preventDefault();
    const contenido = textarea.value;
    if (!contenido.trim()) return false;
    const btn = ev.target.querySelector('button');
    btn.disabled = true;
    try {
        const res = await fetch('/mensajes/enviar', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'chat_id=' + encodeURIComponent(chatId) + '&contenido=' + encodeURIComponent(contenido)
        });
        const data = await res.json();
        if (data.ok) {
            appendMensaje(data.mensaje);
            textarea.value = '';
        } else {
            Swal.fire({ icon:'error', title: data.error || 'Error al enviar', toast:true, position:'top-end', showConfirmButton:false, timer:3000 });
        }
    } catch (err) {
        Swal.fire({ icon:'error', title:'Error de red', toast:true, position:'top-end', showConfirmButton:false, timer:3000 });
    } finally {
        btn.disabled = false;
    }
    return false;
}

function iniciarPolling() {
    if (pollTimer) return;
    pollTimer = setInterval(async () => {
        try {
            const url = '/mensajes/nuevo?chat=' + encodeURIComponent(chatId) + '&ultimo=' + encodeURIComponent(ultimoId || '');
            const res = await fetch(url);
            const data = await res.json();
            if (data.ok && Array.isArray(data.mensajes)) {
                data.mensajes.forEach(appendMensaje);
            }
        } catch (e) {}
    }, 5000);
}

// Realtime (si hay SDK cargado)
if (typeof supabase !== 'undefined') {
    try {
        const sb = supabase.createClient(SUPA_URL, SUPA_KEY);
        sb.channel('chat:' + chatId)
          .on('postgres_changes',
              { event:'INSERT', schema:'public', table:'mensaje', filter:'chat_id=eq.' + chatId },
              payload => { conectado = true; appendMensaje(payload.new); })
          .subscribe(status => { if (status === 'SUBSCRIBED') conectado = true; });
    } catch (e) { console.warn('Realtime init error', e); }
    // Fallback: si a los 5s no conectó, polling
    setTimeout(() => { if (!conectado) iniciarPolling(); }, 5000);
} else {
    iniciarPolling();
}
</script>
<?php elseif ($chatActivo && $otro && $supabaseAnonKey === ''): ?>
<script>
// Realtime deshabilitado (sin anon key) — solo polling.
const chatId = <?= json_encode($chatActivo) ?>;
const uid = <?= json_encode($uid) ?>;
const body = document.getElementById('cc-body');
const textarea = document.getElementById('contenido');
let ultimoId = <?= !empty($mensajes) ? json_encode($mensajes[count($mensajes)-1]['mensaje_id']) : json_encode('') ?>;
function scrollAbajo(){ if(body) body.scrollTop = body.scrollHeight; }
scrollAbajo();
function escapeHtml(s){ return String(s).replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
function fmtHora(f){ if(!f) return ''; const ts=new Date(f.replace(' ','T')).getTime(); if(isNaN(ts)) return ''; return new Date(ts).toLocaleTimeString('es-PE',{hour:'2-digit',minute:'2-digit'}); }
function appendMensaje(m){ if(!m||!m.mensaje_id) return; if(body.querySelector('.bubble[data-id="'+m.mensaje_id+'"]')) return; const esMio=(m.usuario_id===uid); const div=document.createElement('div'); div.className='bubble '+(esMio?'mia':'suya'); div.setAttribute('data-id',m.mensaje_id); div.innerHTML=escapeHtml(m.contenido||'')+'<span class="bh">'+escapeHtml(fmtHora(m.fecha_envio))+'</span>'; body.appendChild(div); ultimoId=m.mensaje_id; scrollAbajo(); }
function usarPlantilla(txt){ if(textarea){ textarea.value=txt; textarea.focus(); } }
async function enviarMensaje(ev){ ev.preventDefault(); const contenido=textarea.value; if(!contenido.trim()) return false; const btn=ev.target.querySelector('button'); btn.disabled=true; try{ const res=await fetch('/mensajes/enviar',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'chat_id='+encodeURIComponent(chatId)+'&contenido='+encodeURIComponent(contenido)}); const data=await res.json(); if(data.ok){ appendMensaje(data.mensaje); textarea.value=''; } else { Swal.fire({icon:'error',title:data.error||'Error',toast:true,position:'top-end',showConfirmButton:false,timer:3000}); } } catch(e){ Swal.fire({icon:'error',title:'Error de red',toast:true,position:'top-end',showConfirmButton:false,timer:3000}); } finally{ btn.disabled=false; } return false; }
setInterval(async ()=>{ try{ const res=await fetch('/mensajes/nuevo?chat='+encodeURIComponent(chatId)+'&ultimo='+encodeURIComponent(ultimoId||'')); const data=await res.json(); if(data.ok&&Array.isArray(data.mensajes)) data.mensajes.forEach(appendMensaje); }catch(e){} }, 5000);
</script>
<?php endif; ?>
