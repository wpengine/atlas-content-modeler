import React, { useState, useRef, useCallback } from "react";
import MediaUploader from "./MediaUploader";
import RichText from "./RichText";
import Relationship from "./relationship";
import Text from "./Text";
import Email from "./Email";
import Number from "./Number";
import Date from "./Date";
import Icon from "acm-icons";
import { sprintf, __ } from "@wordpress/i18n";

const { apiFetch } = wp;

const defaultError = "This field is required";
const integerRegex = /^[-+]?\d+/g;
const decimalRegex = /^\-?(\d+\.?\d*|\d*\.?\d+)$/g;
const debounceEmailUniqueDelay = 350;

function debounce(func, wait, immediate) {
	let timeout;
	return function () {
		let context = this,
			args = arguments;
		let later = function () {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		let callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
}

export default function Field(props) {
	const { field, modelSlug, first } = props;
	const [errors, setErrors] = useState({});

	const debounceCheckUniqueEmail = useCallback(
		debounce(apiCheckUniqueEmail, debounceEmailUniqueDelay),
		[]
	);

	function apiCheckUniqueEmail(slug, email, event) {
		const postId = document.getElementById("post_ID").value;
		const data = { post_id: postId, post_type: modelSlug, slug, email };

		return apiFetch({
			path: `/wpe/atlas/validate-unique-email`,
			method: "POST",
			_wpnonce: wpApiSettings.nonce,
			data,
		})
			.then((response) => {
				if (response?.data) {
					event.target.setCustomValidity("");
					return response.data;
				}
			})
			.catch((error) => {
				if (
					error?.code === "acm_invalid_unique_email" &&
					error?.message
				) {
					const errorMessage = __("Field must be unique.");
					event.target.setCustomValidity(errorMessage);
					setErrors({ ...errors, [field.slug]: errorMessage });
				}
			});
	}

	/**
	 * Adjusts the custom error feedback messages displayed below fields based
	 * on the HTML5 validation failure type.
	 *
	 * @param {object} event onChange
	 * @param {object} field
	 */
	function validate(event, field) {
		let error = defaultError;

		if (field.type === "number") {
			if (!integerRegex.test(event.target.value)) {
				error = __(
					"Value must be an integer.",
					"atlas-content-modeler"
				);
			}

			if (field.numberType === "decimal") {
				if (!decimalRegex.test(event.target.value)) {
					error = __(
						"Value must be a decimal.",
						"atlas-content-modeler"
					);
				}
			}

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

		if (field.type === "email") {
			if (!event.target.validity.valid) {
				error = __("Value must be an email.", "atlas-content-modeler");
			}

			if (field?.required && event.target.value.trim() === "") {
				error = defaultError;
			}

			if (
				field?.allowedDomains &&
				event.target.validity.patternMismatch
			) {
				error = __(
					"Email must end with an allowed domain.",
					"atlas-content-modeler"
				);
			}

			if (field?.isUnique && event.target.validity.valid) {
				debounceCheckUniqueEmail(
					field.slug,
					event.target.value.trim(),
					event
				);
			} else {
				event.target.setCustomValidity("");
			}
		}

		setErrors({ ...errors, [field.slug]: error });
	}

	return (
		<>
			<div
				id={`field-${field.slug}`}
				className={`field d-flex flex-column ${field.type}`}
				{...(first && { "data-first-field": true })}
			>
				{fieldMarkup(field, modelSlug, errors, validate)}
			</div>
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
			return (
				<Text
					field={field}
					modelSlug={modelSlug}
					errors={errors}
					validate={validate}
					defaultError={defaultError}
				/>
			);
		case "number":
			return (
				<Number
					field={field}
					modelSlug={modelSlug}
					errors={errors}
					validate={validate}
					defaultError={defaultError}
				/>
			);
		case "date": // @todo split this out for proper browser and datepicker support
			return (
				<Date
					field={field}
					modelSlug={modelSlug}
					defaultError={defaultError}
				/>
			);

		case "richtext":
			return (
				<RichText
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
		case "email":
			return (
				<Email
					field={field}
					modelSlug={modelSlug}
					errors={errors}
					validate={validate}
					defaultError={defaultError}
				/>
			);
		default:
			return `TODO: ${field.type} fields`;
	}
}
