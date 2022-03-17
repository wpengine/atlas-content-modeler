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
	const [showRepeatable, setShowRepeatable] = useState(
		data?.isRepeatable === true
	);
	const [showTitle, setShowTitle] = useState(data?.isTitle === true);

	return (
		<>
			{showTitleField && (
				<div
					className={
						showRepeatable ? "field  read-only editing" : "field"
					}
				>
					<legend>Title Field</legend>
					<input
						name="isTitle"
						type="checkbox"
						id={`is-title-${fieldId}`}
						ref={register}
						onChange={() => setShowTitle(!showTitle)}
						defaultChecked={data?.isTitle === true}
						disabled={!!titleFieldId || showRepeatable}
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
