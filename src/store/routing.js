import { dispatch } from '@wordpress/data';
import { STORE_NAME } from './constants';

/**
 * Update the active tab in the store and notify listeners.
 *
 * @param {string} tab Tab slug.
 */
export const menuChange = ( tab ) => {
	dispatch( STORE_NAME ).updateActiveTab( tab );
	if ( wp?.hooks?.doAction ) {
		wp.hooks.doAction( 'wc_sma_menu_change', tab );
	}
};
