import { Component } from '@wordpress/element';
import { Button, IconButton, Spinner, Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { apiRoutes, textDomain } from '../../globals';
import SelectApi from './SelectApi';

class RouteLine extends Component {
	static defaultProps = {
		route: {
			ID: null,
			apiRoute: null,
			method: null,
			apiArg: null,
			at_other: null,
		},
		onChange: null,
		onSubmit: null,
		canSave: false,
	};

	state = {
		confirmDelete: false,
		editable: false,
		deprecated: false,
		apiCalling: false,
		error: null,
	};

	_isMounted = true;

	constructor( props ) {
		super( props );

		if ( this.isNewRoute() ) {
			this.state.editable = true;
		} else if ( this.isDeprecatedRoute() ) {
			this.state.deprecated = true;
			this.state.editable = false;
		}

		this.onSubmit = this.onSubmit.bind( this );
		this.onEdit = this.onEdit.bind( this );
		this.onDelete = this.onDelete.bind( this );
		this.closeModal = this.closeModal.bind( this );
		this.closeErrorModal = this.closeErrorModal.bind( this );
	}

	componentWillUnmount() {
		this._isMounted = false;
	}

	isNewRoute() {
		return this.props.id.indexOf( 'newRoute-' ) === 0;
	}

	isDeprecatedRoute() {
		return typeof apiRoutes[ this.props.route.apiRoute ] === 'undefined' ||
				typeof apiRoutes[ this.props.route.apiRoute ][ this.props.route.method ] === 'undefined';
	}

	closeModal() {
		this.setState( { confirmDelete: false } );
	}

	closeErrorModal() {
		this.setState( { error: null } );
	}

	onSubmit( e ) {
		this.setState( { apiCalling: true } );
		const submiting = this.props.onSubmit( this.props.route, e );
		const newState = { editable: false, apiCalling: false };
		if ( typeof submiting.then === 'function' ) {
			submiting.then( () => {
				if ( this._isMounted ) {
					this.setState( newState );
				}
			} ).catch( error => {
				if ( this._isMounted ) {
					this.setState( { apiCalling: false, error } );
				}
			} );
		} else if ( this._isMounted ) {
			this.setState( newState );
		}
	}

	onEdit( e ) {
		e.preventDefault();
		this.setState( { editable: true } );
		const el = document.querySelector( ':focus' );
		if ( el ) {
			el.blur();
		}
	}

	onDelete( e ) {
		e.preventDefault();

		if ( ! this.state.confirmDelete && ! this.isNewRoute() ) {
			this.setState( { confirmDelete: true } );
			return false;
		}

		const newState = { apiCalling: true };
		if ( this.state.confirmDelete ) {
			newState.confirmDelete = false;
		}

		this.setState( newState );

		const deleting = this.props.onDelete( this.props.route, e );
		if ( typeof deleting.then === 'function' ) {
			deleting.then( () => {
				if ( this._isMounted ) {
					this.setState( { apiCalling: false } );
				}

			} ).catch( error => {
				if ( this._isMounted ) {
					this.setState( { apiCalling: false, error } );
				}
			} );
		} else if ( this._isMounted ) {
			this.setState( { apiCalling: false } );
		}
	}

	addArgumentExtraOptions( args ) {
		return {
			0: __( 'NO ID', textDomain ),
			...args,
			at_other: __( 'Other Argument', textDomain ),
		};
	}

	render() {

		const classNames = [];

		if ( this.state.deprecated ) {
			classNames.push( 'deprecated' );
		}

		if ( this.state.apiCalling ) {
			classNames.push( 'apiCalling' );
		}

		return (
			<tr className={ classNames.join( ' ' ) }>
				<td className="icon-action">
					{
						this.state.editable || this.state.deprecated ?
							<IconButton
								icon="trash"
								className="delete-button"
								label={ __( 'Delete', textDomain ) }
								onClick={ this.onDelete }
							/> :

							<IconButton
								icon="edit"
								className="edit-button"
								label={ __( 'Edit', textDomain ) }
								onClick={ this.onEdit }
							/>
					}
				</td>
				<td
					data-title={ __( 'API Route', textDomain ) }
					className="api-route">
					{
						this.state.deprecated ?
							<span className="deprecated-alert">{ __( 'This Route or method does not exist anymore', textDomain ) }</span> :
							null
					}
					{
						this.state.apiCalling ?
							<Spinner /> :
							null
					}
					{
						this.state.error ?
							<Modal
								title={ __( 'Something went wrong', textDomain ) }
								isDismissable={ true }
								isDismissible={ true }
								className="at-modal modal-error"
								onRequestClose={ this.closeErrorModal }>
								<p className="errortext">{ this.state.error.message }</p>
								<p>
									Route: { this.props.route.apiRoute } <br />
									API Method: { this.props.route.method }
								</p>
								<Button isDefault onClick={ this.closeErrorModal }>
									{ __( 'Dammit!', textDomain ) }
								</Button>
							</Modal> :
							null
					}
					{
						this.state.confirmDelete ?
							<Modal
								title={ __( 'Remove line?', textDomain ) }
								isDismissable={ false }
								isDismissible={ false }
								className="at-modal"
								onRequestClose={ this.closeModal }>
								<Button isDefault onClick={ this.closeModal }>
									{ __( 'Cancel', textDomain ) }
								</Button>
								<Button isPrimary onClick={ this.onDelete }>
									{ __( 'Remove', textDomain ) }
								</Button>
							</Modal> :
							null
					}
					<SelectApi
						id={ this.props.id }
						title={ __( 'Choose a REST API Route', textDomain ) }
						onChange={ this.props.onChange }
						name="apiRoute"
						options={ apiRoutes }
						indexAsText
						editable={ this.state.editable }
						value={ this.props.route.apiRoute } />
				</td>

				<td
					data-title={ __( 'API Method', textDomain ) }
					className="api-method">
					{ this.props.route.apiRoute ?

						<SelectApi
							id={ this.props.id }
							title={ __( 'Choose a REST API Method', textDomain ) }
							onChange={ this.props.onChange }
							name="method"
							options={ this.state.deprecated ? [] : apiRoutes[ this.props.route.apiRoute ] }
							useProperty={ 'methods' }
							editable={ this.state.editable }
							value={ this.props.route.method } /> :
						null
					}
				</td>
				<td
					data-title={ __( 'API Argument', textDomain ) }
					className="api-argument">
					{ this.props.route.method ?
						<SelectApi
							id={ this.props.id }
							title={ __( 'Choose argument to read as Post ID', textDomain ) }
							onChange={ this.props.onChange }
							name="apiArg"
							editable={ this.state.editable }
							options={ this.addArgumentExtraOptions(
								this.state.deprecated ? [] :
									apiRoutes
										[ this.props.route.apiRoute ]
										[ this.props.route.method ].args )
							}
							value={ this.props.route.apiArg } /> :
						null
					}
					{
						this.props.route.apiArg && this.props.route.apiArg === 'at_other' ?
							<input
								data-routekey={ this.props.id }
								type="text"
								name="at_other"
								className="at_input"
								placeholder={ __( 'What other argument to read?', textDomain ) }
								onChange={ this.props.onChange }
								value={ this.props.route.at_other || '' } /> :
							null
					}
				</td>
				<td className="button-action">
					{
						this.state.editable ?
							<Button
								isLarge
								onClick={ this.onSubmit }
								{ ...this.props.canSave ? { isPrimary: true } : { disabled: true, isDefault: true } }>
								{ __( 'Save Route', textDomain ) }
							</Button>	:
							null
					}

				</td>
			</tr>
		);
	}
}

export default RouteLine;
