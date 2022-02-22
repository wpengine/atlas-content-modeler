/**
 * Additional form fields for the Media field type.
 */
import React, { useState } from "react";
import { __ } from "@wordpress/i18n";

const MediaFields = ({ register, data, editing, fieldId }) => {
	const [showRepeatable, setShowRepeatable] = useState(
		data?.isRepeatable === true
	);
	const [showTitle, setShowTitle] = useState(data?.isTitle === true);

	return (
		<>
			{data && (
				<div
					className={showTitle ? "field  read-only editing" : "field"}
				>
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
						disabled={editing || showTitle}
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
