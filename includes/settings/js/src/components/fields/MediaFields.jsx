/**
 * Additional form fields for the Media field type.
 */
import React, { useState } from "react";
import { __ } from "@wordpress/i18n";

const MediaFields = ({ register, data, editing, fieldId }) => {
	const [showRepeatable, setShowRepeatable] = useState(
		data?.isRepeatable === true
	);

	return (
		<>
			{data && (
				<div className={"field"}>
					<legend>
						{__("Repeatable Field", "atlas-content-modeler")}
					</legend>
					<input
						name="isRepeatable"
						type="checkbox"
						id={`is-repeatable-${fieldId}`}
						ref={register}
						defaultChecked={showRepeatable}
						onChange={() => setShowRepeatable(!showRepeatable)}
						disabled={editing}
					/>
					<label
						htmlFor={`is-repeatable-${fieldId}`}
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

export default MediaFields;
