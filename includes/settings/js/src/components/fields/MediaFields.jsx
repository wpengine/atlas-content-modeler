/**
 * Additional form fields for the Media field type.
 */
import React from 'react';

const MediaFields = ({register, data, editing}) => {
	return (
		<div className={editing ? 'field read-only editing': 'field'}>
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
					disabled={editing}
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
					disabled={editing}
				/>
				<label className="radio" htmlFor="multi">
					Many files
				</label>
			</fieldset>
		</div>
	);
};

export default MediaFields;
