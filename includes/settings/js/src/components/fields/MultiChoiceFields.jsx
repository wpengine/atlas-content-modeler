/**
 * Additional form fields for the Multi Choice field type.
 */
import React, { useContext, useState } from "react";
import { useForm, useFieldArray, useFormContext } from "react-hook-form";
import supportedFields from "./supportedFields";
import AddIcon from "../../../../../components/icons/AddIcon";
import { ModelsContext } from "../../ModelsContext";
import { useApiIdGenerator } from "./useApiIdGenerator";

function MultiChoiceFields({ register, type, data, editing }) {
	const { handleSubmit, errors, setValue, clearErrors, control, watch, setError } = useForm({
		// defaultValues: {
    //   fieldArray: [{ id: "0", value: "Option 1" }]
    // }
	});

	const { setApiIdGeneratorInput, apiIdFieldAttributes } = useApiIdGenerator({
		setValue,
		editing,
		data,
	});

	const { dispatch } = useContext(ModelsContext);

	const { fields, append } = useFieldArray({
    control,
    name: "fieldArray"
  });

	const watchFieldArray = watch("fieldArray");
  const controlledFields = fields.map((field, index) => {
    return {
      ...field,
      ...watchFieldArray[index]
    };
  });
 
	return (
		<div className={editing ? "field read-only editing" : "field"}>
			<fieldset>
				<div id="multiOptions" className="d-flex flex-column d-sm-flex flex-sm-row">
					<div className="">
						<ul>
							{controlledFields.map((field, index) => {
								return (
									<div className="field">
											<label htmlFor="name">Option {index + 1}</label>
											<br />
											<p className="help">
												Display name for your {supportedFields[type]}{" "}
												option.
											</p>
											<div
												className={`${
													errors.name ? "field has-error" : "field"
												} d-flex flex-column d-sm-flex flex-sm-row me-sm-5`}
											>
												<div 
													className="me-sm-5"
													name="multiples"
												>
													<input
														{...register(`fieldArray.${index}.option`)}
														name="option"
														// defaultValue={field.fieldArray[index]}
														placeholder="Option Name"
														type="text"
														onChange={(e) => {
															setApiIdGeneratorInput(e.target.value);
															clearErrors("slug");
														}}
													/>
												</div>
												<div className="me-sm-5 default-checkbox">
													<input
														{...register(`fieldArray.${index}.default`)}
														name="default"
														type="checkbox"
														// ref={register}
														defaultChecked={data.required === true}
													/>
													<label
														// htmlFor={`is-required-${id}`}
														className="checkbox is-required"
													>
														Default Value
													</label>
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
										append({
											name: Date.now()
										})	
										dispatch({
											type: "addOptionField",
											// position: positionAfter,
											data: data,
											id: data.id,
										})
									}}
								>
									<a>
										<AddIcon size="small" /> <span>Add another option</span>
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
								typeof data?.listType === "multiple"
							}
							disabled={editing}
						/>
						<label className="radio" htmlFor="integer">
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
						<label className="radio" htmlFor="one">
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
