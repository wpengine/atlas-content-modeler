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
		append({ name: "", slug: "" });
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
											Choice {index + 1}
										</label>
										<br />
										<p className="help">
											Display name and API identifier for
											your {supportedFields[type]} choice.
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
													name={`choices[${index}].name`}
													placeholder="Choice Name"
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
														errors &&
															Object.entries(
																errors
															).map((item) => {
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
															});
														clearErrors(
															"multipleChoice" +
																index
														);
														clearErrors(
															"multipleChoiceName" +
																index
														);
													}}
												/>
											</div>
											<div className={`${item.name}`}>
												<input
													placeholder="Choice API Identifier"
													type="text"
													onChange={(event) => {
														errors &&
															Object.entries(
																errors
															).map((item) => {
																item[1].type.includes(
																	"multipleChoiceSlugDuplicate"
																) &&
																	clearErrors(
																		item[0]
																			.type
																	);
																item[1].type.includes(
																	"multipleChoiceSlugEmpty"
																) &&
																	clearErrors(
																		item[0]
																			.type
																	);
															});
														clearErrors(
															"multipleChoice" +
																index
														);
														event.target.value = toValidApiId(
															event.target.value
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
																	Must set a
																	choice
																	identifier.
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
																	Cannot have
																	duplicate
																	identifier.
																</span>
															</span>
														)}
												</div>
											</div>
											<div>
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
														<a>
															<TrashIcon size="small" />{" "}
															<span>
																Remove choice
															</span>
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
														Must set a name.
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
														Cannot have duplicate
														choice names.
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
												? "Add another choice"
												: "Add a choice"}
										</span>
									</a>
								</button>
								{errors.multipleChoice &&
									errors.multipleChoice.type ===
										"multipleChoiceEmpty" && (
										<span className="error">
											<Icon type="error" />
											<span role="alert">
												Must create a choice first.
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
							Single Select
							<span>
								Select this if you need a list of radio buttons
								(single selection)
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
							Multiple Select
							<span>
								Select this if you need a list of checkboxes
								(multiple selections)
							</span>
						</label>
					</div>
				</div>
			</fieldset>
		</div>
	);
}

export default MultipleChoiceFields;
