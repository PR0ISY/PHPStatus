import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        fetch('/api/save-user-timezone', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ timezone: userTimezone }),
        })
            .then(response => {
                if (response.ok) {
                    console.log('Timezone sent successfully.');
                } else {
                    console.error('Failed to send timezone.');
                }
            })
            .catch(error => {
                console.error('Error sending timezone:', error);
            });
    }
}