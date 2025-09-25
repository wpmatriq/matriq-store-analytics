/**
 * Returns true if the viewport matches the given query, or false otherwise.
 *
 * @param {Object} state Viewport state object.
 *
 *
 * @return {boolean} Whether viewport matches query.
 */
export function getState( state ) {
	return state;
}

export function getOnboardingData( state ) {
	return state.onboardingData || {};
}
export function getShowTopBar( state ) {
	return state.showTopBar || false;
}
export function getActiveSpace( state ) {
	return state.activeSpace || {};
}
export function getActiveGroup( state ) {
	return state.activeGroup || {};
}
export function getSaveDisabled( state ) {
	return state.saveDisabled || false;
}
export function getActiveTab( state ) {
	return state.activeTab;
}
export function getActiveSection( state ) {
	return state.activeSection || '';
}
export function getSpaceListingData( state ) {
	return state.spaceListingData || [];
}
export function getSettings( state ) {
	return state.settings || {};
}
export function getAddSpaceModal( state ) {
	return state.addSpaceModal || {};
}
export function getAddGroupModal( state ) {
	return state.addGroupModal || {};
}
export function getIconPickerModal( state ) {
	return state.iconPickerModal || {};
}
export function getNotice( state ) {
	return state.notice || {};
}
export function getLoading( state ) {
	return state.loading || false;
}
export function getCommunityPosts( state ) {
	return state.communityPosts || [];
}

export function getUsers( state ) {
	return state.users || [];
}
