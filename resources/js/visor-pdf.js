function initPdfModal() {

    const modal = document.getElementById('pdfModal');
    const pdfViewer = document.getElementById('pdfViewer');
    const closeBtn = document.getElementById('closePdfModal');

    if (!modal || !pdfViewer) return;

    // Limpiar listeners previos
    document.querySelectorAll('.open-pdf-modal').forEach(btn => {
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
    });

    // Abrir modal
    document.querySelectorAll('.open-pdf-modal').forEach(button => {
        button.addEventListener('click', () => {
            const pdfUrl = button.getAttribute('data-pdf');
            pdfViewer.src = pdfUrl;
            modal.classList.remove('hidden');
        });
    });

    // Cerrar botón
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
            pdfViewer.src = '';
        });
    }

    // Cerrar clic fuera
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
            pdfViewer.src = '';
        }
    });
}

// INICIAL
document.addEventListener('DOMContentLoaded', initPdfModal);

// DESPUÉS DE CADA RENDER LIVEWIRE
document.addEventListener('livewire:navigated', initPdfModal);

document.addEventListener('livewire:initialized', () => {
    Livewire.hook('morph.updated', initPdfModal);
});
