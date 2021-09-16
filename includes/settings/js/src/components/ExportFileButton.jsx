import React from "react";
import { __ } from "@wordpress/i18n";
import { showSuccess, showError } from "../toasts";

export default function ExportFileButton({
	fileData,
	fileTitle,
	successMessage,
	errorMessage,
	buttonTitle,
	callbackFn,
	buttonClasses,
	fileType,
}) {
	/**
	 * Export final file - json
	 * @param data
	 */
	function exportFile(data) {
		const jsonData = JSON.stringify(data);
		const blob = new Blob([jsonData], { type: fileType || "text/plain" });
		const url = URL.createObjectURL(blob);
		const link = document.createElement("a");
		link.download = fileTitle;
		link.href = url;
		link.click();

		showSuccess(
			__(
				successMessage || "The export was successful.",
				"atlas-content-modeler"
			)
		);
	}

	/**
	 * Export click handler for generating models .json file
	 * @param event
	 */
	function exportClickHandler(event) {
		event.preventDefault();

		if (callbackFn && !fileData) {
			callbackFn()
				.then((res) => {
					if (res) {
						exportFile(res);
					} else {
						showError(
							__(
								"There is no data to export.",
								"atlas-content-modeler"
							)
						);
					}
				})
				.catch(() => {
					showError(
						__(
							errorMessage ||
								"There was an error exporting the data.",
							"atlas-content-modeler"
						)
					);
				});
		} else if (fileData) {
			exportFile(fileData);
		}
	}

	return (
		<button
			className={
				buttonClasses || "button dark-blue button-primary link-button"
			}
			onClick={(event) => exportClickHandler(event)}
		>
			{__(buttonTitle || "Export", "atlas-content-modeler")}
		</button>
	);
}
