/** @jsx jsx */
import { jsx, css } from "@emotion/react";
import React, { useEffect, useState } from "react";
import Icon from "../../../../components/icons";
import {
	DarkButton,
	LinkButton,
} from "../../../../shared-assets/js/components/Buttons";
import { __ } from "@wordpress/i18n";
import TrashIcon from "../../../../components/icons/TrashIcon";

export default function MediaUploader({
	modelSlug,
	field,
	required,
	errors,
	defaultError,
}) {
	function getFieldValues() {
		const minLength = parseInt(field.minRepeatable) || 1;

		if (!field?.value) {
			return new Array(minLength).fill("", 0);
		}

		if (minLength < field.value.length) {
			return field.value;
		}

		return field.value.concat(
			new Array(minLength - field.value.length).fill("", 0)
		);
	}

	const [fieldValues, setValues] = useState(getFieldValues());

	const validFieldValues = fieldValues.filter((item) => !!item);
	const showDeleteButton = field.minRepeatable
		? fieldValues.length > field.minRepeatable
		: fieldValues.length > 1;
	const isMaxInputs =
		field.maxRepeatable && fieldValues.length === field.maxRepeatable;
	const isMinRequired =
		field.minRepeatable &&
		validFieldValues.length > 0 &&
		validFieldValues.length < field.minRepeatable;
	const isRequired = field?.required || isMinRequired;

	// state
	const [mediaUrl, setMediaUrl] = useState("");
	const [value, setValue] = useState(field.value);
	const { allowedTypes } = field;

	// local
	const imageRegex = /\.(gif|jpe?g|tiff?|png|webp|bmp)$/i;

	// load media file from wp.media service
	useEffect(() => {
		wp.media
			.attachment(value)
			.fetch()
			.then(() => {
				setMediaUrl(wp.media.attachment(value).get("url"));
			});
	}, []);

	/**
	 * Reset values
	 */
	function deleteImage(e) {
		e.preventDefault();
		setValue("");
		setMediaUrl("");
	}

	/**
	 * Get file extension
	 * @param file
	 * @returns {any}
	 */
	function getFileExtension(file) {
		return file.split(".").pop();
	}

	/**
	 * Get long extension for provided short file extension
	 *
	 * @returns {*[]}
	 */
	function getAllowedTypesLongExtension() {
		const fieldAllowedTypes = allowedTypes.split(",");
		let fieldAllowedTypesLongExtensions = [];

		fieldAllowedTypes.forEach((type) => {
			for (const item in atlasContentModelerFormEditingExperience.allowedMimeTypes) {
				const fileExtensionRegex = new RegExp(type, "gi");

				if (fileExtensionRegex.test(item)) {
					fieldAllowedTypesLongExtensions.push(
						atlasContentModelerFormEditingExperience
							.allowedMimeTypes[item]
					);
				}
			}
		});

		return fieldAllowedTypesLongExtensions;
	}

	/**
	 * Format allowed types for UI display
	 * @returns {string|string}
	 */
	function getAllowedTypesForUi() {
		return allowedTypes ? `${allowedTypes.split(",").join(", ")}` : "";
	}

	function getMediaButtonText(field) {
		return field?.isFeatured
			? mediaUrl
				? __("Change Featured Image", "atlas-content-modeler")
				: __("Add Featured Image", "atlas-content-modeler")
			: mediaUrl
			? __("Change Media", "atlas-content-modeler")
			: __("Upload Media", "atlas-content-modeler");
	}

	/**
	 * Click handler to use wp media uploader
	 * @param e - event
	 */
	function singleClickHandler(e) {
		e.preventDefault();

		let library = {
			order: "DESC",
			orderby: "date",
		};

		if (allowedTypes) {
			library.type = getAllowedTypesLongExtension();
		}

		if (field?.isFeatured) {
			library.type = ["image"];
		}

		const getMediaModalTitle = () => {
			const title = getMediaButtonText(field);
			if (allowedTypes) {
				return `${title} (${getAllowedTypesForUi().toUpperCase()})`;
			}

			return title;
		};

		// If the media frame already exists, reopen it.
		if (media) {
			media.open();
			return;
		}

		const media = wp.media({
			title: getMediaModalTitle(),
			multiple: false,
			frame: "select",
			library: library,
			button: {
				text: __("Done", "atlas-content-modeler"),
			},
		});

		media.open().on("select", function () {
			const uploadedMedia = media.state().get("selection").first();
			setValue(uploadedMedia.attributes.id);
			setMediaUrl(uploadedMedia.attributes.url);
		});
	}

	/**
	 * Click handler to use wp media uploader for repeater
	 * @param e - event
	 */
	function multiClickHandler(e) {
		e.preventDefault();

		let library = {
			order: "DESC",
			orderby: "date",
		};

		if (allowedTypes) {
			library.type = getAllowedTypesLongExtension();
		}

		if (field?.isFeatured) {
			library.type = ["image"];
		}

		const getMediaModalTitle = () => {
			const title = getMediaButtonText(field);
			if (allowedTypes) {
				return `${title} (${getAllowedTypesForUi().toUpperCase()})`;
			}

			return title;
		};

		// If the media frame already exists, reopen it.
		if (media) {
			media.open();
			return;
		}

		const media = wp.media({
			title: getMediaModalTitle(),
			multiple: true,
			frame: "select",
			library: library,
			button: {
				text: __("Done", "atlas-content-modeler"),
			},
		});

		media.open().on("select", function () {
			const imgArr = [];
			const uploadedMedia = media
				.state()
				.get("selection")
				.map(function (attachment) {
					imgArr.push({
						id: attachment.attributes.id,
						url: attachment.attributes.url,
					});
				});
			setValues(imgArr);
		});
	}

	if (field.isRepeatable) {
		return (
			<>
				<div className={"field"}>
					<label
						htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
					>
						{field.name}
					</label>
					{field?.required && <p className="required">*Required</p>}
					{field?.description && (
						<p className="help mb-2">
							{__(field.description, "atlas-content-modeler")}
						</p>
					)}

					<fieldset>
						<div id="repeaterText" className="text-table flex-row">
							<div className="repeater-text-field flex-row">
								<ul>
									<table key="1" className="table mt-2">
										<tbody>
											{fieldValues.map((item, index) => {
												return (
													<tr
														key={index}
														className={`field text-repeater-container-single d-flex mt-1 flex-fill flex-row`}
													>
														<div
															className={`field d-flex flex-row repeater-input mt-0 flex-fill d-lg-flex`}
														>
															<div
																className="me-lg-1 repeater-input-container flex-fill"
																name="repeaters"
															>
																<img
																	className="img img-thumbnail"
																	src={
																		item.url
																	}
																	width="100"
																	hieght="100"
																/>

																<DarkButton
																	name={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}]`}
																	onClick={(
																		event
																	) => {
																		// Update the value of the item.
																		const newValue =
																			event
																				.currentTarget
																				.value;
																		setValues(
																			(
																				oldValues
																			) => {
																				let newValues = [
																					...oldValues,
																				];
																				newValues[
																					index
																				] = newValue;
																				return newValues;
																			}
																		);
																	}}
																>
																	{getMediaButtonText(
																		field
																	)}
																</DarkButton>
															</div>
															<div
																className={`value[${index}].remove-container p-2 me-sm-1`}
															>
																{showDeleteButton && (
																	<button
																		className="remove-item tertiary no-border"
																		onClick={(
																			event
																		) => {
																			event.preventDefault();
																			// Removes the value at the given index.
																			setValues(
																				(
																					currentValues
																				) => {
																					const newValues = [
																						...currentValues,
																					];
																					newValues.splice(
																						index,
																						1
																					);
																					return newValues;
																				}
																			);
																		}}
																	>
																		<a
																			aria-label={__(
																				"Remove item.",
																				"atlas-content-modeler"
																			)}
																		>
																			<TrashIcon size="small" />{" "}
																		</a>
																	</button>
																)}
															</div>
														</div>
													</tr>
												);
											})}
											<tr className="flex add-container">
												<LinkButton
													css={css`
														color: #d21b46;
														&:focus,
														&:hover {
															color: #a51537;
														}
													`}
													href="#"
													onClick={(e) =>
														multiClickHandler(e)
													}
												>
													{__(
														"+ Add Media",
														"atlas-content-modeler"
													)}
												</LinkButton>
												{allowedTypes && (
													<p className="text-muted">
														{__(
															"Accepts file types",
															"atlas-content-modeler"
														)}
														:{" "}
														{getAllowedTypesForUi().toUpperCase()}
													</p>
												)}
											</tr>
										</tbody>
									</table>
								</ul>
							</div>
						</div>
					</fieldset>

					<span className="error">
						<Icon type="error" />
						<span role="alert">
							{__(
								"This field is required",
								"atlas-content-modeler"
							)}
						</span>
					</span>

					<div>
						{mediaUrl && (
							<>
								<div className="media-item">
									{imageRegex.test(mediaUrl) ? (
										<img
											onClick={(e) =>
												singleClickHandler(e)
											}
											src={mediaUrl}
											alt={field.name}
										/>
									) : (
										<a
											href={mediaUrl}
											target="_blank"
											rel="noopener noreferrer"
										>
											[
											{getFileExtension(
												mediaUrl
											).toUpperCase()}
											] {mediaUrl}
										</a>
									)}
								</div>
							</>
						)}

						<input
							type="text"
							name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
							id={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
							className="hidden"
							required={required}
							onChange={() => {}} // Prevents “You provided a `value` prop to a form field without an `onChange` handler.”
							value={value} // Using defaultValue here prevents images from updating on save.
						/>
					</div>
				</div>
			</>
		);
	}

	return (
		<>
			<label
				htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
			>
				{field.name}
			</label>
			{field?.required && <p className="required">*Required</p>}
			{field?.description && (
				<p className="help mb-2">
					{__(field.description, "atlas-content-modeler")}
				</p>
			)}
			<div>
				{mediaUrl && (
					<>
						<div className="media-item">
							{imageRegex.test(mediaUrl) ? (
								<img
									onClick={(e) => singleClickHandler(e)}
									src={mediaUrl}
									alt={field.name}
								/>
							) : (
								<a
									href={mediaUrl}
									target="_blank"
									rel="noopener noreferrer"
								>
									[{getFileExtension(mediaUrl).toUpperCase()}]{" "}
									{mediaUrl}
								</a>
							)}
						</div>
					</>
				)}

				<div className="d-flex flex-row align-items-center media-btns">
					<div>
						<DarkButton
							data-testid="feature-image-button"
							style={{ marginTop: "5px" }}
							onClick={(e) => singleClickHandler(e)}
						>
							{getMediaButtonText(field)}
						</DarkButton>

						{allowedTypes && (
							<p className="text-muted">
								{__(
									"Accepts file types",
									"atlas-content-modeler"
								)}
								: {getAllowedTypesForUi().toUpperCase()}
							</p>
						)}
					</div>

					{mediaUrl && (
						<LinkButton
							css={css`
								color: #d21b46;
								&:focus,
								&:hover {
									color: #a51537;
								}
							`}
							href="#"
							onClick={(e) => deleteImage(e)}
						>
							{field?.isFeatured
								? __(
										"Remove Featured Image",
										"atlas-content-modeler"
								  )
								: __("Remove Media", "atlas-content-modeler")}
						</LinkButton>
					)}
				</div>

				<input
					type="text"
					name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
					id={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
					className="hidden"
					required={required}
					onChange={() => {}} // Prevents “You provided a `value` prop to a form field without an `onChange` handler.”
					value={value} // Using defaultValue here prevents images from updating on save.
				/>
				<span className="error">
					<Icon type="error" />
					<span role="alert">
						{__("This field is required", "atlas-content-modeler")}
					</span>
				</span>
			</div>
		</>
	);
}
