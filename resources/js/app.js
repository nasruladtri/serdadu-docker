import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import intersect from '@alpinejs/intersect';

// Register Alpine.js plugins
Alpine.plugin(focus);
Alpine.plugin(intersect);

// Make Alpine globally available
window.Alpine = Alpine;

// Start Alpine.js
Alpine.start();
