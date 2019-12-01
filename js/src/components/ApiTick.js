const ApiTick = props => {
	const specificClass = props.success ? 'at-api-success' : 'at-api-fail';
	return (
		<span className={ 'at-api-tick ' + specificClass }>
			{
				props.success ?
					<span className="dashicons dashicons-yes"> </span> :
					<span className="dashicons dashicons-no-alt"> </span>
			}
		</span>
	);
};

export default ApiTick;
