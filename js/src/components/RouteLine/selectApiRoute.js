import { __ } from '@wordpress/i18n';
import { apiRoutes, textDomain } from '../../globals';

const SelectApiRoute = ( props ) => {
	return (
		<select
			className="at_select at_first"
			name={ props.name }
			onChange={ props.onChange }
			data-routekey={ props.id }>
			<option value="">{ __( 'Choose a REST API Route', textDomain ) }</option>
			{
				Object.keys( apiRoutes ).map( routePath => (
					<option
						key={ routePath }
						value={ routePath }
						{ ...props.value === routePath ? { selected: 'selected' } : null }
					>{ routePath }</option>
				) )
			}
		</select>
	);
};

export default SelectApiRoute;
