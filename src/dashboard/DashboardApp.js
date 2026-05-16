/**
 * Matriq Store Analytics v2 - Dashboard entry point.
 */
import { mountComponent } from '@Utils/mountComponent';
import App from './App';

// Import main CSS.
import './MainApp.scss';

// Mount the app.
if ( document.getElementById( 'matriq-msa-main-page--wrapper' ) ) {
	mountComponent( '#matriq-msa-main-page--wrapper', <App /> );
}
