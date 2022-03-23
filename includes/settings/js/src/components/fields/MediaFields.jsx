/**
 * Additional form fields for the Media field type.
 */
import React, { useState } from "react";
import { __ } from "@wordpress/i18n";

const MediaFields = ({ register, data, editing, fieldId, watch }) => {
	const [showRepeatableMedia, setShowRepeatableMedia] = useState(
		data?.isRepeatable === true
	);

	const [isFeatured, setIsFeatured] = useState(data?.isFeatured === true);

	const isFeaturedWatcher = watch("isFeatured");

	return (
		<>
			{data && (
				<div
					className={
						isFeaturedWatcher ? "field read-only editing" : "field"
					}
				>
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
						disabled={editing || isFeaturedWatcher}
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
