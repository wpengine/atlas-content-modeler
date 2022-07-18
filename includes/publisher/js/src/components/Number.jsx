/* global atlasContentModelerFormEditingExperience */
/** @jsx jsx */
import React, { useState, useRef } from "react";
import { jsx, css } from "@emotion/react";
import Icon from "../../../../components/icons";
import { __ } from "@wordpress/i18n";
import AddIcon from "../../../../components/icons/AddIcon";
import TrashIcon from "../../../../components/icons/TrashIcon";
import useFocusNewFields from "./shared/repeaters/useFocusNewFields";

export default function Number({
	field,
	errors,
	validate,
	modelSlug,
	defaultError,
}) {
	let numberOptions = {};
	const numberInputRef = useRef();

	if (field?.minValue || field?.minValue === 0) {
		numberOptions.min = field.minValue ?? 0;
	}
	if (field?.maxValue) {
		numberOptions.max = field.maxValue;
	}
	if (field?.step) {
		numberOptions.step = field.step;
	} else {
		field.numberType === "integer"
			? (numberOptions.step = 1)
			: (numberOptions.step = "any");
	}
	/**
	 * Check for need to sanitize number fields further before regular validation
	 * @param event
	 * @param field
	 */
	function preValidate(event, field) {
		const disallowedCharacters = /[.]/g;

		if (field.numberType === "integer") {
			if (disallowedCharacters.test(event.key)) {
				event.preventDefault();
				return;
			}
		}

		// call global validate
		validate(event, field);
		if (field?.isRepeatableNumber) {
			handleKeyPress(event);
		}
	}

	if (field?.isRepeatableNumber) {
		const [values, setValues] = useState(field?.value || [""]);
		const addButtonRef = useRef();

		useFocusNewFields(modelSlug, field?.slug, values);

		/**
		 * Handle keypress to add new entry and continue entering data
		 * @param {*} event
		 */
		function handleKeyPress(event) {
			if (event.key === "Enter") {
				event.preventDefault();

				const lastFieldIsInFocus =
					document.activeElement.getAttribute("name") ===
					`atlas-content-modeler[${modelSlug}][${field.slug}][${
						values?.length - 1
					}]`;

				if (lastFieldIsInFocus) {
					addButtonRef.current.click();
					return;
				}

				const activeFieldName = document.activeElement.getAttribute(
					"name"
				);

				const activeFieldIndex = [
					...document.querySelectorAll(
						`[name*="atlas-content-modeler[${modelSlug}][${field.slug}]`
					),
				]
					.map((field) => field.getAttribute("name"))
					.indexOf(activeFieldName);

				const nextField = document.querySelector(
					`[name="atlas-content-modeler[${modelSlug}][${
						field.slug
					}][${activeFieldIndex + 1}]`
				);

				if (nextField) {
					nextField.focus();
				}
			}
		}

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
				<fieldset>
					<div id="repeaterNumber" className="text-table flex-row">
						<div className="repeater-number-field flex-row">
							<table key="1" className="table mt-2">
								<tbody>
									{values.map((item, index) => {
										return (
											<tr
												key={index}
												className={`field number-repeater-container-single d-flex mt-0 flex-fill flex-row`}
											>
												<td>
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
														<span
															className="px-1 me-2"
															css={css`
																font-family: "Open Sans",
																	sans-serif;
																font-weight: bold;
															`}
														>
															{index + 1}
														</span>
														<div className="me-lg-1 repeater-input-container flex-fill">
															<input
																ref={
																	numberInputRef
																}
																type={`${field.type}`}
																name={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}]`}
																id={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}]`}
																value={
																	values[
																		index
																	]
																}
																required={
																	field.required
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
																onKeyDown={(
																	event
																) =>
																	preValidate(
																		event,
																		field
																	)
																}
																{...numberOptions}
															/>
															<span className="error">
																<Icon type="error" />
																<span role="alert">
																	{errors[
																		field
																			.slug
																	] ??
																		defaultError}
																</span>
															</span>
														</div>
														<div
															className={`value[${index}].remove-container p-2 me-sm-1`}
														>
															{values.length >
																1 && (
																<button
																	type="button"
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
												</td>
											</tr>
										);
									})}
									<tr className="flex add-container">
										<td>
											<button
												className="add-option mt-0 tertiary no-border"
												onClick={(event) => {
													event.preventDefault();
													// Adds a new empty value to display another text field.
													setValues((oldValues) => [
														...oldValues,
														"",
													]);
												}}
												ref={addButtonRef}
											>
												<a>
													<AddIcon noCircle />{" "}
													<span>
														{field.value.length > 0
															? __(
																	`Add Another`,
																	"atlas-content-modeler"
															  )
															: __(
																	`Add Item`,
																	"atlas-content-modeler"
															  )}
													</span>
												</a>
											</button>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</fieldset>
			</>
		);
	}

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
			<input
				ref={numberInputRef}
				type={`${field.type}`}
				name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
				id={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
				defaultValue={field.value}
				required={field.required}
				onChange={(event) => preValidate(event, field)}
				onKeyDown={(event) => preValidate(event, field)}
				{...numberOptions}
			/>
			<span className="error">
				<Icon type="error" />
				<span role="alert">{errors[field.slug] ?? defaultError}</span>
			</span>
		</>
	);
}
