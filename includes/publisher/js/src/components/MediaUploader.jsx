import React, { useEffect, useState } from "react";

export default function MediaUploader({ modelSlug, field }) {
	// state
	const [mediaUrl, setMediaUrl] = useState("");
	const [value, setValue] = useState(field.value);

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
	 * Click handler to use wp media uploader
	 * @param e - event
	 */
	function clickHandler(e) {
		e.preventDefault();

		const media = wp
			.media({
				title: mediaUrl ? "Change Media" : "Upload Media",
				multiple: false,
			})
			.open()
			.on("select", function () {
				const uploadedMedia = media.state().get("selection").first();
				setValue(uploadedMedia.attributes.id);
				setMediaUrl(uploadedMedia.attributes.url);
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
								<a href={mediaUrl}>
									[{getFileExtension(mediaUrl).toUpperCase()}]{" "}
									{mediaUrl}
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
								mediaUrl ? "Change Media" : "Upload Media"
							}
							onClick={(e) => clickHandler(e)}
						/>
					</div>

					{mediaUrl && (
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
