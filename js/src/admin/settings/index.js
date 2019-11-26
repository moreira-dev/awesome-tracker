//  Import CSS.
import './editor.scss';
//import './style.scss';

import { Component, render as wpRender } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import RecordsDB from '../../components/RecordsDB';
import { Button, Modal } from '@wordpress/components';
import { apiDeleteAllRecords } from '../../api';

class Settings extends Component {

	state = {
		modal: false,
	};

	_isMounted = true;

	constructor( props ) {
		super( props );

		this.handleDeleteRecords = this.handleDeleteRecords.bind( this );
		this.closeModal = this.closeModal.bind( this );
		this.deleteAllRecords = this.deleteAllRecords.bind( this );
	}

	componentWillUnmount() {
		this._isMounted = false;
	}

	closeModal() {
		this.setState( { modal: false } );
	}

	handleDeleteRecords( event ) {
		event.preventDefault();
		this.setState( {
			apiCall: false,
			modal: {
				title: __( 'Delete all tracked records', 'awesome-tracker-td' ),
				className: 'modal-error',
				text: __( 'WARNING! This will permanently delete all the tracked records thus far from your database.', 'awesome-tracker-td' ),
				onCancel: this.closeModal,
				onSuccess: this.deleteAllRecords,
				buttonSuccess: __( 'Delete all!', 'awesome-tracker-td' ),
			},
		} );
	}

	deleteAllRecords() {
		this.setState( { apiCall: true } );
		apiDeleteAllRecords( ).then( () => {
			if ( this._isMounted ) {
				this.closeModal();
				this.setState( { apiCall: false } );
			}
		} ).catch( () => {
			if ( this._isMounted ) {
				this.closeModal();
				this.setState( { apiCall: false } );
			}
		} );
	}

	render() {
		const isBusy = {
			isBusy: false,
		};

		if ( this.state.apiCall ) {
			isBusy.isBusy = true;
		}

		return (
			<div className="wrap">
				<h2>{ __( 'Awesome Tracker Settings', 'awesome-tracker-td' ) }</h2>
				<p className="description">
					{ __( 'Hey what happen if I change this option? ... ... *distant screaming* Bob! Why is the homepage showing a white screen?!', 'awesome-tracker-td' ) }
				</p>
				<div className="at-section">
					<RecordsDB />
				</div>
				<div className="at-section">
					<div className="at-danger-zone">
						<h3>{ __( 'Danger Zone!', 'awesome-tracker-td' ) }</h3>
						<Button isDefault isDestructive { ...isBusy } onClick={ this.handleDeleteRecords }>
							{ __( 'Delete all tracked records', 'awesome-tracker-td' ) }
						</Button>
					</div>
				</div>
				{
					this.state.modal ?
						<Modal
							title={ this.state.modal.title }
							isDismissable={ true }
							isDismissible={ true }
							className={ 'at-modal ' + this.state.modal.className }
							onRequestClose={ this.state.modal.onCancel }>
							<p>{ this.state.modal.text }</p>
							<Button isDefault { ...isBusy } onClick={ this.state.modal.onCancel }>
								{ __( 'Cancel', 'awesome-tracker-td' ) }
							</Button>
							<Button isPrimary { ...isBusy } onClick={ this.state.modal.onSuccess }>
								{ this.state.modal.buttonSuccess }
							</Button>
						</Modal> : null
				}
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
