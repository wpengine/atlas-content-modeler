/**
 * Additional form fields for the Text field type.
 */
import React, { useContext, useState } from "react";
import { getTitleFieldId } from "../../queries";
import { ModelsContext } from "../../ModelsContext";
import { useLocationSearch } from "../../utils";
import { __ } from "@wordpress/i18n";

const TextFields = ({ register, data, editing, fieldId }) => {
	const { models } = useContext(ModelsContext);
	const query = useLocationSearch();
	const currentModel = query.get("id");
	const fields = models[currentModel]?.fields;
	const titleFieldId = getTitleFieldId(fields);
	const showTitleField = !titleFieldId || titleFieldId === fieldId;
	const [showRepeatable, setShowRepeatable] = useState(
		data?.isRepeatable === true
	);
	return (
		<>
			{data && (
				<div className="field">
					<legend>Repeatable Field</legend>
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
					<div
						className={
							"radio-row d-flex flex-column d-sm-flex flex-sm-row"
						}
					>
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
