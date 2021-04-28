import React from 'react';

export default function ActionButtons({ isEditMode }) {
	function clickHandler(e) {
		e.preventDefault();
		document.getElementById('publish').click();
	}

	return (
		<div className="flex-parent action-buttons">
			<div className="flex-grow"></div>
			<div>
				<button className="button button-primary button-large action-button" onClick={(e) => {
					clickHandler(e)
				}}>
					{isEditMode ? 'Update' : 'Publish'}
				</button>
			</div>
		</div>
	);
}
