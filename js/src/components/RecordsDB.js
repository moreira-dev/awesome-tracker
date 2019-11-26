import { Component } from '@wordpress/element';
import { Button, Modal, SelectControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { currentSettings } from '../globals';
import { apiSaveRecordDB } from '../api';
import ApiTick from '../components/ApiTick';

class RecordsDB extends Component {

	static defaultProps = {};

	state = {
		fields: {
			selectDaysRecordsDB: currentSettings.recordsDB,
		},
		daysRecordsDB: parseInt( currentSettings.recordsDB ),
		modal: false,
		savedSuccess: null,
	};

	prevDaysRecordsDB = parseInt( currentSettings.recordsDB );

	_isMounted = true;

	constructor( props ) {
		super( props );

		this.handleChangeRecordsDB = this.handleChangeRecordsDB.bind( this );
		this.handleChangeSelectRecordsDB = this.handleChangeSelectRecordsDB.bind( this );
		this.handleSaveRecordsDB = this.handleSaveRecordsDB.bind( this );
		this.saveFieldAPI = this.saveFieldAPI.bind( this );
		this.closeModal = this.closeModal.bind( this );
	}

	componentWillUnmount() {
		this._isMounted = false;
	}

	focusInCurrentTarget( { relatedTarget, currentTarget } ) {
		if ( relatedTarget === null ) {
			return false;
		}

		let node = relatedTarget.parentNode;

		while ( node !== null ) {
			if ( node === currentTarget ) {
				return true;
			}
			node = node.parentNode;
		}

		return false;
	}

	closeModal() {
		this.setState( { modal: false } );
	}

	saveFieldAPI() {
		const daysRecordsDB = this.state.daysRecordsDB;
		apiSaveRecordDB( daysRecordsDB ).then( () => {
			if ( this._isMounted ) {
				this.closeModal();
				this.setState( { savedSuccess: true } );
				this.prevDaysRecordsDB = daysRecordsDB;
			}
		} ).catch( () => {
			if ( this._isMounted ) {
				this.closeModal();
				this.setState( { savedSuccess: false } );
			}
		} );
	}

	handleChangeSelectRecordsDB( days ) {
		this.setState(
			( prevState ) => (
				{
					savedSuccess: null,
					fields: { ...prevState.fields, selectDaysRecordsDB: days },
					daysRecordsDB: isNaN( days ) ? prevState.daysRecordsDB : parseInt( days ),
				}
			)
		);
	}

	handleChangeRecordsDB( days ) {
		this.setState( {
			savedSuccess: null,
			daysRecordsDB: parseInt( days ),
		} );
	}

	handleSaveRecordsDB( event ) {
		if ( ! this.focusInCurrentTarget( event ) ) {
			if ( this.prevDaysRecordsDB === this.state.daysRecordsDB ) {
				return true;
			}
			if ( this.state.daysRecordsDB === 0 ) {
				this.saveFieldAPI();
			} else if ( ! isNaN( this.state.daysRecordsDB ) ) {
				this.setState( { modal: true } );
			}
		}
	}

	renderModalMaybe() {
		if ( this.state.modal ) {
			return (
				<Modal
					title={ __( 'Update Database?', 'awesome-tracker-td' ) }
					isDismissable={ false }
					isDismissible={ false }
					className="at-modal"
					onRequestClose={ this.closeModal }>
					<p>{
						__(
							'WARNING! this change will remove records older than %d days from the database',
							'awesome-tracker-td'
						).replace( '%d', this.state.daysRecordsDB )
					}</p>
					<Button isDefault onClick={ this.closeModal }>
						{ __( 'Cancel', 'awesome-tracker-td' ) }
					</Button>
					<Button isPrimary onClick={ this.saveFieldAPI }>
						{ __( 'Remove', 'awesome-tracker-td' ) }
					</Button>
				</Modal>
			);
		}

		return null;
	}

	renderApiTickMaybe() {
		if ( this.state.savedSuccess === null ) {
			return null;
		}

		return (
			<ApiTick success={ this.state.savedSuccess } />
		);
	}

	render() {
		const selectOptions = [
			{ label: __( '...EVER', 'awesome-tracker-td' ), value: '0' },
			{ label: __( '1 Week', 'awesome-tracker-td' ), value: '7' },
			{ label: __( '1 Month', 'awesome-tracker-td' ), value: '30' },
			{ label: __( '1 Year', 'awesome-tracker-td' ), value: '365' },
		];

		if ( ! isNaN( this.state.fields.selectDaysRecordsDB ) ) {
			const checkValueExists = obj => obj.value === this.state.fields.selectDaysRecordsDB;
			if ( ! selectOptions.some( checkValueExists ) ) {
				selectOptions.push(
					{
						label: __( '%d Days', 'awesome-tracker-td' ).replace( '%d', this.state.fields.selectDaysRecordsDB ),
						value: this.state.fields.selectDaysRecordsDB,
					}
				);
			}

		}

		selectOptions.push( { label: __( 'Set a custom value', 'awesome-tracker-td' ), value: 'custom' } );

		return (
			<div
				className="at-field-container"
				onBlur={ this.handleSaveRecordsDB }
			>
				<SelectControl
					label={ __( 'Keep records in database for...', 'awesome-tracker-td' ) }
					value={ this.state.fields.selectDaysRecordsDB }
					className="at-field"
					options={ selectOptions }
					onChange={ this.handleChangeSelectRecordsDB }
				/>
				{ this.state.fields.selectDaysRecordsDB === 'custom' ?
					<TextControl
						type="number"
						className="at-field"
						label={ __( 'Set number of days to keep records for', 'awesome-tracker-td' ) }
						value={ this.state.daysRecordsDB }
						onChange={ this.handleChangeRecordsDB }
					/> : null
				}
				{ this.renderApiTickMaybe() }
				{ this.renderModalMaybe() }
			</div>
		);
	}
}

export default RecordsDB;
