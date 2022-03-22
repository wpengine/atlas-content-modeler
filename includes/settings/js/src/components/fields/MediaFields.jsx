/**
 * Additional form fields for the Media field type.
 */
import React, { useState } from "react";
import { __ } from "@wordpress/i18n";

const MediaFields = ({ register, data, editing, fieldId }) => {
	const [showRepeatableMedia, setShowRepeatableMedia] = useState(
		data?.isRepeatable === true
	);

	const [isFeaturedImage, setIsFeaturedImage] = useState(
		data?.isFeaturedImage === true
	);

	return (
		<>
			{data && (
				<div className={"field"}>
					<legend>
						{__("Repeatable Field", "atlas-content-modeler")}
					</legend>
					<input
						name="isRepeatableMedia"
						type="checkbox"
						id={`is-repeatable-${fieldId}`}
						ref={register}
						value={showRepeatableMedia}
						onChange={() =>
							setShowRepeatableMedia(!showRepeatableMedia)
						}
						disabled={isFeaturedImage}
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
