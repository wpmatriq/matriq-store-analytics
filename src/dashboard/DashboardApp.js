import { mountComponent } from '@Utils/mountComponent';
import setInitialState from '@Store/setInitialState';
import Wrapper from './Wrapper';

// Import main CSS.
import './MainApp.scss';

// Set initial store setup according to the app.
setInitialState();

// Mount the main app component.
if ( document.getElementById( 'wc-sma-main-page--wrapper' ) ) {
	mountComponent( '#wc-sma-main-page--wrapper', <Wrapper /> );
}
