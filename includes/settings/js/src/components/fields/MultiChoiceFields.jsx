/**
 * Additional form fields for the Multi Choice field type.
 */
import React from "react";
import { useFieldArray } from "react-hook-form";
import supportedFields from "./supportedFields";
import AddIcon from "../../../../../components/icons/AddIcon";

function MultiChoiceFields({
	register,
	type,
	data,
	editing,
	control,
	errors,
	setValue,
	watch,
}) {
	const { fields, append, remove } = useFieldArray({
		control,
		name: "choices",
	});

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
		<div className={editing ? "field read-only editing" : "field"}>
			<fieldset>
				<div
					id="multiOptions"
					className="d-flex flex-column d-sm-flex flex-sm-row"
				>
					<div className="">
						<ul>
							{fields.map((item, index) => {
								return (
									<div key={item.id} className="field">
										<label htmlFor="name">
											Option {index + 1}
										</label>
										<br />
										<p className="help">
											Display name for your{" "}
											{supportedFields[type]} option.
										</p>
										<div
											className={`${
												errors.name
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
													defaultValue={`${item.name}`}
												/>
											</div>
											<div className="me-sm-5 default-checkbox">
												<input
													ref={register()}
													name={`choices[${index}].default`}
													id={`choices-${index}-default`}
													type="checkbox"
													defaultChecked={
														item?.default
													}
													onClick={(event) => {
														if (
															event.target.checked
														) {
															setDefaultOption(
																index
															);
														}
													}}
												/>
												<label
													htmlFor={`choices-${index}-default`}
													className="checkbox is-required"
												>
													Default Value
												</label>
											</div>
											<div>
												<button
													className="tertiary"
													onClick={(event) => {
														event.preventDefault();
														remove(index);
													}}
												>
													<a>
														<AddIcon size="small" />{" "}
														<span>
															Remove option
														</span>
													</a>
												</button>
											</div>
										</div>
									</div>
								);
							})}
							<div className="field">
								<button
									className="tertiary"
									onClick={(event) => {
										event.preventDefault();
										append({ name: "", default: false });
									}}
								>
									<a>
										<AddIcon size="small" />{" "}
										<span>
											{fields.length > 0
												? "Add another option"
												: "Add an option"}
										</span>
									</a>
								</button>
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
							id="multi"
							name="listType"
							value="multi"
							ref={register()}
							defaultChecked={data?.listType === "multi"}
							disabled={editing}
						/>
						<label className="radio" htmlFor="multi">
							Multi Select
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

export default MultiChoiceFields;
