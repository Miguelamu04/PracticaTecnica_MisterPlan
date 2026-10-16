
document.addEventListener('DOMContentLoaded', () => {
    const createForm = document.getElementById('create-reservation-form');
    const createMessage = document.getElementById('create-message');

    if (!createForm || !createMessage) {
        return;
    }
    let isSubmitting = false;

    createForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (isSubmitting) {
            return;
        }

        const data = Object.fromEntries(new FormData(createForm));

        if (data.check_out <= data.check_in) {
            createMessage.textContent = 'La fecha de salida debe ser posterior a la fecha de entrada.';
            return;
        }

        const submitButton = createForm.querySelector('button[type="submit"]');

        isSubmitting = true;
        submitButton.disabled = true;
        createMessage.textContent = 'Creando reserva...';

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
                const errorMessages = {
                    'Invalid guest email': 'El correo electrónico no es válido.',
                    'Invalid reservation dates': 'Las fechas de la reserva no son válidas.',
                    'Invalid amount': 'El importe introducido no es válido.',
                    'Invalid notes': 'Las observaciones no son válidas.'
                };

                const errorMessage = errorMessages[result.error]
                    || 'No se ha podido crear la reserva. Revisa los datos introducidos.';

                throw new Error(errorMessage);
            }

            createMessage.textContent = 'Reserva creada correctamente.';
            createForm.reset();

        } catch (error) {
            createMessage.textContent = error.message;
        } finally {
            isSubmitting = false;
            submitButton.disabled = false;
        }
    });
});
