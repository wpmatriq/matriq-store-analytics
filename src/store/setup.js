/**
 * WordPress dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

/**
 * Internal dependencies
 */
import reducer from '@Store/reducer';
import * as actions from '@Store/actions';
import * as selectors from '@Store/selectors';
import { STORE_NAME as storeName } from '@Store/constants';

/**
 * Store definition for the viewport namespace.
 *
 * @see https://github.com/WordPress/gutenberg/blob/HEAD/packages/data/README.md#createReduxStore
 *
 * @type {Object}
 */
export const store = createReduxStore( storeName, {
	reducer,
	actions,
	selectors,
} );

register( store );
