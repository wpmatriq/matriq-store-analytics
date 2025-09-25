import React from 'react';
import { __ } from '@wordpress/i18n';
import { CrownIcon } from 'lucide-react';

const HomePage = () => {
	return (
		<div className="suredash-home-page">
			<h1>{ __( 'Welcome to WC Smart Analytics', 'suredash' ) }</h1>
			<CrownIcon />
		</div>
	);
};

export default HomePage;
