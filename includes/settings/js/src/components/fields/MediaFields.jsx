/**
 * Additional form fields for the Media field type.
 */
import React from 'react';

const MediaFields = ({register, data, editing}) => {
	return (
		<div className={editing ? 'field read-only editing': 'field'}>
			<legend>Upload Type</legend>
			<fieldset>
				<div className="radio-row">
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
						<span>Select this if there is only one thing to store.<br/>For example, a single photo or one PDF file.</span>
					</label>
				</div>
				<div className="radio-row">
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
						<span>Select this if there are several things to store.<br/>For example, several photos or PDF files.</span>
					</label>
				</div>
			</fieldset>
		</div>
	);
};

export default MediaFields;
