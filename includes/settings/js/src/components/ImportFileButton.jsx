import React from "react";
import { __ } from "@wordpress/i18n";
import { showError } from "../toasts";
import { DarkButton } from "../../../../shared-assets/js/components/Buttons";

export default function ImportFileButton({
	errorMessage,
	buttonTitle,
	callbackFn,
	buttonClasses,
	allowedMimeTypes,
	fileUploaderRef,
}) {
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
	async function validateFileUpload(uploadedFile) {
		const reader = new FileReader();
		reader.addEventListener("load", async (event) => {
			if (event.target.result && isValidJson(event.target.result)) {
				form.append("file", uploadedFile);
				if (callbackFn && form) {
					await callbackFn(event.target.result);
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
	async function onChangeHandler(event) {
		event.preventDefault();
		const uploadedFile = event.target.files[0];
		if (uploadedFile) {
			await validateFileUpload(uploadedFile);
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

			<DarkButton
				data-testid="export-models-button"
				onClick={(event) => importClickHandler(event)}
			>
				{buttonTitle || __("Select File", "atlas-content-modeler")}
			</DarkButton>
		</>
	);
}
