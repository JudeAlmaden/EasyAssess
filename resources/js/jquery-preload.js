// jQuery Preload - Loads synchronously before Vite modules
// This ensures jQuery is available for inline scripts in blade templates

import $ from 'jquery';

// Immediately expose jQuery globally
window.$ = window.jQuery = $;

console.log('jQuery preloaded for blade templates');
