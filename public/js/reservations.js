
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('reservation-filters');
    const tableBody = document.getElementById('reservations-body');
    const message = document.getElementById('filter-message');
    const createForm = document.getElementById('create-reservation-form');
    const createMessage = document.getElementById('create-message');

    if (!form || !tableBody || !message) {
        return;
    }

    function formatDate(value) {
        const [year, month, day] = value.split('-');
        return `${day}/${month}/${year}`;
    }

    function formatAmount(value) {
        return Number(value).toLocaleString('es-ES', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + ' €';
    }

    function addCell(row, value) {
        const cell = document.createElement('td');
        cell.textContent = value;
        row.appendChild(cell);
        return cell;
    }

    function renderReservations(reservations) {
        tableBody.replaceChildren();

        if (reservations.length === 0) {
            const row = document.createElement('tr');
            const cell = addCell(row, 'No reservations found.');
            cell.colSpan = 8;
            tableBody.appendChild(row);
            return;
        }

        reservations.forEach((reservation) => {
            const row = document.createElement('tr');

            addCell(row, reservation.nombre_huesped);
            addCell(row, reservation.nombre_alojamiento);
            addCell(row, formatDate(reservation.fecha_entrada));
            addCell(row, formatDate(reservation.fecha_salida));
            addCell(row, reservation.estado);
            addCell(row, formatAmount(reservation.importe));

            const detailCell = document.createElement('td');
            const link = document.createElement('a');

            link.href = `/reservations/${reservation.id}`;
            link.textContent = 'View details';
            
            detailCell.appendChild(link);
            row.appendChild(detailCell);

            const actionCell = document.createElement('td');

            if (reservation.estado !== 'cancelada') {
                const cancelButton = document.createElement('button');

                cancelButton.type = 'button';
                cancelButton.className = 'cancel-reservation';
                cancelButton.dataset.id = reservation.id;
                cancelButton.textContent = 'Cancel reservation';

                actionCell.appendChild(cancelButton);
            } else {
                actionCell.textContent = 'Cancelled';
            }

            row.appendChild(actionCell);
            tableBody.appendChild(row);

        });
    }

    async function loadReservations() {
        const params = new URLSearchParams(new FormData(form));

        for (const [key, value] of params) {
            if (value.trim() === '') {
                params.delete(key);
            }
        }

        const query = params.toString();
        const url = query ? `/api/reservations?${query}` : '/api/reservations';

        message.textContent = 'Loading reservations...';

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Could not load reservations.');
            }

            const result = await response.json();

            renderReservations(result.data);
            message.textContent = `${result.data.length} reservations found.`;
        } catch (error) {
            message.textContent = error.message;
        }
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        loadReservations();
    });

    form.addEventListener('reset', () => {
        setTimeout(loadReservations, 0);
    });

    
    if (createForm && createMessage) {
        createForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const data = Object.fromEntries(new FormData(createForm));

            if (data.check_out <= data.check_in) {
                createMessage.textContent = 'Check-out must be after check-in.';
                return;
            }

            createMessage.textContent = 'Creating reservation...';

            const submitButton = createForm.querySelector('button[type="submit"]');
            submitButton.disabled = true;

            try {
                const response = await fetch('/api/reservations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    throw new Error('Could not create reservation.');
                }

                createMessage.textContent = 'Reservation created successfully.';
                createForm.reset();

                await loadReservations();
            } catch (error) {
                createMessage.textContent = error.message;
            } finally {
                submitButton.disabled = false;
            }
        });
    }

    
    tableBody.addEventListener('click', async (event) => {
        const button = event.target.closest('.cancel-reservation');

        if (!button) {
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

            await loadReservations();
            message.textContent = 'Reservation cancelled successfully.';
        } catch (error) {
            message.textContent = error.message;
            button.disabled = false;
        }
    });

});
