/**
 * Additional form fields for the Text field type.
 */
import React, { useContext, useRef } from "react";
import { getTitleFieldId } from "../../queries";
import { ModelsContext } from "../../ModelsContext";
import { useLocationSearch } from "../../utils";
import { __ } from "@wordpress/i18n";

const TextFields = ({ register, data, editing, fieldId }) => {
	const { models } = useContext(ModelsContext);
	const query = useLocationSearch();
	const currentModel = query.get("id");
	const fields = models[currentModel]?.fields;
	const originalTitleFieldId = useRef(getTitleFieldId(fields));
	const shouldShowTitleField =
		!originalTitleFieldId.current ||
		originalTitleFieldId.current === fieldId;

	return (
		<>
			{shouldShowTitleField && (
				<div className="field">
					<legend>Title Field</legend>
					<input
						name="isTitle"
						type="checkbox"
						id={`is-title-${fieldId}`}
						ref={register}
						defaultChecked={data?.isTitle === true}
						disabled={!!originalTitleFieldId.current}
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
			<div className={editing ? "field read-only editing" : "field"}>
				<legend>Input Type</legend>
				<fieldset>
					<div className="radio-row">
						<input
							type="radio"
							id="single"
							name="inputType"
							value="single"
							ref={register}
							defaultChecked={
								data?.inputType === "single" ||
								typeof data?.inputType === "undefined"
							}
							disabled={editing}
						/>
						<label className="radio" htmlFor="single">
							{__("Single line", "atlas-content-modeler")}
							<span>
								{__(
									"Displays an input field",
									"atlas-content-modeler"
								)}
							</span>
						</label>
					</div>
					<div className="radio-row">
						<input
							type="radio"
							id="multi"
							name="inputType"
							value="multi"
							ref={register}
							defaultChecked={data?.inputType === "multi"}
							disabled={editing}
						/>
						<label className="radio" htmlFor="multi">
							{__("Multiple lines", "atlas-content-modeler")}
							<span>
								{__(
									"Displays a textarea field",
									"atlas-content-modeler"
								)}
							</span>
						</label>
					</div>
				</fieldset>
			</div>
		</>
	);
};

export default TextFields;
