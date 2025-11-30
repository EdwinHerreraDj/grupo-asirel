(() => {
  function handleBrowserEvent(eOrData) {
    const detail = eOrData?.detail ?? eOrData ?? {};
    const mensaje =
      detail?.message ??
      detail?.msg ??
      (typeof detail === "string" ? detail : "Datos guardados correctamente");

    // 🔹 Cerrar modal FC correctamente
    try {
      // Busca el botón con data-fc-dismiss dentro del modal #empresa
      const closeBtn = document.querySelector('#empresa [data-fc-dismiss]');
      if (closeBtn) {
        setTimeout(() => closeBtn.click(), 100); // pequeño delay para evitar conflictos
      } else {
        console.warn('No se encontró el botón de cierre en #empresa');
      }
    } catch (e) {
      console.error('Error al intentar cerrar el modal:', e);
    }

    // 🔹 Mostrar notificación con Notyf
    try {
      const notyf =
        window.__notyf ||
        (window.__notyf = new Notyf({
          duration: 4000,
          dismissible: true,
          position: { x: "right", y: "top" },
        }));
      notyf.success(mensaje);
    } catch (e) {
      console.error("Error al mostrar notificación:", e);
    }

    console.log("Evento recibido correctamente:", mensaje);
  }

  // ✅ Livewire v3
  window.addEventListener("empresa-guardada", handleBrowserEvent);

  // ♻️ Re-enganchar tras navegación interna
  document.addEventListener("livewire:navigated", () => {
    window.removeEventListener("empresa-guardada", handleBrowserEvent);
    window.addEventListener("empresa-guardada", handleBrowserEvent);
  });

  // 🔙 Fallback Livewire v2
  if (window.Livewire?.on) {
    window.Livewire.on("empresaGuardada", (data) => {
      handleBrowserEvent({ detail: data });
    });
  }
})();