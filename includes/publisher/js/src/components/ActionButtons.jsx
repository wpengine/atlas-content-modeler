import React from 'react';

export default function ActionButtons() {
	function clickHandler(e) {
		e.preventDefault();
		document.getElementById('publish').click();
	}

	return (
		<div className="flex-parent">
			<div className="flex-grow"></div>
			<div style={{marginTop: '20px'}}>
				<button className="button button-primary button-large action-button" onClick={(e) => {
					clickHandler(e)
				}}>Update/Add</button>
			</div>
		</div>
	);
}
