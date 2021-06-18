/**
 * Additional form fields for the Multiple Choice field type.
 */
import React from "react";
import { useFieldArray } from "react-hook-form";
import supportedFields from "./supportedFields";
import AddIcon from "../../../../../components/icons/AddIcon";
import TrashIcon from "../../../../../components/icons/TrashIcon";
import Icon from "../../../../../components/icons";

function MultipleChoiceFields({
	register,
	type,
	data,
	editing,
	control,
	errors,
	clearErrors,
	setValue,
	watch,
}) {
	const { fields, append, remove } = useFieldArray({
		control,
		name: "choices",
	});

	const currentListType = watch("listType");
	const currentChoices = watch("choices");

	/**
	 * Unchecks other “default value” fields when checking a new default.
	 *
	 * @param newDefaultIndex The choice to make the default.
	 */
	const setDefaultOption = (newDefaultIndex) => {
		const newChoices = currentChoices.map((choice, index) => {
			choice.default = index === newDefaultIndex;
			return choice;
		});
		setValue("choices", newChoices);
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
										className="field multiple-option-container-single"
									>
										<label
											htmlFor={"multipleChoice" + index}
										>
											Option {index + 1}
										</label>
										<br />
										<p className="help">
											Display name for your{" "}
											{supportedFields[type]} option.
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
													placeholder="Option Name"
													type="text"
													onKeyPress={(event) => {
														if (
															event.key ===
															"Enter"
														)
															event.preventDefault();
													}}
													defaultValue={`${item.name}`}
													onChange={(event) => {
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
											<div>
												<button
													className="remove-option tertiary no-border"
													onClick={(event) => {
														event.preventDefault();
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
															});
														remove(index);
													}}
												>
													<a>
														<TrashIcon size="small" />{" "}
														<span>
															Remove option
														</span>
													</a>
												</button>
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
														option names.
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
										append({ name: "", default: false });
									}}
								>
									<a>
										<AddIcon noCircle />{" "}
										<span>
											{fields.length > 0
												? "Add another option"
												: "Add an option"}
										</span>
									</a>
								</button>
								{errors.multipleChoice &&
									errors.multipleChoice.type ===
										"multipleChoiceEmpty" && (
										<span className="error">
											<Icon type="error" />
											<span role="alert">
												Must create an option first.
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
							id="one"
							name="listType"
							value="one"
							ref={register}
							defaultChecked={
								data?.listType === "one" ||
								typeof data?.listType === "undefined"
							}
							disabled={editing}
						/>
						<label className="radio" htmlFor="one">
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
