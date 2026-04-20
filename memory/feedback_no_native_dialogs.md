---
name: No usar alert/confirm/prompt nativos del navegador
description: Nunca usar los dialogs nativos del navegador (alert, confirm, prompt, wire:confirm) en ninguna parte del proyecto
type: feedback
---

Nunca usar `alert()`, `confirm()`, `prompt()` de JavaScript ni `wire:confirm` de Livewire (que usa el confirm nativo del navegador). Tampoco SweetAlert2 modales nativos si se puede evitar.

**Why:** El usuario quiere que TODOS los dialogs del sistema sigan el estilo visual de la app (modales Livewire/Alpine con Tailwind, coherentes con el resto de modales que ya existen — el de eliminar obra, el de eliminar documento). Los dialogs nativos rompen la consistencia visual y no se pueden estilizar.

**How to apply:**
- Para confirmar acciones destructivas: usar un modal inline en el componente Livewire con un flag de estado (p. ej. `$xAEliminarId`) + botones Cancelar/Confirmar — mismo patrón que el modal "Eliminar obra" en `Obras/Index` y el modal "Eliminar documento" en `Documentos/Index`.
- Para validación client-side (ej. tamaño/tipo de archivo): no mostrar `alert()`, confiar en la validación server-side de Livewire que ya pinta el `@error(...)` en la vista. Si se quiere feedback inmediato, usar un estado Alpine local que pinte un mensaje en la propia interfaz.
- Para notificaciones de éxito/error: usar el evento Livewire `notify` que ya está cableado al listener global de Notyf en `resources/js/notify.js`.
- Regla aplica a TODO el proyecto, incluyendo refactors futuros de otros módulos.
