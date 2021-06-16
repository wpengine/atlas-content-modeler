import React, { useState } from "react";
import MediaUploader from "./MediaUploader";
import RichTextEditor from "./RichTextEditor";
import Icon from "../../../../components/icons";

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

		if (field.type === "text") {
			if (event.target.validity.tooShort) {
				error = `Minimum length is ${event.target.minLength}.`;
			} else if (event.target.validity.tooLong) {
				error = `Maximum length is ${event.target.maxLength}.`;
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

function onOptionChange(field, changedIndex) {
	field.value.map((item, index) => {
		if (index === changedIndex) {
			remove(field.value[changedIndex]);
		}
	});
	return console.log(field);
}

function fieldMarkup(field, modelSlug, errors, validate) {
	modelSlug = modelSlug.toLowerCase();

	switch (field.type) {
		case "media":
			return (
				<MediaUploader
					field={field}
					modelSlug={modelSlug}
					required={field.required}
				/>
			);
		case "text":
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
					<br />
					{field?.inputType === "multi" ? (
						<textarea {...textProps} />
					) : (
						<input {...textProps} />
					)}

					<span className="error">
						<Icon type="error" />
						<span role="alert">
							{errors[field.slug] ?? defaultError}
						</span>
					</span>
				</>
			);
		case "number":
			return (
				<>
					<label
						htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
					>
						{field.name}
					</label>
					<input
						type={`${field.type}`}
						name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
						id={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
						defaultValue={field.value}
						required={field.required}
						onChange={(event) => validate(event, field)}
						min={field?.minValue}
						max={field?.maxValue}
						step={field?.step}
					/>
					<span className="error">
						<Icon type="error" />
						<span role="alert">{defaultError}</span>
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

		case "multiOption":
			console.log(field);
			if (field.listType === "multi") {
				return (
					<>
						<label
							htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
						>
							{field.name}
						</label>
						{/* <p className="help">
							Choose any of the below options.
						</p> */}
						{field.required && (
							<p className="help">This field is required.</p>
						)}
						{field.choices.map((item, index) => {
							// console.log(field.value[index]);
							const [checked, setChecked] = useState(
								field.value[index]
							);
							return (
								<label
									className="check-container multi-check-container"
									// htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
								>
									{item.name}
									<input
										type="checkbox"
										name={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}][${item.name}]`}
										id={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}][${item.name}]`}
										placeholder="Option Name"
										checked={checked}
										defaultChecked={item.default}
										onChange={(event) => {
											event.preventDefault();
											setChecked(!checked);
										}}
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
					</>
				);
			}
			if (field.listType === "one") {
				return (
					<>
						<label
							htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
						>
							{field.name}
						</label>
						{/* <p className="help">
							Choose one of the below options.
						</p> */}
						{field.required && (
							<p className="help">This field is required.</p>
						)}
						{field.choices.map((item, index) => {
							console.log(field.value);
							return (
								<label
									className="radio-container"
									// htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
								>
									{item.name}
									<input
										type="radio"
										name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
										id={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
										value={item.name}
										// checked={}
										defaultChecked={
											field.value && item.default
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
					</>
				);
			}

		default:
			return `TODO: ${field.type} fields`;
	}
}
