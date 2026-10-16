
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

        if (!window.confirm('Are you sure you want to cancel this reservation?')) {
            return;
        }

        button.disabled = true;
        message.textContent = 'Cancelling reservation...';

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
                throw new Error('Could not cancel reservation.');
            }

            const row = button.closest('tr');

            if (row) {
                row.cells[2].textContent = 'cancelada';
            }

            button.replaceWith(document.createTextNode('Cancelled'));
            message.textContent = 'Reservation cancelled successfully.';
        } catch (error) {
            message.textContent = error.message;
            button.disabled = false;
        }
    });
});
