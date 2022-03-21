/**
 * Additional form fields for the Rich Text field type.
 */
import React, { useState } from "react";
import { __ } from "@wordpress/i18n";

const RichTextFields = ({ register, data, editing, fieldId }) => {
	const [repeatable, setRepeatable] = useState(
		data?.isRepeatableRichText === true
	);
	return (
		<>
			{data && (
				<div className={"field"}>
					<legend>
						{__("Repeatable Field", "atlas-content-modeler")}
					</legend>
					<input
						name="isRepeatableRichText"
						type="checkbox"
						id={`is-repeatable-richtext-${fieldId}`}
						ref={register}
						value={repeatable}
						onChange={() => setRepeatable(!repeatable)}
						disabled={editing}
					/>
					<label
						htmlFor={`is-repeatable-richtext-${fieldId}`}
						className="checkbox is-repeatable"
					>
						{__(
							"Make this field repeatable",
							"atlas-content-modeler"
						)}
					</label>
				</div>
			)}
		</>
	);
};

export default RichTextFields;
