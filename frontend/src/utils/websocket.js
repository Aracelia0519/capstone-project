import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// =========================================================================
// 🚀 DEPLOYMENT SWITCH: Comment / Uncomment the correct URL before building!
// =========================================================================

// 1. LOCAL DEVELOPMENT (Use this when coding on your laptop)
const authEndpointUrl = 'http://127.0.0.1:8000/api/broadcasting/auth';

// 2. HOSTINGER PRODUCTION (Uncomment this when ready to deploy)
// const authEndpointUrl = 'https://api.capstone001.com/api/broadcasting/auth';

// =========================================================================

const echo = new Echo({
    broadcaster: 'pusher',
    key: 'fade6ce6ed8705f2ace4',
    cluster: 'ap1',
    forceTLS: true,
    authEndpoint: authEndpointUrl,
    auth: {
        headers: {
            Authorization: `Bearer ${localStorage.getItem('auth_token')}`
        }
    }
});

export default echo;