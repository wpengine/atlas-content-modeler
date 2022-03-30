/**
 * Additional form fields for the Date field type.
 */
import React, { useState } from "react";
import { __ } from "@wordpress/i18n";

const DateFields = ({ register, data, editing, fieldId }) => {
	const [showRepeatableDate, setShowRepeatableDate] = useState(
		data?.isRepeatableDate === true
	);
	return (
		<>
			{data && (
				<div className={"field"}>
					<legend>
						{__("Repeatable Date Field", "atlas-content-modeler")}
					</legend>
					<input
						// Each name should be unique, so for this field we are using isRepeatableDate.
						name="isRepeatableDate"
						type="checkbox"
						id={`is-repeatable-date-${fieldId}`}
						ref={register}
						value={showRepeatableDate}
						onChange={() =>
							setShowRepeatableDate(!showRepeatableDate)
						}
						disabled={editing}
					/>
					<label
						htmlFor={`is-repeatable-date-${fieldId}`}
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

export default DateFields;
