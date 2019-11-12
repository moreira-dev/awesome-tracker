//  Import CSS.
import './editor.scss';
import './style.scss';

import { Component, render as wpRender } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { textDomain, currentRoutes } from '../../globals';
import RouteLine from '../../components/RouteLine';
import { apiSaveRoute, apiEditRoute, apiDeleteRoute } from '../../api';

class Routes extends Component {

	static defaultProps = {
	};

	state = {
		routes: {},
	};

	constructor( props ) {
		super( props );

		this.addNewRoute = this.addNewRoute.bind( this );
		this.handleInputChange = this.handleInputChange.bind( this );
		this.saveRoute = this.saveRoute.bind( this );
		this.onDeleteRoute = this.onDeleteRoute.bind( this );
		this.getObjectWithoutPropertiesOnTheRight = this.getObjectWithoutPropertiesOnTheRight.bind( this );
	}

	componentDidMount() {
		this.setState( {
			routes: currentRoutes,
		} );
	}

	addNewRoute( e ) {
		e.preventDefault();

		this.setState( prevState => {
			const tempID = 'newRoute-' + ( Object.keys( prevState.routes ).length + 1 );
			return ( {
				routes: {
					[ tempID ]: { ID: tempID },
					...prevState.routes,
				},
			} );
		} );
	}

	isNewRoute( $ID ) {
		return $ID.indexOf( 'newRoute-' ) === 0;
	}

	getObjectWithoutPropertiesOnTheRight( selectedRoute = {}, selectedProperty = '' ) {
		let routeToSlice = Object.keys( selectedRoute );
		let shallowRouteCopy = {};
		const indexOfCurrentProperty = routeToSlice.indexOf( selectedProperty );

		if ( indexOfCurrentProperty >= 0 ) {
			routeToSlice = routeToSlice.slice( 0, indexOfCurrentProperty );
			routeToSlice.forEach( index => {
				shallowRouteCopy[ index ] = selectedRoute[ index ];
			} );
		} else {
			shallowRouteCopy = { ...selectedRoute };
		}

		return shallowRouteCopy;
	}

	handleInputChange( event ) {
		const target = event.currentTarget;
		const value = target.value;
		const name = target.name;
		const id = target.dataset.routekey;

		this.setState( ( prevState ) => {
			return ( {
				routes: {
					...prevState.routes,
					[ id ]: {
						...this.getObjectWithoutPropertiesOnTheRight( prevState.routes[ id ], name ),
						[ name ]: value,
					},
				},
			} );
		} );
	}

	canSaveRoute( route ) {
		if ( route.apiArg === 'at_other' && ! route.at_other ) {
			return false;
		}

		return !! ( route.apiRoute && route.method && route.apiArg );
	}

	updateRouteID( route, newID ) {
		this.setState( ( prevState ) => {
			const newState = { routes: { ...prevState.routes } };

			delete newState.routes[ route.ID ]; //ID stands for the old ID

			newState.routes[ newID ] = { ...route, ID: newID };

			if ( route.at_other ) {
				newState.routes[ newID ].apiArg = route.at_other;
				newState.routes[ newID ].at_other = null;
			}

			return newState;
		} );
	}

	deleteRouteID( route ) {
		this.setState( ( prevState ) => {
			const newState = { routes: { ...prevState.routes } };

			delete newState.routes[ route.ID ];

			return newState;
		} );
	}

	saveRoute( route, e ) {
		e.preventDefault();

		if ( ! this.isNewRoute( route.ID ) ) {

			return apiEditRoute( route ).then( response => {
				this.updateRouteID( route, response.ID );
			} );

		}

		return apiSaveRoute( route ).then( response => {
			this.updateRouteID( route, response.ID );
		} );
	}

	onDeleteRoute( route, e ) {
		e.preventDefault();

		if ( this.isNewRoute( route.ID ) ) {
			this.deleteRouteID( route );
			return true;
		}

		return apiDeleteRoute( route ).then( () => {
			this.deleteRouteID( route );
		} );

	}

	render() {
		return (
			<div className="no-more-tables wrap">
				<div className="new">
					<h2>{ __( 'Awesome Tracker API Route Rules', textDomain ) }</h2>
					<p className="description">
						{ __( 'Here you can set the API calls you want to track', textDomain ) }
					</p>
					<Button className="new-route" isDefault isLarge onClick={ this.addNewRoute }>
						{ __( 'New Route Rule', textDomain ) }
					</Button>
				</div>
				<table className="at-table">
					<thead>
						<tr>
							<th className="icon-action"> </th>
							<th className="api-route">{ __( 'API Route', textDomain ) }</th>
							<th className="api-method">{ __( 'Api Method', textDomain ) }</th>
							<th className="api-argument">{ __( 'Api Argument', textDomain ) }</th>
							<th className="button-action"> </th>
						</tr>
					</thead>
					<tbody>
						{ Object.keys( this.state.routes ).map( ( routeKey ) => (
							<RouteLine
								id={ routeKey }
								key={ routeKey }
								route={ this.state.routes[ routeKey ] }
								onChange={ this.handleInputChange }
								canSave={ this.canSaveRoute( this.state.routes[ routeKey ] ) }
								onSubmit={ this.saveRoute }
								onDelete={ this.onDeleteRoute }
							/>
						) ) }
						{
							Object.keys( this.state.routes ).length === 0 ?
								<tr>
									<td />
									<td
										data-title={ __( 'API Route', textDomain ) }
										colSpan={ 3 }>{ __( 'No Rules Yet', textDomain ) }
									</td>
									<td />
								</tr> : null
						}
					</tbody>
				</table>
			</div>
		);
	}

}

wpRender(
	<Routes />,
	document.getElementById( 'at-routes' )
);
