import { __ } from '@wordpress/i18n';

const SelectApi = props => {
	let valueText = props.value;
	const options =
		Object.keys( props.options ).map( optionKey => {
			let optionText = props.indexAsText ? optionKey : props.options[ optionKey ];

			if ( props.useProperty ) {
				optionText = props.options[ optionKey ][ props.useProperty ];
			}

			if ( props.value === optionKey ) {
				valueText = optionText;
			}

			return (
				<option
					key={ optionKey }
					value={ optionKey } >
					{ optionText }
				</option>
			);
		} );

	if ( ! props.editable ) {
		return (
			<span className="select-api">
				{ valueText }
			</span>
		);
	}

	return (
		<select
			className={ props.className }
			name={ props.name }
			onChange={ props.onChange }
			value={ props.value }
			data-routekey={ props.id }>
			<option value="">{ props.title }</option>
			{ options }
		</select>
	);
};

SelectApi.defaultProps = {
	className: 'at_select',
	name: '',
	onChange: null,
	title: __( 'Select an option', 'awesome-tracker-td' ),
	indexAsText: false,
	useProperty: false,
	value: '',
	options: {},
};

export default SelectApi;
