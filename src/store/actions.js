/**
 * Returns an action object used in signalling that viewport queries have been
 * updated. Values are specified as an object of breakpoint query keys where
 * value represents whether query matches.
 * Ignored from documentation as it is for internal use only.
 *
 * @param  portalData
 */

export function updateStoreData( portalData ) {
	return {
		type: 'UPDATE_STORE_DATA',
		payload: portalData,
	};
}

export function updateOnboardingData( onboardingData ) {
	return {
		type: 'UPDATE_ONBOARDING_DATA',
		payload: onboardingData,
	};
}

export function updateActiveSpace( activeSpace ) {
	return {
		type: 'UPDATE_ACTIVE_SPACE',
		payload: activeSpace,
	};
}

export function resetActiveSpace() {
	return {
		type: 'RESET_ACTIVE_SPACE',
	};
}

export function updateActiveGroup( activeGroup ) {
	return {
		type: 'UPDATE_ACTIVE_GROUP',
		payload: activeGroup,
	};
}

export function updateActiveTab( activeTab ) {
	return {
		type: 'UPDATE_ACTIVE_TAB',
		payload: activeTab,
	};
}

export function updateSettings( settings ) {
	return {
		type: 'UPDATE_SETTINGS',
		payload: settings,
	};
}
export function setShowTopBar( showTopBar ) {
	return {
		type: 'SET_SHOW_TOP_BAR',
		payload: showTopBar,
	};
}
export function setAddSpaceModal( addSpaceModal ) {
	return {
		type: 'UPDATE_ADD_SPACE_MODAL',
		payload: addSpaceModal,
	};
}
export function setAddGroupModal( addGroupModal ) {
	return {
		type: 'UPDATE_ADD_GROUP_MODAL',
		payload: addGroupModal,
	};
}

export function resetAppSpaceModal() {
	return {
		type: 'RESET_ADD_SPACE_MODAL',
	};
}

export function navigateTo( params ) {
	return {
		type: 'NAVIGATE',
		payload: params,
	};
}

export function setSpaceListingData( spaceListingData ) {
	return {
		type: 'UPDATE_SPACE_LISTING_DATA',
		payload: spaceListingData,
	};
}

export function deleteSpaceGroup( groupKey ) {
	return {
		type: 'DELETE_SPACE_GROUP',
		payload: groupKey,
	};
}
export function addSpaceInSpaceListing( { spaceData, groupKey } ) {
	return {
		type: 'ADD_SPACE_IN_SPACE_LISTING',
		payload: { spaceData, groupKey },
	};
}
export function deleteSpace( { spaceId, groupKey } ) {
	return {
		type: 'DELETE_SPACE',
		payload: { spaceId, groupKey },
	};
}
export function setActiveSection( activeSection ) {
	return {
		type: 'UPDATE_ACTIVE_SECTION',
		payload: activeSection,
	};
}
export function setIconPickerModal( iconPickerModal ) {
	return {
		type: 'UPDATE_ICON_PICKER_MODAL',
		payload: iconPickerModal,
	};
}
export function setNotice( notice ) {
	return {
		type: 'UPDATE_NOTICE',
		payload: notice,
	};
}
export function setLoading( loading ) {
	return {
		type: 'SET_LOADING',
		payload: loading,
	};
}
export function updateSaveDisabled( saveDisabled ) {
	return {
		type: 'SET_SAVE_DISABLED',
		payload: saveDisabled,
	};
}
export function updateCommunityPosts( communityPosts ) {
	return {
		type: 'UPDATE_COMMUNITY_POSTS',
		payload: communityPosts,
	};
}

export function updateUsers( users ) {
	return {
		type: 'UPDATE_USERS',
		payload: users,
	};
}
