import React, { useState } from "react";

export default function MediaUploader({ modelSlug, field }) {
	const [value, setValue] = useState(field.value);
	const imageRegex = /\.(gif|jpe?g|tiff?|png|webp|bmp)$/i;

	/**
	 * Delete input value
	 */
	function deleteImage(e) {
		e.preventDefault();
		setValue("");
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
	 * Click handler to use wp media uploader
	 * @param e - event
	 */
	function clickHandler(e) {
		e.preventDefault();

		const media = wp
			.media({
				title: value ? "Change Media" : "Upload Media",
				multiple: false,
			})
			.open()
			.on("select", function (e) {
				// This will return the selected image from the Media Uploader, the result is an object
				const uploadedMedia = media.state().get("selection").first();
				// We convert uploaded_image to a JSON object to make accessing it easier
				// Let's assign the url value to the input field
				const url = uploadedMedia.attributes.url;
				setValue(url);
			});
	}

	return (
		<>
			<label htmlFor={`wpe-content-model[${modelSlug}][${field.slug}]`}>
				{field.name}
			</label>

			<input
				type="text"
				name={`wpe-content-model[${modelSlug}][${field.slug}]`}
				id={`wpe-content-model[${modelSlug}][${field.slug}]`}
				className="hidden"
				readOnly={true}
				value={value}
			/>

			<div>
				{value && (
					<>
						<div className="media-item">
							{imageRegex.test(value) ? (
								<img
									onClick={(e) => clickHandler(e)}
									src={value}
									alt={field.name}
								/>
							) : (
								<a href={value}>
									[{getFileExtension(value).toUpperCase()}]{" "}
									{value}
								</a>
							)}
						</div>
					</>
				)}

				<div className="flex-parent flex-align-v">
					<div>
						<input
							type="button"
							className="button button-primary button-large margin-top-5"
							defaultValue={
								value ? "Change Media" : "Upload Media"
							}
							onClick={(e) => clickHandler(e)}
						/>
					</div>

					{value && (
						<div>
							<a
								href="#"
								className="btn-delete margin-left-20"
								onClick={(e) => deleteImage(e)}
							>
								Remove Media
							</a>
						</div>
					)}
				</div>
			</div>
		</>
	);
}
