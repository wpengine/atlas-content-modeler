import React, { useEffect, useState } from "react";
import Icon from "../../../../components/icons";

export default function MediaUploader({ modelSlug, field, required }) {
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

	/**
	 * Click handler to use wp media uploader
	 * @param e - event
	 */
	function clickHandler(e) {
		e.preventDefault();

		let library = {
			order: "DESC",
			orderby: "date",
		};

		if (allowedTypes) {
			library.type = getAllowedTypesLongExtension();
		}

		const getMediaModalTitle = () => {
			const title = mediaUrl ? "Change Media" : "Upload Media";
			if (allowedTypes) {
				return `${title} (${getAllowedTypesForUi()})`;
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
				text: "Done",
			},
		});

		media.open().on("select", function () {
			const uploadedMedia = media.state().get("selection").first();
			setValue(uploadedMedia.attributes.id);
			setMediaUrl(uploadedMedia.attributes.url);
		});
	}

	return (
		<>
			<label
				htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
			>
				{field.name}
			</label>
			<input
				type="text"
				name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
				id={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
				className="hidden"
				readOnly={true}
				value={value}
			/>
			<div>
				{mediaUrl && (
					<>
						<div className="media-item">
							{imageRegex.test(mediaUrl) ? (
								<img
									onClick={(e) => clickHandler(e)}
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
						<input
							type="button"
							className="button button-primary button-large"
							style={{ marginTop: "5px" }}
							defaultValue={
								mediaUrl ? "Change Media" : "Upload Media"
							}
							onClick={(e) => clickHandler(e)}
						/>

						{allowedTypes && (
							<p className="text-muted">
								Accepts file types:{" "}
								{getAllowedTypesForUi().toUpperCase()}
							</p>
						)}
					</div>

					{mediaUrl && (
						<div>
							<a
								href="#"
								style={{ marginLeft: "20px" }}
								className="btn-delete"
								onClick={(e) => deleteImage(e)}
							>
								Remove Media
							</a>
						</div>
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
					<span role="alert">This field is required</span>
				</span>
			</div>
		</>
	);
}
