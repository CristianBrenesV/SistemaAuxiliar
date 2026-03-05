(() => {
    const INACTIVITY_TIMEOUT = 5 * 60 * 1000; // 5 minutos
    let inactivityTimer;

    function resetTimer() {
        clearTimeout(inactivityTimer);

        const modalElement = document.getElementById('sessionExpiredModal');
        if (!modalElement) return;

        inactivityTimer = setTimeout(() => {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();

            // Redirigir automáticamente después de mostrar el modal
            setTimeout(() => {
                window.location.href = "/login"; // coincide con tu login blade
            }, 3000); // 3 segundos para que el usuario vea el mensaje
        }, INACTIVITY_TIMEOUT);
    }

    ['load', 'mousemove', 'keypress', 'click', 'scroll']
        .forEach(evt => window.addEventListener(evt, resetTimer));
})();
