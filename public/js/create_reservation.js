
document.addEventListener('DOMContentLoaded', () => {
    const createForm = document.getElementById('create-reservation-form');
    const createMessage = document.getElementById('create-message');

    if (!createForm || !createMessage) {
        return;
    }

    function showNotification(text, type) {
        createMessage.textContent = text;
        createMessage.className = `notification notification--${type}`;
        createMessage.hidden = false;
    }

    let isSubmitting = false;

    createForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (isSubmitting) {
            return;
        }

        const data = Object.fromEntries(new FormData(createForm));
        const notes = data.notes || '';

        if (new TextEncoder().encode(notes).length > 65535) {
            showNotification('Las observaciones son demasiado largas.', 'error');
            return;
        }

        if (data.check_out <= data.check_in) {
            showNotification('La fecha de salida debe ser posterior a la fecha de entrada.', 'error');
            return;
        }

        const submitButton = createForm.querySelector('button[type="submit"]');

        isSubmitting = true;
        submitButton.disabled = true;
        showNotification('Creando reserva...', 'loading');

        try {
            const response = await fetch('/api/reservations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

                        if (!response.ok) {
                const fieldNames = {
                    guest_name: 'nombre del huésped',
                    guest_email: 'correo electrónico',
                    accommodation: 'alojamiento',
                    check_in: 'fecha de entrada',
                    check_out: 'fecha de salida',
                    amount: 'importe'
                };

                const errorMessages = {
                    'Invalid guest email': 'El correo electrónico no es válido.',
                    'Invalid reservation dates': 'Las fechas de la reserva no son válidas.',
                    'Invalid amount': 'El importe introducido no es válido.',
                    'Invalid notes': 'Las observaciones no son válidas.'
                };

                let errorMessage = errorMessages[result.error];

                if (!errorMessage && typeof result.error === 'string') {
                    const [errorType, field] = result.error.split(': ');

                    if (fieldNames[field]) {
                        if (errorType === 'Required field') {
                            errorMessage = `El campo ${fieldNames[field]} es obligatorio.`;
                        } else if (errorType === 'Invalid field') {
                            errorMessage = `El campo ${fieldNames[field]} no es válido.`;
                        } else if (errorType === 'Invalid date') {
                            errorMessage = `La ${fieldNames[field]} no es válida.`;
                        }
                    }
                }

                throw new Error(
                    errorMessage || 'No se ha podido crear la reserva. Revisa los datos introducidos.'
                );
            }

            showNotification('Reserva creada correctamente.', 'success');
            createForm.reset();

        } catch (error) {
            showNotification(error.message, 'error');
        } finally {
            isSubmitting = false;
            submitButton.disabled = false;
        }
    });
});
