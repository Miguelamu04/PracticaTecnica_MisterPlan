document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('manage-reservations-body');
    const message = document.getElementById('manage-message');

    if (!tableBody || !message) {
        return;
    }

    const pendingCancellations = new Set();

    tableBody.addEventListener('click', async (event) => {
        const button = event.target.closest('.cancel-reservation');

        if (!button || !tableBody.contains(button)) {
            return;
        }

        const reservationId = button.dataset.id;

        if (pendingCancellations.has(reservationId)) {
            return;
        }

        if (!window.confirm('¿Seguro que quieres cancelar esta reserva?')) {
            return;
        }

        pendingCancellations.add(reservationId);

        button.disabled = true;
        message.textContent = 'Cancelando reserva...';

        try {
            const response = await fetch(
                `/api/reservations/${reservationId}/status`,
                {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: 'cancelada'
                    })
                }
            );

            if (!response.ok) {
                let errorMessage = 'No se ha podido cancelar la reserva.';

                if (response.status === 404) {
                    errorMessage = 'La reserva ya no existe.';
                } else if (response.status === 400) {
                    errorMessage = 'La solicitud de cancelación no es válida.';
                } else if (response.status >= 500) {
                    errorMessage = 'Error del servidor. Inténtalo de nuevo más tarde.';
                }

                throw new Error(errorMessage);
            }

            const row = button.closest('tr');

            if (row) {
                row.cells[2].textContent = 'Cancelada';
            }

            button.replaceWith(document.createTextNode('Cancelada'));
            message.textContent = 'Reserva cancelada correctamente.';
        } catch (error) {
            message.textContent = error.message;
        } finally {
            pendingCancellations.delete(reservationId);

            if (button.isConnected) {
                button.disabled = false;
            }
        }
    });
});
