/**
 * Additional form fields for the Number field type.
 */
import React from 'react';

const NumberFields = ({register}) => {
	return (
		<div className="field">
			<legend>Number Type</legend>
			<fieldset>
				<input type="radio" id="integer" name="numberType" value="integer" ref={register} defaultChecked />
				<label className="radio" htmlFor="integer">Integer (1, 2, 3, 5, 8, 13, ...)</label><br/>
				<input type="radio" id="decimal" name="numberType" value="decimal" ref={register} />
				<label className="radio" htmlFor="decimal">Decimal (3.14159265389)</label>
			</fieldset>
		</div>
	);
};

export default NumberFields;
