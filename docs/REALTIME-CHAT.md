# Chat en tiempo real — habilitar push instantáneo en Supabase

El chat ya funciona en tiempo real (entrega ≤ ~2s por polling + push de Supabase
Realtime cuando está disponible). Para que el push sea **instantáneo** (sin
depender del polling de 2s), la tabla `mensaje` debe estar publicada en el canal
de Realtime de Supabase.

## Cómo habilitarlo (una sola vez)

1. Ir al **SQL Editor** del proyecto Supabase
   (`https://lokjiueialuwrulybgut.supabase.co`).
2. Ejecutar:

```sql
-- Publicar la tabla mensaje en el canal realtime (INSERT)
alter publication supabase_realtime add table mensaje;
```

3. Verificar que exista una política RLS de `SELECT` para `mensaje` que permita
   leer las filas del chat con la `anon key` (Realtime respeta RLS). Si la tabla
   no tiene RLS o no hay políticas, el navegador no recibirá los eventos. Ejemplo
   mínimo si los participantes están en la fila `chat`:

```sql
-- Solo si necesitas abrir lectura al anon (ajustar a tu modelo de permisos):
-- alter table mensaje enable row level security;
-- create policy "lectura mensajes chat"
--   on mensaje for select to anon
--   using (
--     exists (
--       select 1 from chat c
--       where c.chat_id = mensaje.chat_id
--         and (c.usuario1_id = auth.uid() or c.usuario2_id = auth.uid())
--     )
--   );
```

4. Para revertir:

```sql
alter publication supabase_realtime drop table mensaje;
```

## Notas

- El frontend ya está suscrito al canal `postgres_changes` (INSERT) filtrado por
  `chat_id`, así que al publicar la tabla los mensajes llegarán instantáneamente.
- Aunque el push no esté habilitado, el polling de 2s mantiene el chat con
  latencia baja como red de seguridad.
