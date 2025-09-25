import React from 'react';
import { __ } from '@wordpress/i18n';

const SettingsPage = () => {
	return (
		<div className="suredash-home-page">
			<h1>{ __( 'Settings of WC Smart Analytics', 'suredash' ) }</h1>
		</div>
	);
};

export default SettingsPage;
