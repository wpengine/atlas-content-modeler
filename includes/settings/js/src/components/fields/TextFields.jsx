/**
 * Additional form fields for the Text field type.
 */
import React from 'react';

const TextFields = ({register, data, editing}) => {
	return (
		<div className={editing ? 'field read-only editing': 'field'}>
			<legend>Text Length</legend>
			<fieldset>
				<div className="radio-row">
					<input
						type="radio"
						id="short"
						name="textLength"
						value="short"
						ref={register}
						defaultChecked={
							data?.textLength === "short" || typeof data?.textLength === "undefined"
						}
						disabled={editing}
					/>
					<label className="radio" htmlFor="short">
						Short text
						<span>Maximum 50 characters</span>
					</label>
				</div>
				<div className="radio-row">
					<input
						type="radio"
						id="long"
						name="textLength"
						value="long"
						ref={register}
						defaultChecked={data?.textLength === "long"}
						disabled={editing}
					/>
					<label className="radio" htmlFor="long">
						Long text
						<span>Maximum 500 characters</span>
					</label>
				</div>
			</fieldset>
		</div>
	);
};

export default TextFields;
