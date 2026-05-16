/**
 * Runtime "is Pro active?" probe.
 *
 * Pro registers tabs (Analytics) via the public extension API and slot
 * overlays (Copilot chat drawer) via `registerSlot`. Either presence is a
 * reliable proxy: the free shell can't see Pro's PHP-side licence state,
 * but the simple presence of any registered Pro tab is enough to gate the
 * upgrade trigger so we don't double-render with Pro's chat chip.
 *
 * @return {boolean} True when the Pro bundle has registered with the shell.
 */
export function isProActive() {
	if ( typeof window === 'undefined' ) {
		return false;
	}
	const tabs = window.matriqMSA?.tabs || {};
	if ( Object.keys( tabs ).length > 0 ) {
		return true;
	}
	const slots = window.matriqMSA?.slots?.[ 'app-overlay' ] || [];
	return slots.some( ( s ) => s?.id === 'copilot-chat-drawer' );
}
