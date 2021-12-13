import React, { useState, useRef } from "react";
import MediaUploader from "./MediaUploader";
import RichTextEditor from "./RichTextEditor";
import Relationship from "./relationship";
import Icon from "acm-icons";
import { sprintf, __ } from "@wordpress/i18n";

const defaultError = "This field is required";

export default function Field(props) {
	const { field, modelSlug } = props;
	const [errors, setErrors] = useState({});

	/**
	 * Adjusts the custom error feedback messages displayed below fields based
	 * on the HTML5 validation failure type.
	 *
	 * @param {object} event onChange
	 * @param {object} field
	 */
	function validate(event, field) {
		if (event.target.validity.valid) {
			return;
		}

		let error = defaultError;

		if (field.type === "number") {
			if (event.target.validity.rangeOverflow) {
				error = sprintf(
					__("Maximum value is %s.", "atlas-content-modeler"),
					event.target.max.toString()
				);
			} else if (event.target.validity.rangeUnderflow) {
				error = sprintf(
					__("Minimum value is %s.", "atlas-content-modeler"),
					event.target.min.toString()
				);
			} else if (event.target.validity.stepMismatch) {
				error = sprintf(
					__(
						"Value must be a multiple of %s.",
						"atlas-content-modeler"
					),
					event.target.step.toString()
				);
			}
		}

		if (field.type === "text") {
			if (event.target.validity.tooShort) {
				error = sprintf(
					__("Minimum length is %d.", "atlas-content-modeler"),
					event.target.minLength
				);
			} else if (event.target.validity.tooLong) {
				error = sprintf(
					__("Maximum length is %d.", "atlas-content-modeler"),
					event.target.maxLength
				);
			}
		}

		setErrors({ ...errors, [field.slug]: error });
	}

	return (
		<>
			<div
				id={`field-${field.slug}`}
				className={`field d-flex flex-column ${field.type}`}
			>
				{fieldMarkup(field, modelSlug, errors, validate)}
			</div>
		</>
	);
}

function SingleTextField({ field, modelSlug, errors, validate }) {
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

function RepeatingTextField({ field, modelSlug, errors, validate }) {
	const [values, setValues] = useState(field?.value || { unsaved1: "" });

	return (
		<>
			<label>{field.name}</label>
			{Object.entries(values ?? {}).map(([key, value], index) => {
				return (
					<div key={index} className="d-flex">
						<input
							name={`atlas-content-modeler[${modelSlug}][${field.slug}][${key}]`}
							placeholder={sprintf(
								// translators: the item number, such as 1.
								__("Item %s", "atlas-content-modeler"),
								index + 1
							)}
							type="text"
							onKeyPress={(event) => {
								if (event.key === "Enter") {
									event.preventDefault();
								}
							}}
							value={value}
							onChange={(event) => {
								// Update the value of the item.
								const newValue = event.currentTarget.value;
								setValues((oldValues) => {
									let newValues = { ...oldValues };
									newValues[key] = newValue;
									return newValues;
								});
							}}
						/>
						{Object.values(values).length > 1 && (
							<button
								className="remove-option tertiary no-border"
								onClick={(event) => {
									event.preventDefault();
									// Remove the value.
									setValues((currentValues) => {
										const newValues = { ...currentValues };
										delete newValues[key];
										return newValues;
									});
								}}
							>
								<a>
									<span>
										{__("Remove", "atlas-content-modeler")}
									</span>
								</a>
							</button>
						)}
					</div>
				);
			})}

			<button
				className="add-option tertiary no-border"
				onClick={(event) => {
					event.preventDefault();
					// Adds a new empty value to display another text field.
					setValues((currentValues) => {
						const newValues = { ...currentValues };
						newValues[
							`unsaved${Object.values(currentValues)?.length + 1}`
						] = "";
						return newValues;
					});
				}}
			>
				<a>
					<span>{__("Add another", "atlas-content-modeler")}</span>
				</a>
			</button>
		</>
	);
}

function fieldMarkup(field, modelSlug, errors, validate) {
	switch (field.type) {
		case "relationship":
			return (
				<Relationship
					field={field}
					modelSlug={modelSlug}
					required={field.required}
				/>
			);
		case "media":
			return (
				<MediaUploader
					field={field}
					modelSlug={modelSlug}
					required={field.required}
				/>
			);
		case "text":
			if (field?.isRepeatable) {
				return (
					<RepeatingTextField
						field={field}
						modelSlug={modelSlug}
						errors={errors}
						validate={validate}
					/>
				);
			}

			return (
				<SingleTextField
					field={field}
					modelSlug={modelSlug}
					errors={errors}
					validate={validate}
				/>
			);
		case "number":
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
						<span role="alert">
							{errors[field.slug] ?? defaultError}
						</span>
					</span>
				</>
			);
		case "date": // @todo split this out for proper browser and datepicker support
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
						type={`${field.type}`}
						name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
						id={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
						defaultValue={field.value}
						required={field.required}
					/>
					<span className="error">
						<Icon type="error" />
						<span role="alert">{defaultError}</span>
					</span>
				</>
			);

		case "richtext":
			return (
				<RichTextEditor
					field={field}
					modelSlug={modelSlug}
					errors={errors}
					validate={validate}
					defaultError={defaultError}
				/>
			);

		case "boolean":
			const [checked, setChecked] = useState(field.value === "on");
			return (
				<>
					{field?.description && (
						<p className="help mb-3">{field.description}</p>
					)}
					<label
						className="check-container"
						htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
					>
						{field.name}
						<input
							type="checkbox"
							name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
							id={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
							checked={checked}
							onChange={(event) => setChecked(!checked)}
							required={field.required}
						/>
						<span className="error">
							<Icon type="error" />
							<span role="alert">{defaultError}</span>
						</span>
						{/* span is used for custom checkbox styling purposes */}
						<span className="checkmark"></span>
					</label>
				</>
			);

		case "multipleChoice":
			if (field.listType === "multiple") {
				return (
					<fieldset>
						<legend>{field.name}</legend>
						{field?.description && (
							<p className="help mb-0">{field.description}</p>
						)}
						{field.choices.map((item, index) => {
							return (
								<label
									key={index}
									className="check-container multi-check-container"
									htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}][${item.slug}]`}
								>
									{item.name}
									<input
										type="checkbox"
										name={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}][${item.slug}]`}
										id={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}][${item.slug}]`}
										placeholder="Option Name"
										value={item.slug}
										defaultChecked={
											field.value &&
											field.value.some(
												(slug) => slug === item.slug
											)
										}
									/>
									<span className="error">
										<Icon type="error" />
										<span role="alert">{defaultError}</span>
									</span>
									{/* span is used for custom checkbox styling purposes */}
									<span className="checkmark"></span>
								</label>
							);
						})}
					</fieldset>
				);
			}
			if (field.listType === "single") {
				return (
					<fieldset>
						<legend>{field.name}</legend>
						{field?.description && (
							<p className="help mb-0">{field.description}</p>
						)}
						{field.choices.map((item, index) => {
							return (
								<label className="radio-container" key={index}>
									{item.name}
									<input
										type="radio"
										name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
										id={`atlas-content-modeler[${modelSlug}][${field.slug}][${item.name}]`}
										value={item.slug}
										defaultChecked={
											field.value[0] === item.slug
										}
									/>
									<span className="error">
										<Icon type="error" />
										<span role="alert">{defaultError}</span>
									</span>
									{/* span is used for custom radio styling purposes */}
									<span className="radio-select"></span>
								</label>
							);
						})}
					</fieldset>
				);
			}

		default:
			return `TODO: ${field.type} fields`;
	}
}
