import { createRoot } from 'react-dom/client';

// Example of checking if a variable is a React component
export const isReactComponent = ( variable ) => {
	// Check by verifying the presence of the `$$typeof` property Symbol(react.element) in the variable.
	return variable && variable?.$$typeof === Symbol.for( 'react.element' );
};

export const mountComponent = ( selector, Component, timeout = 100 ) => {
	// Validate selector parameters
	if ( typeof selector !== 'string' || ! selector.trim() ) {
		// eslint-disable-next-line no-console
		console.error( 'Invalid selector provided.' );
		return;
	}

	// Validate Component parameters this should be react component
	if ( ! isReactComponent( Component ) ) {
		// eslint-disable-next-line no-console
		console.error( 'Invalid React component provided.' );
		return;
	}

	// Check if the target element exists in the DOM
	const targetElement = document.querySelector( selector );

	// Log an error if the target element is not found
	if ( ! targetElement ) {
		// eslint-disable-next-line no-console
		console.error( `Target element with selector '${ selector }' not found.` );
		return;
	}

	// Render the component after a timeout
	setTimeout( () => {
		const root = createRoot( targetElement );
		root.render( Component );
	}, timeout );
};
