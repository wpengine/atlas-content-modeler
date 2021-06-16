import React, { useEffect, useState } from "react";
import Icon from "../../../../components/icons";
import { sprintf, __ } from "@wordpress/i18n";

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
				title: mediaUrl
					? __("Change Media", "atlas-content-modeler")
					: __("Upload Media", "atlas-content-modeler"),
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
					<div>
						<input
							type="button"
							className="button button-primary button-large"
							style={{ marginTop: "5px" }}
							defaultValue={
								mediaUrl
									? __(
											"Change Media",
											"atlas-content-modeler"
									  )
									: __(
											"Upload Media",
											"atlas-content-modeler"
									  )
							}
							onClick={(e) => clickHandler(e)}
						/>
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
					<span role="alert">
						{__("This field is required", "atlas-content-modeler")}
					</span>
				</span>
			</div>
		</>
	);
}
