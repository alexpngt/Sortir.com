import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
// Import du CSS du framework Bootstrap :
import './vendor/bootstrap/dist/css/bootstrap.min.css';

// Import du JS du framework Bootstrap :
import './vendor/bootstrap/bootstrap.index.js';

// Nos fichiers css / js
import './styles/app.css'
import './styles/table.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
