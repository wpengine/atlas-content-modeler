import React, { useRef, useState } from "react";
import { __ } from "@wordpress/i18n";
import { showSuccess, showError } from "../toasts";

export default function ImportFileButton({
	successMessage,
	errorMessage,
	buttonTitle,
	callbackFn,
	buttonClasses,
	allowedMimeTypes,
}) {
	const [file, setFile] = useState(null);
	const fileUploaderRef = useRef(null);

	/**
	 * Import file click handler
	 * @param event
	 */
	function importClickHandler(event) {
		event.preventDefault();
		fileUploaderRef.current.click();
	}

	/**
	 * Validate file before uploading
	 * @param uploadedFile
	 */
	function validateFileUpload(uploadedFile) {
		setFile(uploadedFile);
	}

	/**
	 * Handle file change
	 * @param event
	 */
	function onChangeHandler(event) {
		event.preventDefault();
		const uploadedFile = event.target.files[0];
		if (uploadedFile) {
			validateFileUpload(uploadedFile);
		}
	}

	return (
		<>
			<input
				type="file"
				id="file"
				ref={fileUploaderRef}
				className="hidden"
				onChange={onChangeHandler}
			/>

			<button
				className={
					buttonClasses ||
					"button dark-blue button-primary link-button"
				}
				onClick={(event) => importClickHandler(event)}
			>
				{__(buttonTitle || "Select File", "atlas-content-modeler")}
			</button>
		</>
	);
}
