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
		setFile(uploadedFile);

		// read file
		const reader = new FileReader();
		reader.addEventListener("load", (event) => {
			if (event.target.result && isValidJson(event.target.result)) {
				form.append("file", uploadedFile);
				if (callbackFn && form) {
					callbackFn(event.target.result)
						.then((res) => {
							if (res) {
								showSuccess(
									__(
										successMessage ||
											"The file was successfully uploaded.",
										"atlas-content-modeler"
									)
								);
							} else {
								showError(
									__(
										errorMessage ||
											"The file upload failed.",
										"atlas-content-modeler"
									)
								);
							}
						})
						.catch(() => {
							showError(
								__(
									errorMessage ||
										"There was an error uploading the file.",
									"atlas-content-modeler"
								)
							);
						});
				}
			} else {
				showError(
					__(
						errorMessage ||
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
				{__(buttonTitle || "Select File", "atlas-content-modeler")}
			</button>
		</>
	);
}
