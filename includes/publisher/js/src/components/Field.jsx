import React, { useState } from "react";
import MediaUploader from "./MediaUploader";
import RichTextEditor from "./RichTextEditor";
import Icon from "../../../../components/icons";
import { sprintf, __ } from "@wordpress/i18n";
import RelationshipModal from "./RelationshipModal";

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

function fieldMarkup(field, modelSlug, errors, validate) {
	modelSlug = modelSlug.toLowerCase();
	console.log(field.type);

	switch (field.type) {
		case "relationship":
			const [
				editSingleRelModalIsOpen,
				setEditSingleRelModalIsOpen,
			] = useState(true);
			const [model, setModel] = useState({});

			function relationshipClickHandler(
				e,
				field,
				modelSlug,
				errors,
				validate
			) {
				setEditModelModalIsOpen(true);
			}

			return (
				<>
					<label
						htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
					>
						{field.name}
					</label>
					<div className="d-flex flex-row align-items-center media-btns">
						<button
							className="button button-primary button-large"
							style={{ marginTop: "5px" }}
							id={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
							onClick={(e) => {
								relationshipClickHandler(
									e,
									field,
									modelSlug,
									errors,
									validate
								);
							}}
						>
							{/*<Icon type="error" />*/}
							{__("Link Reference", "atlas-content-modeler")}
						</button>
					</div>
					<RelationshipModal
						model={model}
						isOpen={editSingleRelModalIsOpen}
						setIsOpen={setEditSingleRelModalIsOpen}
					/>
				</>
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
					{field?.required && <p className="required">*Required</p>}
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
					{field?.required && <p className="required">*Required</p>}
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
					{field?.required && <p className="required">*Required</p>}
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

		case "multipleChoice":
			if (field.listType === "multiple") {
				return (
					<fieldset>
						<legend>{field.name}</legend>
						{field.choices.map((item, index) => {
							return (
								<label
									key={index}
									className="check-container multi-check-container"
									htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}][${item.name}]`}
								>
									{item.name}
									<input
										type="checkbox"
										name={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}][${item.name}]`}
										id={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}][${item.name}]`}
										placeholder="Option Name"
										defaultChecked={
											field.value &&
											field.value.some(
												(name) => name == item.name
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
						{field.choices.map((item, index) => {
							return (
								<label className="radio-container" key={index}>
									{item.name}
									<input
										type="radio"
										name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
										id={`atlas-content-modeler[${modelSlug}][${field.slug}][${item.name}]`}
										value={item.name}
										defaultChecked={
											field.value === item.name
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
