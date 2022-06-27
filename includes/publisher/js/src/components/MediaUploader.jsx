/** @jsx jsx */
import { jsx, css } from "@emotion/react";
import React, { useEffect, useState } from "react";
import Icon from "../../../../components/icons";
import {
	LinkButton,
	DarkButton,
} from "../../../../shared-assets/js/components/Buttons";
import { __ } from "@wordpress/i18n";

export default function MediaUploader({ modelSlug, field, required }) {
	const [fieldValues, setValues] = useState([]);

	// state
	const [mediaUrl, setMediaUrl] = useState("");
	const [value, setValue] = useState(field.value);
	const { allowedTypes } = field;

	// local
	const imageRegex = /\.(gif|jpe?g|tiff?|png|webp|bmp)$/i;
	const audioRegex = /\.(mp3|ogg|wav|m4a)$/i;
	const fileRegex = /\.(pdf|doc?x|ppt?x|pps?x|odt|sls?x|psd|txt)$/i;
	const multimediaRegex = /\.(mp4|m4v|mov|wmv|avi|mpeg|ogv|3gp|3g2)$/i;

	// load media file from wp.media service
	useEffect(() => {
		// get ids and values and set on defaultValues
		if (!field.isRepeatableMedia) {
			wp.media
				.attachment(value)
				.fetch()
				.then(() => {
					setMediaUrl(wp.media.attachment(value).get("url"));
				});
		} else {
			setMultiMediaUrls();
		}
	}, []);

	/**
	 * Check file type for icon display
	 * @param {*} item
	 * @param {*} type
	 * @returns
	 */
	function getFileTypeImageType(item, type) {
		switch (type) {
			case "audio":
				return audioRegex.test(item.url);
			case "file":
				return fileRegex.test(item.url);
			case "multimedia":
				return multimediaRegex.test(item.url);
			case "image":
				return imageRegex.test(item.url);
			default:
				return (
					!audioRegex.test(item.url) &&
					!fileRegex.test(item.url) &&
					!multimediaRegex.test(item.url) &&
					!imageRegex.test(item.url)
				);
		}
	}

	/**
	 * Reset values - single
	 */
	function deleteImage(e) {
		e.preventDefault();
		setValue("");
		setMediaUrl("");
	}

	/**
	 * Sets urls for repeater media field
	 */
	function setMultiMediaUrls() {
		if (Array.isArray(value)) {
			function addUrl(index, item, url) {
				setValues((fieldValues) => [
					...fieldValues,
					{
						url: url,
						id: +item,
					},
				]);
			}

			value.forEach(function (item, i) {
				wp.media
					.attachment(item)
					.fetch()
					.then(() => {
						addUrl(i, item, wp.media.attachment(item).get("url"));
					});
			});
		}
	}

	/**
	 * Get file extension
	 * @param file
	 * @returns {any}
	 */
	function getFileExtension(file) {
		return file ? file.split(".").pop() : "";
	}

	/**
	 * Get file name
	 * @param file
	 * @returns {any}
	 */
	function getFileName(file) {
		return file ? file.split("/").pop() : "";
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

		const selectedIds = fieldValues.map((item) => {
			return item.id;
		});

		const media = wp.media({
			title: getMediaModalTitle(),
			multiple: "add",
			frame: "select",
			library: library,
			selected: selectedIds,
			button: {
				text: __("Done", "atlas-content-modeler"),
			},
		});

		media.on("open", function () {
			let selection = wp.media.frame.state().get("selection");

			selectedIds.forEach(function (id) {
				let attachment = wp.media.attachment(id);
				selection.add(attachment ? [attachment] : []);
			});
		});

		media.on("select", function () {
			const imgArr = [];
			media
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

		media.open();
	}

	if (field.isRepeatableMedia) {
		return (
			<>
				<div className={"field"}>
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
						<p className="help mb-2">
							{__(field.description, "atlas-content-modeler")}
						</p>
					)}

					<fieldset>
						<div id="repeaterMedia" className="text-table flex-row">
							<div className="repeater-media-field flex-row">
								<ul>
									<table key="1" className="table mt-2">
										<tbody>
											{/* Blocks submission if required and is empty */}
											{fieldValues.length === 0 &&
												field?.required && (
													<tr>
														<td>
															<input
																aria-label={__(
																	"Repeatable media",
																	"atlas-content-modeler"
																)}
																name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
																required={true}
																className="visually-hidden"
															/>
														</td>
													</tr>
												)}

											{fieldValues.map((item, index) => {
												return (
													<tr
														key={index}
														className={`field media-repeater-container-single d-flex mt-0 flex-fill flex-row`}
													>
														<td
															className={`field d-flex flex-row repeater-input mt-0 flex-fill d-lg-flex`}
														>
															<span
																className="px-1 me-2"
																css={css`
																	font-family: "Open Sans",
																		sans-serif;
																	font-weight: bold;
																`}
															>
																{index + 1}
															</span>
															<div
																className="me-lg-1 repeater-input-container"
																name="repeaters"
															>
																<div>
																	{getFileTypeImageType(
																		item,
																		"image"
																	) && (
																		<img
																			height="60"
																			width="48"
																			className="p-3"
																			onClick={(
																				e
																			) =>
																				multiClickHandler(
																					e
																				)
																			}
																			src={
																				item.url
																			}
																			alt={
																				item.url
																			}
																		/>
																	)}

																	{getFileTypeImageType(
																		item,
																		"audio"
																	) && (
																		<span className="media dashicons dashicons-media-audio"></span>
																	)}

																	{getFileTypeImageType(
																		item,
																		"file"
																	) && (
																		<span className="media dashicons dashicons-media-default"></span>
																	)}

																	{getFileTypeImageType(
																		item,
																		"default"
																	) && (
																		<span className="media dashicons dashicons-media-default"></span>
																	)}

																	{getFileTypeImageType(
																		item,
																		"multimedia"
																	) && (
																		<span className="media dashicons dashicons-media-video"></span>
																	)}

																	<input
																		type="hidden"
																		name={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}]`}
																		value={
																			fieldValues[
																				index
																			].id
																		}
																		onChange={(
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
																	/>
																</div>
															</div>
															<div
																className={`field d-flex flex-row repeater-input title-ellipsis mt-0 flex-fill d-lg-flex`}
															>
																<a
																	href={
																		item.url
																	}
																	target="_blank"
																	rel="noopener noreferrer"
																>
																	[
																	{getFileExtension(
																		item.url
																	).toUpperCase()}
																	]{" "}
																	{getFileName(
																		item.url
																	)}
																</a>
															</div>
															<div
																className={`value[${index}].remove-container p-2 me-sm-1`}
															>
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
																		<Icon
																			type="trash"
																			size="small"
																		/>{" "}
																	</a>
																</button>
															</div>
														</td>
													</tr>
												);
											})}
											<tr className="flex add-container">
												<td>
													<div>
														<LinkButton
															type="submit"
															className="mx-3"
															data-testid="media-uploader-manage-media-button"
															onClick={(e) => {
																multiClickHandler(
																	e
																);
															}}
														>
															{__(
																"+ Manage Media",
																"atlas-content-modeler"
															)}
														</LinkButton>
													</div>
													{allowedTypes && (
														<p className="help text-muted">
															{__(
																"Accepts file types",
																"atlas-content-modeler"
															)}
															:{" "}
															{getAllowedTypesForUi().toUpperCase()}
														</p>
													)}
												</td>
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
