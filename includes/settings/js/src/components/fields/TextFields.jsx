/**
 * Additional form fields for the Text field type.
 */
import React from 'react';

const TextFields = ({register}) => {
	return (
		<div className="field">
			<legend>Text Length</legend>
			<fieldset>
				<input type="radio" id="short" name="textLength" value="short" ref={register} defaultChecked />
				<label className="radio" htmlFor="short">Short text (maximum 50 characters)</label><br/>
				<input type="radio" id="long" name="textLength" value="long" ref={register} />
				<label className="radio" htmlFor="long">Long text (maximum 500 characters)</label>
			</fieldset>
		</div>
	);
};

export default TextFields;
