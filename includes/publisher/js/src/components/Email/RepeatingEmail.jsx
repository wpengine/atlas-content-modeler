/** @jsx jsx */
import React, { useState, useRef } from "react";
import { jsx, css } from "@emotion/react";
import Icon from "../../../../../components/icons";
import { buildWildcardRegex } from "../../../../../shared-assets/js/validation/emailValidation";
import TrashIcon from "../../../../../components/icons/TrashIcon";
import AddIcon from "../../../../../components/icons/AddIcon";
import { __ } from "@wordpress/i18n";
import EmailHeader from "./EmailHeader";
import useFocusNewFields from "../shared/repeaters/useFocusNewFields";

const RepeatingEmail = ({
	modelSlug,
	field,
	validate,
	errors,
	defaultError,
}) => {
	const getDefaultValues = () => {
		if (field?.minRepeatable) {
			return Array(field.minRepeatable).fill("");
		} else if (field?.exactRepeatable) {
			return Array(field.exactRepeatable).fill("");
		} else {
			return [""];
		}
	};

	const [values, setValues] = useState(field?.value || getDefaultValues());
	const emailPattern = buildWildcardRegex(field.allowedDomains);

	const addButtonRef = useRef();

	useFocusNewFields(modelSlug, field?.slug, values);

	/**
	 * When enter is pressed move focus to the next field, or add a new
	 * field if the field in focus is the final one in the repeating list.
	 *
	 * @param {object} event
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

			const activeFieldName = document.activeElement.getAttribute("name");

			const activeFieldIndex = [
				...document.querySelectorAll(
					`[name*="atlas-content-modeler[${modelSlug}][${field.slug}]`
				),
			]
				.map((field) => field.getAttribute("name"))
				.indexOf(activeFieldName);

			const nextField = document.querySelector(
				`[name="atlas-content-modeler[${modelSlug}][${field.slug}][${
					activeFieldIndex + 1
				}]`
			);

			if (nextField) {
				nextField.focus();
			}
		}
	}

	const shouldShowAddItemButton = () => {
		if (field?.exactRepeatable) {
			return false;
		} else if (field?.maxRepeatable) {
			return values.length < field.maxRepeatable;
		} else {
			return true;
		}
	};

	const shouldShowDeleteItemButton = () => {
		if (field?.exactRepeatable) {
			return false;
		} else if (field?.minRepeatable) {
			return values.length > field.minRepeatable;
		} else {
			return true;
		}
	};

	return (
		<>
			<EmailHeader modelSlug={modelSlug} field={field} />
			<fieldset>
				<div id="repeaterEmail" className="text-table flex-row">
					<div className="repeater-email-field flex-row">
						<table key="1" className="table mt-2">
							<tbody>
								{values.map((item, index) => {
									return (
										<tr
											key={index}
											className={`field email-repeater-container-single d-flex mt-0 flex-fill flex-row`}
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
															type={`${field.type}`}
															name={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}]`}
															id={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}]`}
															value={
																values[index]
															}
															required={
																field.required
															}
															pattern={
																emailPattern
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
																validate(
																	event,
																	field
																)
															}
															onKeyPress={
																handleKeyPress
															}
														/>
														<span className="error">
															<Icon type="error" />
															<span role="alert">
																{errors[
																	field.slug
																] ??
																	defaultError}
															</span>
														</span>
													</div>
													{shouldShowDeleteItemButton() && (
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
													)}
												</div>
											</td>
										</tr>
									);
								})}
								{shouldShowAddItemButton() && (
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
												type="button"
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
								)}
							</tbody>
						</table>
					</div>
				</div>
			</fieldset>
		</>
	);
};

export default RepeatingEmail;
