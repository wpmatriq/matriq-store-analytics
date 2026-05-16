/**
 * Reducer returning the viewport state, as keys of breakpoint queries with
 * boolean value representing whether query is matched.
 *
 * @param {Object} state  Current state.
 * @param {Object} action Dispatched action.
 *
 * @return {Object} Updated state.
 */

import { dispatch } from '@wordpress/data';

const DEFAULT_STATE = {
	admin_url: matriqMSAData?.ajax_url || '',
	groups_count: '',
	isSaving: false,
	isProcessing: false,
	initialStateSetFlag: false,
	settingsSavedNotification: '',
	onboardingData: {
		content_cpt: 'on',
		topic_cpt: 'on',
		subscribe_to_newsletter: 'on',
		share_non_sensitive_data: 'on',
		hidden_community: false,
		user_email: matriqMSAData?.email || '',
	},
	routes: {
		home: {
			activeTab: 'home',
			section: 'home',
		},
		settings: {
			activeTab: 'settings',
			section: [ 'customizer' ],
		},
	},
	showTopBar: false,
	activeTab: '',
	activeSection: '',
	activeSpace: {},
	activeGroup: {},
	settings: matriqMSAData.settings || [],
	addSpaceModal: {
		open: false,
		category: 'create', // groupID
		item_emoji: '',
		hidden_space: false,
	},
	addGroupModal: {
		open: false,
		groupName: '',
		hide_label: false,
		homegrid_spaces: [],
		homegrid_spaces_count: 3,
	},
	notice: {
		...( matriqMSAData?.notice || [] ),
	},
	iconPickerModal: false,
	loading: false,
	saveDisabled: true,
	users: [],
};

const reducer = ( state = DEFAULT_STATE, action ) => {
	const actionType = wp.hooks.applyFilters(
		'matriq_msa_dashboard/data_reducer_action',
		action.type
	);

	if ( typeof state.onboardingData === 'undefined' ) {
		state.onboardingData = DEFAULT_STATE.onboardingData;
	}

	switch ( actionType ) {
		case 'SET_LOADING':
			return {
				...state,
				loading: action.payload,
			};
		case 'UPDATE_NOTICE':
			return {
				...state,
				notice: {
					...state.notice,
					[ action.payload.type ]: action.payload.value,
				},
			};
		case 'SET_SHOW_TOP_BAR':
			return {
				...state,
				showTopBar: action.payload,
			};
		case 'SET_SAVE_DISABLED':
			return {
				...state,
				saveDisabled: action.payload,
			};

		case 'NAVIGATE':
			const url = new URL( window.location.href );
			dispatch( { type: 'SET_LOADING', payload: true } );
			const { page, tab, section, ...extraParams } = action.payload || {};

			const orderedParams = [
				[ 'page', page || matriqMSAData.home_slug ],
				[ 'tab', tab || 'home' ],
				...( section
					? [ [ 'section', section ] ]
					: tab === 'settings'
						? [ [ 'section', 'general' ] ]
						: [] ),
				...Object.entries( extraParams ).filter(
					( [ key, value ] ) => value !== null // eslint-disable-line no-unused-vars
				),
			];

			const queryString = orderedParams
				.map(
					( [ key, value ] ) =>
						`${ encodeURIComponent( key ) }=${ encodeURIComponent(
							value
						) }`
				)
				.join( '&' );

			const newUrl = `${ url.origin }${ url.pathname }?${ queryString }`;
			if ( window.location.search !== `?${ queryString }` ) {
				window.history.pushState( {}, '', newUrl );
			}
			state = {
				...state,
				showTopBar: true,
			};
			dispatch( { type: 'SET_LOADING', payload: false } );
			return {
				...state,
				activeTab:
					orderedParams.find( ( [ key ] ) => key === 'tab' )?.[ 1 ] ||
					state.activeTab,
				activeSection:
					orderedParams.find(
						( [ key ] ) => key === 'section'
					)?.[ 1 ] || '',
			};
		case 'UPDATE_ADD_SPACE_MODAL':
			return {
				...state,
				addSpaceModal: {
					...state.addSpaceModal,
					...action.payload,
				},
			};
		case 'RESET_ADD_SPACE_MODAL':
			return {
				...state,
				addSpaceModal: {
					open: false,
					hidden_space: false,
				},
			};
		case 'UPDATE_ADD_GROUP_MODAL':
			return {
				...state,
				addGroupModal: {
					...state.addGroupModal,
					...action.payload,
				},
			};
		case 'UPDATE_ACTIVE_TAB':
			return {
				...state,
				activeTab: action.payload,
			};
		case 'UPDATE_ACTIVE_SECTION':
			return {
				...state,
				activeSection: action.payload,
			};
		case 'UPDATE_ACTIVE_SPACE':
			return {
				...state,
				activeSpace: {
					...state.activeSpace,
					...action.payload,
				},
				saveDisabled: action?.payload?.saveDisabled || false,
			};
		case 'RESET_ACTIVE_SPACE':
			return {
				...state,
				activeSpace: {},
				saveDisabled: false,
			};
		case 'UPDATE_ACTIVE_GROUP':
			return {
				...state,
				activeGroup: {
					...state.activeGroup,
					...action.payload,
				},
			};
		case 'UPDATE_SETTINGS':
			return {
				...state,
				settings: {
					...state.settings,
					...action.payload,
				},
			};
		case 'UPDATE_STORE_DATA':
			return {
				...state,
				...action.payload,
				onboardingData: {
					...state.onboardingData,
					...action.payload.onboardingData,
				},
			};
		case 'UPDATE_INITIAL_STATE':
			return {
				...action.payload,
			};
		case 'UPDATE_INITIAL_STATE_FLAG':
			return {
				...state,
				initialStateSetFlag: action.payload,
			};
		case 'UPDATE_SETTINGS_SAVED_NOTIFICATION':
			return {
				...state,
				settingsSavedNotification: action.payload,
			};
		case 'UPDATE_SETTINGS_ACTIVE_NAVIGATION_TAB':
			return {
				...state,
				activeSettingsNavigationTab: action.payload,
			};
		case 'UPDATE_PRIMARY_COLOR':
			return {
				...state,
				primaryColor: action.payload,
			};
		case 'UPDATE_HEADING_COLOR':
			return {
				...state,
				headingColor: action.payload,
			};
		case 'UPDATE_SECONDARY_COLOR':
			return {
				...state,
				textColor: action.payload,
			};
		case 'UPDATE_LINK_COLOR':
			return {
				...state,
				linkColor: action.payload,
			};
		case 'UPDATE_LINK_ACTIVE_COLOR':
			return {
				...state,
				linkActiveColor: action.payload,
			};
		case 'UPDATE_SELECTION_COLOR':
			return {
				...state,
				selectionColor: action.payload,
			};
		case 'UPDATE_ONBOARDING_DATA':
			return {
				...state,
				onboardingData: {
					...state.onboardingData,
					...action.payload,
				},
			};
		default:
			return state;
	}
};

export default reducer;
