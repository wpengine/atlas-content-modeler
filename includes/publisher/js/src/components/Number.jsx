/* global atlasContentModelerFormEditingExperience */
import React, { useState, useRef } from "react";
import Icon from "../../../../components/icons";
import { __ } from "@wordpress/i18n";
const { wp } = window;
import AddIcon from "../../../../components/icons/AddIcon";
import TrashIcon from "../../../../components/icons/TrashIcon";

export default function Number({
	field,
	errors,
	validate,
	modelSlug,
	defaultError,
}) {
	console.log(field);
	if (field.isRepeatable) {
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
				<div className="repeater-text-field flex-row">
					<table key="1" className="table mt-2">
						<tbody>
							{values.map((item, index) => {
								return (
									<>
										<div>
											<input
												ref={numberInputRef}
												type={`${field.type}`}
												name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
												id={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
												defaultValue={field.value}
												required={field.required}
												onChange={(event) =>
													preValidate(event, field)
												}
												onKeyDown={(event) =>
													preValidate(event, field)
												}
												{...numberOptions}
											/>
											<span className="error">
												<Icon type="error" />
												<span role="alert">
													{errors[field.slug] ??
														defaultError}
												</span>
											</span>
										</div>
										<div
											className={`value[${index}].remove-container p-2 me-sm-1`}
										>
											{values.length > 1 && (
												<button
													className="remove-item tertiary no-border"
													onClick={(event) => {
														event.preventDefault();
														// Removes the value at the given index.
														setValues(
															(currentValues) => {
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
									</>
								);
							})}
							<tr className="flex add-container">
								<button
									className="add-option mt-1 tertiary no-border"
									onClick={(event) => {
										event.preventDefault();
										// Adds a new empty value to display another text field.
										setValues((oldValues) => [
											...oldValues,
											"",
										]);
									}}
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
							</tr>
						</tbody>
					</table>
				</div>
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
