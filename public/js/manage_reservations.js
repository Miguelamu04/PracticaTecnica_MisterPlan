
document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('manage-reservations-body');
    const message = document.getElementById('manage-message');

    if (!tableBody || !message) {
        return;
    }

    tableBody.addEventListener('click', async (event) => {
        const button = event.target.closest('.cancel-reservation');

        if (!button || !tableBody.contains(button)) {
            return;
        }

        const reservationId = button.dataset.id;

        if (!window.confirm('¿Seguro que quieres cancelar esta reserva?')) {
            return;
        }

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
                throw new Error('No se ha podido cancelar la reserva.');
            }

            const row = button.closest('tr');

            if (row) {
                row.cells[2].textContent = 'Cancelada';
            }

            button.replaceWith(document.createTextNode('Cancelada'));
            message.textContent = 'Reserva cancelada correctamente.';
        } catch (error) {
            message.textContent = error.message;
            button.disabled = false;
        }
    });
});
