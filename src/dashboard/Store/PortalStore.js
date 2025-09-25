import { create } from 'zustand';
import apiFetch from '@wordpress/api-fetch';
import { getSpaceGroupData, getSpaceData } from '@Hooks/useSpaceApi';
import { FileText } from 'lucide-react';

const useWcSmartStore = create( ( set, get ) => ( {
	// State.
	loading: false,
	error: null,
	currentItemData: null,
	displayPage: '',
	activeTab: 'home',
	selectedItemId: null,
	selectedItemType: null,
	iconLibrary: {},
	iconLibraryLoading: false,
	iconLibraryError: null,
	routes: {
		home: {
			activeTab: 'home',
			displayPage: 'home',
		},
		settings: {
			activeTab: 'settings',
			displayPage: [ 'customizer' ],
		},
	},

	postTypeIcons: {
		none: <FileText />,
	},

	// Setters
	setLoading: ( loading ) => set( { loading } ),
	setError: ( error ) => set( { error } ),
	setCurrentItemData: ( currentItemData ) => set( { currentItemData } ),
	setDisplayPage: ( displayPage ) => set( { displayPage } ),

	// Bulk setters
	resetState: () =>
		set( {
			loading: false,
			error: null,
			currentItemData: null,
			displayPage: 'spaceList',
			selectedItemId: null,
			selectedItemType: null,
		} ),

	updateMultipleStates: ( newStates ) =>
		set( ( state ) => ( {
			...state,
			...newStates,
		} ) ),

	// Actions
	fetchItemData: async ( itemId, isCategory = false ) => {
		set( { loading: true, error: null } );
		if ( isCategory ) {
			const groupData = await getSpaceGroupData( itemId );
			const existingGroupData = get().groups[ itemId ] || {};

			const updatedData = {
				...existingGroupData,
				...groupData,
				term_id: itemId,
				isCategory: true,
			};

			set( ( state ) => ( {
				groups: { ...state.groups, [ itemId ]: updatedData },
				currentItemData: updatedData,
				sidebarData: {
					...state.sidebarData,
					[ 'group-' + itemId ]: { ...updatedData },
				},
			} ) );
		} else {
			const spaceData = await getSpaceData( itemId );
			const existingSpaceData = get().spaces[ itemId ] || {};

			const updatedData = {
				...existingSpaceData,
				...spaceData,
				post_id: itemId,
				isCategory: false,
			};

			set( ( state ) => ( {
				spaces: { ...state.spaces, [ itemId ]: updatedData },
				currentItemData: updatedData,
				sidebarData: {
					...state.sidebarData,
					[ 'group-' + itemId ]: { ...updatedData },
				},
			} ) );
		}

		set( { loading: false } );
	},

	saveData: async ( itemId, data, isCategory = false ) => {
		set( { loading: true, error: null } );
		const formData = new FormData();

		formData.append( 'action', 'portal_update_a_space' );
		formData.append( 'post_id', itemId );
		formData.append( 'security', wc_sma_admin_data.update_nonce );

		const existingData = isCategory
			? get().groups[ itemId ]
			: get().spaces[ itemId ];
		const updatedData = { ...existingData, ...data };
		formData.append( 'formData', JSON.stringify( updatedData ) );

		try {
			const response = await apiFetch( {
				url: wc_sma_admin_data.ajax_url,
				method: 'POST',
				body: formData,
			} );

			if ( response.success ) {
				set( ( state ) => ( {
					spaces: { ...state.spaces, [ itemId ]: updatedData },
					currentItemData: updatedData,
				} ) );
				wc_sma_admin_data.spaces_meta_set[ itemId ] = updatedData;

				return response;
			}
		} catch ( err ) {
			set( { error: err.message } );
			throw err;
		} finally {
			set( { loading: false } );
		}
	},

	navigate: ( {
		page = wc_sma_admin_data.home_slug,
		tab = 'home',
		...params
	} ) => {
		if ( window.unsavedChanges ) {
			const shouldProceed = window.confirm(
				'You have unsaved changes. Do you want to proceed?'
			);
			if ( ! shouldProceed ) {
				return false;
			}
		}

		const url = new URL( window.location.href );
		// Manually constructing query string to preserve existing parameters and their order
		const orderedParams = [
			[ 'page', page ],
			[ 'tab', tab ],
			...Object.entries( params ).filter(
				( [ value ] ) => value !== null
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

		// Only update if URL actually changed
		if ( window.location.search !== newUrl ) {
			window.history.pushState( {}, '', newUrl );
			get().updateFromURL();
		}
	},

	updateFromURL: async () => {
		const urlParams = new URLSearchParams( window.location.search );
		const newState = {
			selectedItemId: null,
			selectedItemType: null,
			displayPage: null,
			activeTab: 'home',
		};

		const tabType = urlParams.get( 'tab' ) || 'home';

		newState.activeTab = get().routes[ tabType ].activeTab;
		for ( const params of get().routes[ tabType ].displayPage ) {
			if ( urlParams.get( params ) ) {
				newState.displayPage = params;
				newState.selectedItemId = urlParams.get( params );
				newState.selectedItemType = params;
				break;
			}
		}

		const oldState = get();

		// Check if values are actually different before updating.
		const isStateEqual =
			oldState.selectedItemId === newState.selectedItemId &&
			oldState.selectedItemType === newState.selectedItemType &&
			oldState.displayPage === newState.displayPage &&
			oldState.activeTab === newState.activeTab;

		if ( ! isStateEqual ) {
			set( newState );
		}
	},

	handleNavClick: ( e, navigationConfig ) => {
		if ( e.metaKey || e.ctrlKey ) {
			return;
		}
		e.preventDefault();
		get().navigate( navigationConfig );
	},
} ) );

export default useWcSmartStore;
