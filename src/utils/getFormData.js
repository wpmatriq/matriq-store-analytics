export const getFormData = ( formRef ) => {
	const formData = new FormData( formRef );
	const formObject = [];

	formData.forEach( ( value, key ) => {
		formObject[ key ] = value;
	} );

	return formObject;
};
