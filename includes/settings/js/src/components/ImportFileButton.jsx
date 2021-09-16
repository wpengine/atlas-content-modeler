import React from "react";
import { __ } from "@wordpress/i18n";
import { showSuccess, showError } from "../toasts";

export default function ImportFileButton({
	successMessage,
	errorMessage,
	buttonTitle,
	callbackFn,
	buttonClasses,
	allowedFileTypes,
}) {
	function importClickHandler(event) {
		event.preventDefault();
		console.log(event);
	}

	return (
		<button
			className={
				buttonClasses || "button dark-blue button-primary link-button"
			}
			onClick={(event) => importClickHandler(event)}
		>
			{__(buttonTitle || "Select File", "atlas-content-modeler")}
		</button>
	);
}
