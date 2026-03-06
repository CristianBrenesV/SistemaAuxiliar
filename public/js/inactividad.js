(() => {
    const INACTIVITY_TIMEOUT = 5 * 60 * 1000; // 5 minutos
    let inactivityTimer;

    function logoutAndShowModal() {
        const modalElement = document.getElementById('sessionExpiredModal');
        if (!modalElement) return;

        // Hacer petición al backend para cerrar sesión
        fetch('/logout', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        }).finally(() => {
            // Mostrar modal de sesión expirada
            const modal = new bootstrap.Modal(modalElement);
            modal.show();

            // Redirigir automáticamente después de 3 segundos
            setTimeout(() => {
                window.location.href = "/login";
            }, 3000);
        });
    }

    function resetTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(logoutAndShowModal, INACTIVITY_TIMEOUT);
    }

    ['load', 'mousemove', 'keypress', 'click', 'scroll']
        .forEach(evt => window.addEventListener(evt, resetTimer));
})();
