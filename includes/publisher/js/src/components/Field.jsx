import React, { useState, useRef, useEffect } from "react";
import MediaUploader from "./MediaUploader";
import RichTextEditor from "./RichTextEditor";
import Icon from "../../../../components/icons";
import { sprintf, __ } from "@wordpress/i18n";
import RelationshipModal from "./RelationshipModal";
const { wp } = window;
const { apiFetch } = wp;

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

function fieldMarkup(field, modelSlug, errors, validate) {
	switch (field.type) {
		case "relationship":
			const [
				editSingleRelModalIsOpen,
				setEditSingleRelModalIsOpen,
			] = useState(false);
			const [relatedContent, setRelatedContent] = useState();

			/**
			 * Retrieves related content information for display.
			 *
			 * @param {object} field
			 * @returns {object}
			 */
			function getRelatedTitles(field) {
				const { models } = atlasContentModelerFormEditingExperience;
				let values = field.value.split(",");
				let query = "";

				values.forEach((relationship) => {
					let lead = query === "" ? "?" : "&";
					query = query + `${lead}include[]=${relationship}`;
				});

				const endpoint = `/wp/v2/${
					models[field.reference].wp_rest_base
				}/${query}`;

				const params = {
					path: endpoint,
					parse: false, // So the response status and headers are available.
				};

				return apiFetch(params).then((response) => {
					if (response.status !== 200) {
						console.error(
							sprintf(
								__(
									/* translators: %s The HTTP error code, such as 200. */
									"Received %s error when fetching entries.",
									"atlas-content-modeler"
								),
								response.status
							)
						);
						return;
					}

					return response.json();
				});
			}

			/**
			 * Gets entries whenever the state of 'page' changes.
			 * Caches those entries in the pagedEntries object, keyed by page.
			 */
			useEffect(() => {
				if (field.value !== "") {
					// let's not make the call if we don't have to.
					getRelatedTitles(field).then((entries) => {
						setRelatedContent(() => {
							return entries;
						});
					});
				}
			}, [field.value]);

			function relationshipClickHandler(
				e,
				field,
				modelSlug,
				errors,
				validate
			) {
				setEditSingleRelModalIsOpen(true);
			}

			return (
				<>
					<label
						htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
					>
						{field.name}
					</label>
					{field.value &&
						relatedContent?.map((entry) => {
							const { id, title } = entry;
							return (
								<>
									<div className="app-card">
										<section className="card-content">
											<ul className="model-list">
												<li>
													<div className="relation-model-card flex-wrap d-flex flex-column d-sm-flex flex-sm-row">
														<span className="flex-item mb-3 mb-sm-0 pr-1">
															<p className="label">
																Linked Reference
															</p>
															<p className="value">
																<strong>
																	{
																		title.rendered
																	}
																</strong>
															</p>
														</span>
													</div>
													{field.value && (
														<input
															name={`atlas-content-modeler[${modelSlug}][${field.slug}][relationshipEntryId]`}
															value={field.value}
															type="hidden"
														/>
													)}
												</li>
											</ul>
										</section>
									</div>
								</>
							);
						})}
					<div className="d-flex flex-row align-items-center media-btns">
						<button
							className="button button-primary button-large"
							style={{ marginTop: "5px" }}
							id={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
							onClick={(e) => {
								e.preventDefault();
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
							{field.value
								? __(
										"Link New Reference",
										"atlas-content-modeler"
								  )
								: __("Link Reference", "atlas-content-modeler")}
						</button>
					</div>
					<RelationshipModal
						field={field}
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
					{field?.required && <p className="required">*Required</p>}
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
