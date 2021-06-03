/**
 * Additional form fields for the Multi Choice field type.
 */
import React from "react";
import { useForm } from "react-hook-form";
import supportedFields from "./supportedFields";
import AddIcon from "../../../../../components/icons/AddIcon";

function MultiChoiceFields({ register, type, data, editing }) {
	const { handleSubmit, errors, setValue, clearErrors, setError } = useForm();

	return (
		<div className={editing ? "field read-only editing" : "field"}>
			<fieldset>
				<div className="d-flex flex-column d-sm-flex flex-sm-row">
					<div className="">
						<label htmlFor="name">Option 1</label>
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
							<div className="me-sm-5">
								<input
									aria-invalid={
										errors.name ? "true" : "false"
									}
									id="name"
									name="name"
									// defaultValue={storedData?.name}
									placeholder="Name"
									type="text"
									ref={register({
										required: true,
										maxLength: 50,
									})}
									onChange={(e) => {
										setApiIdGeneratorInput(e.target.value);
										clearErrors("slug");
									}}
								/>
							</div>
							<div className="me-sm-5 default-checkbox">
								<input
									name="required"
									type="checkbox"
									ref={register}
									// defaultChecked={storedData?.required === true}
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
				</div>
			</fieldset>
			<div className="field">
				<button>
					<a>
						<AddIcon size="small" /> <span>Add another option</span>
					</a>
				</button>
			</div>
			<fieldset>
				<div className="field">
					<legend>List Type</legend>
					<div className="radio-row">
						<input
							type="radio"
							id="integer"
							name="numberType"
							value="integer"
							ref={register}
							defaultChecked={
								data?.numberType === "integer" ||
								typeof data?.numberType === "undefined"
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
							id="decimal"
							name="numberType"
							value="decimal"
							ref={register}
							defaultChecked={data?.numberType === "decimal"}
							disabled={editing}
						/>
						<label className="radio" htmlFor="decimal">
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
