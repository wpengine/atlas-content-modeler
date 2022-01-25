/* global atlasContentModelerFormEditingExperience */
import React, { useState, useRef } from "react";
import Icon from "../../../../components/icons";
import { __ } from "@wordpress/i18n";
const { wp } = window;
import AddIcon from "../../../../components/icons/AddIcon";
import TrashIcon from "../../../../components/icons/TrashIcon";

export default function Text({ field, modelSlug, defaultError }) {
	const [errors, setErrors] = useState({});

	if (field.isRepeatable) {
		const [values, setValues] = useState(field?.value || [""]);
		return (
			<div className={"field"}>
				<label>{field.name}</label>
				{field?.required && (
					<p className="required">
						*{__("Required", "atlas-content-modeler")}
					</p>
				)}
				{field?.description && (
					<p className="help mb-0">{field.description}</p>
				)}
				<fieldset>
					<div id="repeaterText" className="text-table flex-row">
						<div className="repeater-text-field">
							<ul>
								<table
									key="1"
									className="table table-striped mt-2"
								>
									<thead>
										<tr>
											<th>
												{__(
													field?.name,
													"atlas-content-modeler"
												)}
											</th>
										</tr>
									</thead>
									<tbody>
										{values.map((item, index) => {
											return (
												<div
													key={index}
													className={`field text-repeater-container-single`}
												>
													<div
														className={`${
															errors[
																"repeaterText" +
																	index
															]
																? "field has-error"
																: "field"
														} d-flex flex-row repeater-input d-lg-flex`}
													>
														<div
															className="me-lg-1"
															name="repeaters"
														>
															{field.inputType ===
															"single" ? (
																<input
																	name={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}]`}
																	placeholder={__(
																		"Item Name",
																		"atlas-content-modeler"
																	)}
																	type="text"
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
																		values[
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
																		"Item Name",
																		"atlas-content-modeler"
																	)}
																	type="text"
																	value={
																		values[
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
															{values.length >
																1 && (
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
												</div>
											);
										})}
										<div className="field">
											<button
												className="add-option tertiary no-border"
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
																	"Add another item",
																	"atlas-content-modeler"
															  )
															: __(
																	"Add an item",
																	"atlas-content-modeler"
															  )}
													</span>
												</a>
											</button>
										</div>
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
