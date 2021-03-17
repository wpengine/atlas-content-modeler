/**
 * Additional form fields for the Media field type.
 */
import React from 'react';

const MediaFields = ({register, data}) => {
	return (
		<div className="field">
			<legend>Upload Type</legend>
			<fieldset>
				<input
					type="radio"
					id="single"
					name="uploadType"
					value="single"
					ref={register}
					defaultChecked={
						data?.uploadType === "single" || typeof data?.uploadType === "undefined"
					}
				/>
				<label className="radio" htmlFor="single">
					One file
				</label>
				<br />
				<input
					type="radio"
					id="multi"
					name="uploadType"
					value="multi"
					ref={register}
					defaultChecked={data?.uploadType === "multi"}
				/>
				<label className="radio" htmlFor="multi">
					Many files
				</label>
			</fieldset>
		</div>
	);
};

export default MediaFields;
