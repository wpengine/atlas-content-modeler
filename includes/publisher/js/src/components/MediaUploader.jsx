import React, { useEffect, useState } from "react";
import Icon from "../../../../components/icons";

export default function MediaUploader({ modelSlug, field, required }) {
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
			<label
				htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
			>
				{field.name}
			</label>
			{field?.required && <p className="required">*Required</p>}
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
								<a href={mediaUrl}>
									[{getFileExtension(mediaUrl).toUpperCase()}]{" "}
									{mediaUrl}
								</a>
							)}
						</div>
					</>
				)}

				<div className="d-flex flex-row align-items-center media-btns">
					<input
						type="button"
						className="button button-primary button-large"
						defaultValue={
							mediaUrl ? "Change Media" : "Upload Media"
						}
						onClick={(e) => clickHandler(e)}
					/>

					{mediaUrl && (
						<a
							href="#"
							style={{ marginLeft: "20px" }}
							className="btn-delete"
							onClick={(e) => deleteImage(e)}
						>
							Remove Media
						</a>
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
