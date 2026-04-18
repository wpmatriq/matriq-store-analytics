/**
 * Sales Pulse v2 — Dashboard entry point.
 */
import { mountComponent } from '@Utils/mountComponent';
import App from './App';

// Import main CSS.
import './MainApp.scss';

// Mount the app.
if ( document.getElementById( 'wc-sma-main-page--wrapper' ) ) {
	mountComponent( '#wc-sma-main-page--wrapper', <App /> );
}
