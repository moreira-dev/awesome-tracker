import apiFetch from '@wordpress/api-fetch';
import { nameSpace } from './globals';

const customThen = response => (
	typeof response.payload !== 'undefined' ?
		response.payload :
		response
);

const customCatch = error => {
	if ( error.message !== 'undefined' ) {
		throw new Error( error.message );
	} else {
		throw new Error( 'Something went wrong' );
	}
};

export const apiSaveRoute = route => {
	return apiFetch( { path: nameSpace + '/route', method: 'POST', data: { route } } )
		.then( customThen )
		.catch( customCatch );
};

export const apiEditRoute = route => {
	return apiFetch( { path: nameSpace + '/route', method: 'PUT', data: { route } } )
		.then( customThen )
		.catch( customCatch );
};

export const apiDeleteRoute = route => {
	return apiFetch( { path: nameSpace + '/route', method: 'DELETE', data: { route } } )
		.then( customThen )
		.catch( customCatch );
};

export const apiSaveRecordDB = days => {
	return apiFetch( { path: nameSpace + '/options', method: 'POST', data: { recordsDB: days } } )
		.then( customThen )
		.catch( customCatch );
};

export const apiDeleteAllRecords = () => {
	return apiFetch( { path: nameSpace + '/options', method: 'POST', data: { deleteAllRecords: true } } )
		.then( customThen )
		.catch( customCatch );
};
