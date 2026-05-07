import { __, sprintf } from '@wordpress/i18n';
import wpApiFetch from '@wordpress/api-fetch';

export const activatePlugin = async ( plugin ) => {
	try {
		const formData = new FormData();
		formData.append( 'plugin_init', plugin?.init );
		formData.append( 'plugin_slug', plugin?.slug );
		const response = await wpApiFetch( {
			path: '/wc-sma/v1/activate-plugin',
			method: 'POST',
			body: formData,
		} );

		if ( response.success ) {
			console.log(
				sprintf(
					/* translators: %s: Plugin name */
					__( '%s Plugin Activated Successfully..!', 'sales-pulse' ),
					plugin?.name
				)
			);
			return true;
		}
	} catch ( error ) {
		console.error( `Failed to activate ${ plugin?.name }:`, error );
		return false;
	}
};

export const installPlugins = async ( pluginInstallList ) => {
	return new Promise( ( resolve ) => {
		if ( ! pluginInstallList?.length ) {
			resolve();
			return;
		}

		const installPromises = pluginInstallList.map( ( plugin ) => {
			return new Promise( ( resolveInstall, rejectInstall ) => {
				try {
					console.log(
						sprintf(
							/* translators: %s: Plugin name */
							__(
								'Installing %s plugin. Please wait..',
								'sales-pulse'
							),
							plugin?.name
						)
					);
					wp.updates.queue.push( {
						action: 'install-plugin',
						data: {
							slug: plugin?.slug,
							init: plugin?.init,
							success: async () => {
								console.log(
									sprintf(
										/* translators: %s: Plugin name */
										__(
											`%s plugin Installed Successfully..`,
											'sales-pulse'
										),
										plugin?.name
									)
								);

								try {
									await activatePlugin( plugin );
									resolveInstall();
								} catch ( activationError ) {
									console.error(
										`Failed to activate ${ plugin?.name }:`,
										activationError
									);
									rejectInstall( activationError );
								}
							},
							error: ( error ) => {
								console.error(
									`Failed to install ${ plugin?.name }:`,
									error
								);
								rejectInstall( error );
							},
						},
					} );

					wp.updates.queueChecker();
				} catch ( error ) {
					console.error(
						`Error installing ${ plugin?.name }:`,
						error
					);
					rejectInstall( error );
				}
			} );
		} );

		// ✅ Wait for all plugins to install & activate before resolving
		Promise.allSettled( installPromises ).then( () => resolve() );
	} );
};
