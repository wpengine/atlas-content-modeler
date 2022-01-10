/**
 * Additional form fields for the Multiple Choice field type.
 */
import React from "react";
import { useFieldArray } from "react-hook-form";
import supportedFields from "./supportedFields";
import AddIcon from "../../../../../components/icons/AddIcon";
import TrashIcon from "../../../../../components/icons/TrashIcon";
import Icon from "../../../../../components/icons";
import { toValidApiId } from "../../formats";
import { sprintf, __ } from "@wordpress/i18n";

function MultipleChoiceFields({
	register,
	type,
	data,
	editing,
	control,
	errors,
	clearErrors,
}) {
	let { fields, append, remove } = useFieldArray({
		control,
		name: "choices",
	});

	if (fields.length < 1) {
		append({ name: "", slug: "" }, false);
	}

	/**
	 * Checks if a choice has been saved, so that new choices can be given
	 * editable API Identifier fields, but saved choices have read-only IDs.
	 *
	 * @param choice
	 * @return {boolean}
	 */
	const choiceIsSaved = (choice) => {
		return (
			data.choices &&
			data.choices.some((savedChoice) => savedChoice.slug === choice.slug)
		);
	};

	const clearChoiceIdentifierErrors = (errors, index, event) => {
		errors &&
			Object.entries(errors).map((item) => {
				item[1].type.includes("multipleChoiceSlugDuplicate") &&
					clearErrors(item[0].type);
				item[1].type.includes("multipleChoiceSlugEmpty") &&
					clearErrors(item[0].type);
			});
		clearErrors("multipleChoice" + index);
		event.target.value = toValidApiId(event.target.value);
	};

	const clearNameErrors = (errors, index) => {
		errors &&
			Object.entries(errors).map((item) => {
				item[1].type.includes("multipleChoiceNameDuplicate") &&
					clearErrors(item[0].type);
				item[1].type.includes("multipleChoiceNameEmpty") &&
					clearErrors(item[0].type);
			});
		clearErrors("multipleChoice" + index);
		clearErrors("multipleChoiceName" + index);
	};

	return (
		<div className={editing ? "field read-only" : "field"}>
			<fieldset>
				<div
					id="multipleChoices"
					className="d-flex flex-column d-sm-flex flex-sm-row"
				>
					<div className="multiple-option-container">
						<ul>
							{fields.map((item, index) => {
								return (
									<div
										key={item.id}
										className={`field multiple-option-container-single`}
									>
										<label
											htmlFor={"multipleChoice" + index}
										>
											{__(
												"Choice",
												"atlas-content-modeler"
											)}
											{index + 1}
										</label>
										<br />
										<p className="help">
											{__(
												"Display name and API identifier for your choice",
												"atlas-content-modeler"
											)}
										</p>
										<div
											className={`${
												errors["multipleChoice" + index]
													? "field has-error"
													: "field"
											} d-flex flex-column d-sm-flex flex-sm-row me-sm-5`}
										>
											<div
												className="me-sm-5"
												name="multiples"
											>
												<input
													ref={register()}
													name={`choices[${index}].name`}
													placeholder={__(
														"Choice Name",
														"atlas-content-modeler"
													)}
													type="text"
													onKeyPress={(event) => {
														if (
															event.key ===
															"Enter"
														)
															event.preventDefault();
													}}
													defaultValue={`${item.name}`}
													onChange={(e) => {
														clearNameErrors(
															errors,
															index
														);
													}}
												/>
											</div>
											<div className={`${item.name}`}>
												<input
													ref={register()}
													placeholder={__(
														"Choice API Identifier",
														"atlas-content-modeler"
													)}
													type="text"
													onChange={(event) => {
														clearChoiceIdentifierErrors(
															errors,
															index,
															event
														);
													}}
													onKeyPress={(event) => {
														if (
															event.key ===
															"Enter"
														)
															event.preventDefault();
													}}
													defaultValue={`${item.slug}`}
													name={`choices[${index}].slug`}
													id={`choices[${index}].slug`}
													readOnly={choiceIsSaved(
														item
													)}
												/>
												<div>
													{errors[
														"multipleChoice" + index
													] &&
														errors[
															"multipleChoice" +
																index
														].type ===
															"multipleChoiceSlugEmpty" +
																index && (
															<span className="error">
																<Icon type="error" />
																<span role="alert">
																	{__(
																		"Must set a choice identifier.",
																		"atlas-content-modeler"
																	)}
																</span>
															</span>
														)}
													{errors[
														"multipleChoice" + index
													] &&
														errors[
															"multipleChoice" +
																index
														].type ===
															"multipleChoiceSlugDuplicate" +
																index && (
															<span className="error">
																<Icon type="error" />
																<span role="alert">
																	{__(
																		"Cannot have duplicate identifier.",
																		"atlas-content-modeler"
																	)}
																</span>
															</span>
														)}
												</div>
											</div>
											<div
												className={`choices[${index}].remove-container`}
											>
												{fields.length > 1 && (
													<button
														className="remove-option tertiary no-border"
														onClick={(event) => {
															event.preventDefault();
															errors &&
																Object.entries(
																	errors
																).map(
																	(item) => {
																		item[1].type.includes(
																			"multipleChoiceNameDuplicate"
																		) &&
																			clearErrors(
																				item[0]
																					.type
																			);
																		item[1].type.includes(
																			"multipleChoiceNameEmpty"
																		) &&
																			clearErrors(
																				item[0]
																					.type
																			);
																	}
																);
															remove(index);
														}}
													>
														<a
															aria-label={__(
																"Remove choice.",
																"atlas-content-modeler"
															)}
														>
															<TrashIcon size="small" />{" "}
														</a>
													</button>
												)}
											</div>
										</div>
										{errors["multipleChoice" + index] &&
											errors["multipleChoice" + index]
												.type ===
												"multipleChoiceNameEmpty" +
													index && (
												<span className="error">
													<Icon type="error" />
													<span role="alert">
														{__(
															"Must set a name.",
															"atlas-content-modeler"
														)}
													</span>
												</span>
											)}
										{errors["multipleChoiceName" + index] &&
											errors["multipleChoiceName" + index]
												.type ===
												"multipleChoiceNameDuplicate" +
													index && (
												<span className="error">
													<Icon type="error" />
													<span role="alert">
														{__(
															"Cannot have duplicate choice names.",
															"atlas-content-modeler"
														)}
													</span>
												</span>
											)}
									</div>
								);
							})}
							<div className="field">
								<button
									className="add-option tertiary no-border"
									onClick={(event) => {
										event.preventDefault();
										clearErrors("multipleChoice");
										append({ name: "", slug: "" });
									}}
								>
									<a>
										<AddIcon noCircle />{" "}
										<span>
											{fields.length > 0
												? __(
														"Add another choice",
														"atlas-content-modeler"
												  )
												: __(
														"Add a choice",
														"atlas-content-modeler"
												  )}
										</span>
									</a>
								</button>
								{errors.multipleChoice &&
									errors.multipleChoice.type ===
										"multipleChoiceEmpty" && (
										<span className="error">
											<Icon type="error" />
											<span role="alert">
												{__(
													"Must create a choice first.",
													"atlas-content-modeler"
												)}
											</span>
										</span>
									)}
							</div>
						</ul>
					</div>
				</div>
			</fieldset>
			<fieldset>
				<div className="field">
					<legend>List Type</legend>
					<div className="radio-row">
						<input
							type="radio"
							id="single"
							name="listType"
							value="single"
							ref={register}
							defaultChecked={
								data?.listType === "single" ||
								typeof data?.listType === "undefined"
							}
							disabled={editing}
						/>
						<label className="radio" htmlFor="single">
							{__("Single Select.", "atlas-content-modeler")}
							<span>
								{__(
									"Select this if you need a list of radio buttons. (single selection)",
									"atlas-content-modeler"
								)}
							</span>
						</label>
					</div>
					<div className="radio-row">
						<input
							type="radio"
							id="multiple"
							name="listType"
							value="multiple"
							ref={register()}
							defaultChecked={data?.listType === "multiple"}
							disabled={editing}
						/>
						<label className="radio" htmlFor="multiple">
							{__("Multiple Select", "atlas-content-modeler")}
							<span>
								{__(
									"Select this if you need a list of checkboxes. (multiple selections)",
									"atlas-content-modeler"
								)}
							</span>
						</label>
					</div>
				</div>
			</fieldset>
		</div>
	);
}

export default MultipleChoiceFields;
