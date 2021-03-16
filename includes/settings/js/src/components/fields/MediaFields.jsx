/**
 * Additional form fields for the Media field type.
 */
import React from 'react';

const MediaFields = ({register}) => {
	return (
		<div className="field">
			<legend>Upload Type</legend>
			<fieldset>
				<input type="radio" id="single" name="uploadType" value="single" ref={register} defaultChecked />
				<label className="radio" htmlFor="single">One file</label><br/>
				<input type="radio" id="multi" name="uploadType" value="multi" ref={register} />
				<label className="radio" htmlFor="multi">Many files</label>
			</fieldset>
		</div>
	);
};

export default MediaFields;
