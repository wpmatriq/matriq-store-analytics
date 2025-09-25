import { __ } from '@wordpress/i18n';

const Onboarding = () => {
	return (
		<>
			<div className="flex flex-col">
				{ __( 'Onboarding', 'suredash' ) }
			</div>
		</>
	);
};

export default Onboarding;
