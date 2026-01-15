// Blade Templates Entry Point
// This file bundles all dependencies needed for legacy blade pages

// Import jQuery and jQuery UI first
import $ from 'jquery';

// Immediately make jQuery globally available BEFORE anything else
window.$ = window.jQuery = $;

// Now import jQuery UI (it will find jQuery on window)
import 'jquery-ui/dist/jquery-ui.min.js';

// Import html2pdf
import html2pdf from 'html2pdf.js';

// Make html2pdf globally available
window.html2pdf = html2pdf;

// Import Fonts
import '@fontsource/roboto/400.css';
import '@fontsource/roboto/500.css';
import '@fontsource/roboto/700.css';
import '@fontsource/inter/400.css';
import '@fontsource/inter/500.css';
import '@fontsource/inter/600.css';

// Import CSS (includes Tailwind)
import '../css/app.css';

// Log to confirm blade.js has loaded
console.log('Blade assets loaded successfully');
