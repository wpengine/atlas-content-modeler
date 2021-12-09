/**
 * Additional form fields for the Text field type.
 */
import React, { useContext, useRef } from "react";
import { getTitleFieldId } from "../../queries";
import { ModelsContext } from "../../ModelsContext";
import { useLocationSearch } from "../../utils";
import { sprintf, __ } from "@wordpress/i18n";

const TextFields = ({ register, data, editing, fieldId }) => {
	const { models, dispatch } = useContext(ModelsContext);
	const query = useLocationSearch();
	const currentModel = query.get("id");
	const fields = models[currentModel]?.fields;
	const originalTitleFieldId = useRef(getTitleFieldId(fields));

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
						checked={data?.isRepeatable === true}
						onChange={(event) => {
							/**
							 * Unchecks other fields when checking a field.
							 * Only one field can be the title field.
							 */
							if (event.target.checked) {
								dispatch({
									type: "setRepeatableField",
									id: fieldId,
									model: currentModel,
								});
								return;
							}

							if (!event.target.checked) {
								/**
								 * At this point we're just unchecking the original
								 * title field.
								 */
								dispatch({
									type: "setFieldProperties",
									id: fieldId,
									model: currentModel,
									properties: [
										{ name: "isRepeatable", value: false },
									],
								});
							}
						}}
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
			{!data?.parent && (
				<div className="field">
					<legend>Title Field</legend>
					<input
						name="isTitle"
						type="checkbox"
						id={`is-title-${fieldId}`}
						ref={register}
						checked={data?.isTitle === true}
						onChange={(event) => {
							/**
							 * Unchecks other fields when checking a field.
							 * Only one field can be the title field.
							 */
							if (event.target.checked) {
								dispatch({
									type: "setTitleField",
									id: fieldId,
									model: currentModel,
								});
								return;
							}

							if (!event.target.checked) {
								/**
								 * When unchecking a field that was not the original
								 * title, restore isTitle on the original title
								 * field if there is one. Prevents an issue where
								 * checking “is title” then unchecking it removes
								 * isTitle from the original.
								 */
								if (
									originalTitleFieldId.current &&
									originalTitleFieldId.current !== fieldId
								) {
									dispatch({
										type: "setTitleField",
										id: originalTitleFieldId.current,
										model: currentModel,
									});
									return;
								}

								/**
								 * At this point we're just unchecking the original
								 * title field.
								 */
								dispatch({
									type: "setFieldProperties",
									id: fieldId,
									model: currentModel,
									properties: [
										{ name: "isTitle", value: false },
									],
								});
							}
						}}
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
