import { Component } from '@wordpress/element';

class SingleTD extends Component {
	static defaultProps = {
		className: '',
		colSpan: 1,
		th: '',
	};

	render() {
		let th = null;
		if ( this.props.th ) {
			th = (
				<th>{ this.props.th }</th>
			);
		}
		return (
			<tr className={ this.props.className }>
				{ th }
				<td colSpan={ this.props.colSpan }>
					{ this.props.children }
				</td>
			</tr>
		);
	}
}

export default SingleTD;
