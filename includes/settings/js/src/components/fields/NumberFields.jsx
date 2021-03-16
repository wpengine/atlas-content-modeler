/**
 * Additional form fields for the Number field type.
 */
import React from 'react';

const NumberFields = ({register}) => {
	return (
		<div className="field">
			<legend>Upload Type</legend>
			<fieldset>
				<input type="radio" id="short" name="uploadType" value="short" ref={register} defaultChecked />
				<label className="radio" htmlFor="short">One file</label><br/>
				<input type="radio" id="long" name="uploadType" value="long" ref={register} />
				<label className="radio" htmlFor="long">Many files</label>
			</fieldset>
		</div>
	);
};

export default NumberFields;
