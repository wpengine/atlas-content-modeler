/**
 * Additional form fields for the Email field type.
 */
import React, { useContext, useState } from "react";
import { getTitleFieldId } from "../../queries";
import { ModelsContext } from "../../ModelsContext";
import { useLocationSearch } from "../../utils";
import { __ } from "@wordpress/i18n";

const EmailFields = ({ register, data, editing, fieldId }) => {
	const { models } = useContext(ModelsContext);
	const query = useLocationSearch();
	const currentModel = query.get("id");
	const fields = models[currentModel]?.fields;
	const titleFieldId = getTitleFieldId(fields);
	const showTitleField = !titleFieldId || titleFieldId === fieldId;
	const [showTitle, setShowTitle] = useState(data?.isTitle);
	const [showRepeatableEmail, setShowRepeatableEmail] = useState(
		data?.isRepeatableEmail
	);

	return (
		<>
			{data && (
				<div className={"field"}>
					<legend>
						{__("Repeatable Field", "atlas-content-modeler")}
					</legend>
					<input
						name="isRepeatableEmail"
						type="checkbox"
						id={`is-repeatable-email-${fieldId}`}
						ref={register}
						defaultChecked={showRepeatableEmail}
						onChange={() =>
							setShowRepeatableEmail(!showRepeatableEmail)
						}
						disabled={editing || showTitle}
					/>
					<label
						htmlFor={`is-repeatable-email-${fieldId}`}
						className="checkbox is-repeatable"
					>
						{__(
							"Make this field repeatable",
							"atlas-content-modeler"
						)}
					</label>
				</div>
			)}
			{showTitleField && (
				<div className="field">
					<legend>Title Field</legend>
					<input
						name="isTitle"
						type="checkbox"
						id={`is-title-${fieldId}`}
						ref={register}
						onChange={() => setShowTitle(!showTitle)}
						defaultChecked={data?.isTitle}
						disabled={!!titleFieldId || showRepeatableEmail}
					/>
					<label
						htmlFor={`is-title-${fieldId}`}
						className="checkbox is-title"
					>
						{__(
							"Use this field as the entry title",
							"atlas-content-modeler"
						)}
					</label>
				</div>
			)}
		</>
	);
};

export default EmailFields;
