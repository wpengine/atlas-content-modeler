/* global atlasContentModelerFormEditingExperience */
import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import Icon from "../../../../../components/icons";
import TrashIcon from "../../../../../components/icons/TrashIcon";
import AddRepeatableItemButton from "./AddRepeatableItemButton";

export default function Text({
	field,
	errors,
	validate,
	modelSlug,
	defaultError,
}) {
	if (field.isRepeatable) {
		function getFieldValues() {
			const minLength = parseInt(field.minRepeatable) || 1;

			if (!field?.value) {
				return new Array(minLength).fill("", 0);
			}

			if (minLength < field.value.length) {
				return field.value;
			}

			return field.value.concat(
				new Array(minLength - field.value.length).fill("", 0)
			);
		}

		const [fieldValues, setValues] = useState(getFieldValues());

		const validFieldValues = fieldValues.filter((item) => !!item);
		const showDeleteButton = field.minRepeatable
			? fieldValues.length > field.minRepeatable
			: fieldValues.length > 1;
		const isMaxInputs =
			field.maxRepeatable && fieldValues.length === field.maxRepeatable;
		const isMinRequired =
			field.minRepeatable &&
			validFieldValues.length > 0 &&
			validFieldValues.length < field.minRepeatable;
		const isRequired = field?.required || isMinRequired;

		return (
			<div className={"field"}>
				<label>{field.name}</label>
				{isRequired && (
					<p className="required">
						*{__("Required", "atlas-content-modeler")}
						{isMinRequired && ` Minimum of ${field.minRepeatable}`}
					</p>
				)}
				{field?.description && (
					<p className="help mb-0">{field.description}</p>
				)}
				<fieldset>
					<div id="repeaterText" className="text-table flex-row">
						<div className="repeater-text-field flex-row">
							<ul>
								<table key="1" className="table mt-2">
									<tbody>
										{fieldValues.map((item, index) => {
											return (
												<tr
													key={index}
													className={`field text-repeater-container-single d-flex mt-1 flex-fill flex-row`}
												>
													<div
														className={`${
															errors[
																"repeaterText" +
																	index
															]
																? "field has-error"
																: "field"
														} d-flex flex-row repeater-input mt-0 flex-fill d-lg-flex`}
													>
														<div
															className="me-lg-1 repeater-input-container flex-fill"
															name="repeaters"
														>
															{field.inputType ===
															"single" ? (
																<input
																	name={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}]`}
																	placeholder={__(
																		`Add ${field.name}`,
																		"atlas-content-modeler"
																	)}
																	type="text"
																	required={
																		isRequired
																	}
																	minLength={
																		field?.minChars
																	}
																	maxLength={
																		field?.maxChars
																	}
																	onKeyPress={(
																		event
																	) => {
																		if (
																			event.key ===
																			"Enter"
																		) {
																			event.preventDefault();
																		}
																	}}
																	value={
																		fieldValues[
																			index
																		]
																	}
																	onChange={(
																		event
																	) => {
																		// Update the value of the item.
																		const newValue =
																			event
																				.currentTarget
																				.value;
																		setValues(
																			(
																				oldValues
																			) => {
																				let newValues = [
																					...oldValues,
																				];
																				newValues[
																					index
																				] = newValue;
																				return newValues;
																			}
																		);
																	}}
																/>
															) : (
																<textarea
																	name={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}]`}
																	placeholder={__(
																		`Add ${field.name}`,
																		"atlas-content-modeler"
																	)}
																	type="text"
																	required={
																		isRequired
																	}
																	minLength={
																		field?.minChars
																	}
																	maxLength={
																		field?.maxChars
																	}
																	value={
																		fieldValues[
																			index
																		]
																	}
																	onChange={(
																		event
																	) => {
																		// Update the value of the item.
																		const newValue =
																			event
																				.currentTarget
																				.value;
																		setValues(
																			(
																				oldValues
																			) => {
																				let newValues = [
																					...oldValues,
																				];
																				newValues[
																					index
																				] = newValue;
																				return newValues;
																			}
																		);
																	}}
																/>
															)}
														</div>
														<div
															className={`value[${index}].remove-container p-2 me-sm-1`}
														>
															{showDeleteButton && (
																<button
																	className="remove-item tertiary no-border"
																	onClick={(
																		event
																	) => {
																		event.preventDefault();
																		// Removes the value at the given index.
																		setValues(
																			(
																				currentValues
																			) => {
																				const newValues = [
																					...currentValues,
																				];
																				newValues.splice(
																					index,
																					1
																				);
																				return newValues;
																			}
																		);
																	}}
																>
																	<a
																		aria-label={__(
																			"Remove item.",
																			"atlas-content-modeler"
																		)}
																	>
																		<TrashIcon size="small" />{" "}
																	</a>
																</button>
															)}
														</div>
													</div>
												</tr>
											);
										})}
										<tr className="flex add-container">
											<AddRepeatableItemButton
												field={field}
												values={fieldValues}
												setValues={setValues}
												isMaxInputs={isMaxInputs}
											/>
										</tr>
									</tbody>
								</table>
							</ul>
						</div>
					</div>
				</fieldset>
			</div>
		);
	}

	const textProps = {
		type: `${field.type}`,
		name: `atlas-content-modeler[${modelSlug}][${field.slug}]`,
		id: `atlas-content-modeler[${modelSlug}][${field.slug}]`,
		defaultValue: field.value,
		required: field.required,
		onChange: (event) => validate(event, field),
		minLength: field?.minChars,
		maxLength: field?.maxChars,
	};

	return (
		<>
			<label
				htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
			>
				{field.name}
			</label>
			{field?.required && (
				<p className="required">
					*{__("Required", "atlas-content-modeler")}
				</p>
			)}
			{field?.description && (
				<p className="help mb-0">{field.description}</p>
			)}
			{field?.inputType === "multi" ? (
				<textarea {...textProps} />
			) : (
				<input {...textProps} />
			)}
			<span className="error">
				<Icon type="error" />
				<span role="alert">{errors[field.slug] ?? defaultError}</span>
			</span>
		</>
	);
}