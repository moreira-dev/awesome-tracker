//  Import CSS.
import './editor.scss';
//import './style.scss';

import { Component, render as wpRender } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { textDomain } from '../../globals';
import RecordsDB from '../../components/RecordsDB';

class Settings extends Component {

	render() {
		return (
			<div className="wrap">
				<h2>{ __( 'Awesome Tracker Settings', textDomain ) }</h2>
				<p className="description">
					{ __( 'Hey what happen if I change this option? ... ... *distant screaming* Bob! Why is the homepage showing a white screen?!', textDomain ) }
				</p>
				<div className="at-section">
					<RecordsDB />
				</div>
			</div>
		);
	}

}

const elementToRender = document.getElementById( 'at-settings' );
if ( elementToRender ) {
	wpRender(
		<Settings />,
		elementToRender
	);
}
