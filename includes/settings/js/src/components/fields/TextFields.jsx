/**
 * Additional form fields for the Text field type.
 */
import React, { useContext, useRef } from "react";
import { getTitleFieldId } from "../../queries";
import { ModelsContext } from "../../ModelsContext";
import { useLocationSearch } from "../../utils";

const TextFields = ({ register, data, editing, fieldId }) => {
	const { models, dispatch } = useContext(ModelsContext);
	const query = useLocationSearch();
	const currentModel = query.get("id");
	const fields = models[currentModel]?.fields;
	const originalTitleFieldId = useRef(getTitleFieldId(fields));

	return (
		<>
			<div className="field">
				<legend>Field Options</legend>
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
								properties: [{ name: "isTitle", value: false }],
							});
						}
					}}
				/>
				<label htmlFor={`is-title-${fieldId}`} className="checkbox">
					Use this field as the entry title
				</label>
			</div>
			<div className={editing ? "field read-only editing" : "field"}>
				<legend>Text Length</legend>
				<fieldset>
					<div className="radio-row">
						<input
							type="radio"
							id="short"
							name="textLength"
							value="short"
							ref={register}
							defaultChecked={
								data?.textLength === "short" ||
								typeof data?.textLength === "undefined"
							}
							disabled={editing}
						/>
						<label className="radio" htmlFor="short">
							Short text
							<span>Maximum 50 characters</span>
						</label>
					</div>
					<div className="radio-row">
						<input
							type="radio"
							id="long"
							name="textLength"
							value="long"
							ref={register}
							defaultChecked={data?.textLength === "long"}
							disabled={editing}
						/>
						<label className="radio" htmlFor="long">
							Long text
							<span>Maximum 500 characters</span>
						</label>
					</div>
				</fieldset>
			</div>
		</>
	);
};

export default TextFields;
