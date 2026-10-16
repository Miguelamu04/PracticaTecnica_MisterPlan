
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('reservation-filters');
    const tableBody = document.getElementById('reservations-body');
    const message = document.getElementById('filter-message');

    if (!form || !tableBody || !message) {
        return;
    }

    function showNotification(text, type) {
        message.textContent = text;
        message.className = `notification notification--${type}`;
        message.hidden = false;
    }

    const pendingCancellations = new Set();

    let currentController = null;

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
            const cell = addCell(row, 'No se han encontrado reservas.');
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
            addCell(row, ({ pendiente: 'Pendiente', confirmada: 'Confirmada', cancelada: 'Cancelada' })[reservation.estado] ?? reservation.estado);
            addCell(row, formatAmount(reservation.importe));

            const detailCell = document.createElement('td');
            const link = document.createElement('a');

            link.href = `/reservations/${reservation.id}`;
            link.textContent = 'Ver detalles';
            
            detailCell.appendChild(link);
            row.appendChild(detailCell);

            const actionCell = document.createElement('td');

            if (reservation.estado === 'pendiente') {
                const confirmButton = document.createElement('button');

                confirmButton.type = 'button';
                confirmButton.className = 'confirm-reservation';
                confirmButton.dataset.id = reservation.id;
                confirmButton.textContent = 'Confirmar reserva';

                actionCell.appendChild(confirmButton);
            }

            if (reservation.estado !== 'cancelada') {
                const cancelButton = document.createElement('button');

                cancelButton.type = 'button';
                cancelButton.className = 'cancel-reservation';
                cancelButton.dataset.id = reservation.id;
                cancelButton.textContent = 'Cancelar reserva';

                actionCell.appendChild(cancelButton);
            } else {
                actionCell.textContent = 'Cancelada';
            }

            row.appendChild(actionCell);
            tableBody.appendChild(row);

        });
    }

    async function loadReservations() {
        const params = new URLSearchParams(new FormData(form));

        const startDate = params.get('start_date');
        const endDate = params.get('end_date');

        if (startDate && endDate && endDate < startDate) {
            showNotification('La fecha «Hasta» no puede ser anterior a «Desde».', 'error');
            return;
        }

        for (const [key, value] of params) {
            if (value.trim() === '') {
                params.delete(key);
            }
        }

        const query = params.toString();
        const url = query ? `/api/reservations?${query}` : '/api/reservations';

        message.textContent = 'Cargando reservas...';

        if (currentController) {
            currentController.abort();
        }

        const controller = new AbortController();
        currentController = controller;

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                },
                signal: controller.signal
            });

            if (!response.ok) {
                let errorMessage = 'No se han podido cargar las reservas.';

                if (response.status === 400) {
                    errorMessage = 'Los filtros introducidos no son válidos.';
                } else if (response.status >= 500) {
                    errorMessage = 'Error del servidor. Inténtalo de nuevo más tarde.';
                }

                throw new Error(errorMessage);
            }

            const result = await response.json();

            renderReservations(result.data);

            showNotification(
                result.data.length === 1
                    ? '1 reserva encontrada.'
                    : `${result.data.length} reservas encontradas.`,
                'info'
            );
        } catch (error) {
            if (error.name !== 'AbortError') {
                message.textContent = error.message;
            }
        } finally {
            if (currentController === controller) {
                currentController = null;
            }
        }
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        loadReservations();
    });

    form.addEventListener('reset', () => {
        setTimeout(loadReservations, 0);
    });

            tableBody.addEventListener('click', async (event) => {
        const button = event.target.closest('.confirm-reservation');

        if (!button || !tableBody.contains(button)) {
            return;
        }

        const reservationId = button.dataset.id;

        if (pendingCancellations.has(reservationId)) {
            return;
        }

        if (!window.confirm('¿Seguro que quieres confirmar esta reserva?')) {
            return;
        }

        pendingCancellations.add(reservationId);
        button.disabled = true;
        showNotification('Confirmando reserva...', 'loading');

        try {
            const response = await fetch(
                `/api/reservations/${reservationId}/status`,
                {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: 'confirmada' })
                }
            );

            if (!response.ok) {
                let errorMessage = 'No se ha podido confirmar la reserva.';

                if (response.status === 404) {
                    errorMessage = 'La reserva ya no existe.';
                } else if (response.status === 400) {
                    errorMessage = 'La solicitud de confirmación no es válida.';
                } else if (response.status >= 500) {
                    errorMessage = 'Error del servidor. Inténtalo de nuevo más tarde.';
                }

                throw new Error(errorMessage);
            }

            const row = button.closest('tr');

            if (row) {
                row.cells[4].textContent = 'Confirmada';
            }

            button.remove();
            showNotification('Reserva confirmada correctamente.', 'success');

        } catch (error) {
            showNotification(error.message, 'error');
        } finally {
            pendingCancellations.delete(reservationId);

            if (button.isConnected) {
                button.disabled = false;
            }
        }
    });

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
        showNotification('Cancelando reserva...', 'loading');

        try {
            const response = await fetch(
                `/api/reservations/${reservationId}/status`,
                {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: 'cancelada' })
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
                row.cells[4].textContent = 'Cancelada';
            }

            button.replaceWith(document.createTextNode('Cancelada'));
            showNotification('Reserva cancelada correctamente.', 'success');

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
