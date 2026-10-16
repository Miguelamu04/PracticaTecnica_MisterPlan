
document.addEventListener('DOMContentLoaded', () => {
    const createForm = document.getElementById('create-reservation-form');
    const createMessage = document.getElementById('create-message');

    if (!createForm || !createMessage) {
        return;
    }

    createForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const data = Object.fromEntries(new FormData(createForm));

        if (data.check_out <= data.check_in) {
            createMessage.textContent = 'Check-out must be after check-in.';
            return;
        }

        const submitButton = createForm.querySelector('button[type="submit"]');

        submitButton.disabled = true;
        createMessage.textContent = 'Creating reservation...';

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

        } catch (error) {
            createMessage.textContent = error.message;
        } finally {
            submitButton.disabled = false;
        }
    });
});
