import React, { useRef } from "react";
import { __ } from "@wordpress/i18n";
import { showError } from "../toasts";

export default function ImportFileButton({
	errorMessage,
	buttonTitle,
	callbackFn,
	buttonClasses,
	allowedMimeTypes,
}) {
	const fileUploaderRef = useRef(null);

	const form = new FormData();

	/**
	 * Import file click handler
	 * @param event
	 */
	function importClickHandler(event) {
		event.preventDefault();
		fileUploaderRef.current.click();
	}

	/**
	 * Validate the JSON can be successfully parsed
	 * @param file
	 * @returns {any}
	 */
	function isValidJson(file) {
		try {
			JSON.parse(file);
		} catch (e) {
			return false;
		}
		return true;
	}

	/**
	 * Validate file before uploading
	 * @param uploadedFile
	 */
	function validateFileUpload(uploadedFile) {
		const reader = new FileReader();
		reader.addEventListener("load", (event) => {
			if (event.target.result && isValidJson(event.target.result)) {
				form.append("file", uploadedFile);
				if (callbackFn && form) {
					callbackFn(event.target.result);
				} else {
					showError(
						errorMessage ||
							__(
								"There was an error during upload.",
								"atlas-content-modeler"
							)
					);
				}
			} else {
				showError(
					errorMessage ||
						__(
							"The file could not be read or is invalid.",
							"atlas-content-modeler"
						)
				);
			}
		});
		reader.readAsText(uploadedFile);
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
				accept={allowedMimeTypes}
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
				{buttonTitle || __("Select File", "atlas-content-modeler")}
			</button>
		</>
	);
}
