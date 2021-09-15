import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import { showSuccess, showError } from "../toasts";

export default function ExportFileButton({
	fileData,
	fileTitle,
	successMessage,
	errorMessage,
	buttonTitle,
	callbackFn,
	btnClasses,
}) {
	const [buttonLabel, setButtonLabel] = useState(buttonTitle);
	const [buttonClasses, setButtonClasses] = useState(btnClasses);
	const [callbackFunction, setCallbackFunction] = useState(callbackFn);
	const [exportData, setExportData] = useState(fileData);
	const [fileName, setFileName] = useState(fileTitle);
	const [errorMsg, setErrorMsg] = useState(errorMessage);
	const [successMsg, setSuccessMsg] = useState(successMessage);

	/**
	 * Format filename for export
	 * @returns {string}
	 */
	function getFormattedDateTime() {
		var today = new Date();
		var y = today.getFullYear();
		var m = today.getMonth() + 1;
		var d = today.getDate();
		var h = today.getHours();
		var mi = today.getMinutes();
		var s = today.getSeconds();
		return m + "-" + d + "-" + y + "-" + h + "-" + mi + "-" + s;
	}

	/**
	 * Export final file - json
	 * @param data
	 */
	function exportFile(data) {
		const jsonData = JSON.stringify(data);
		const blob = new Blob([jsonData], { type: "text/plain" });
		const url = URL.createObjectURL(blob);
		const link = document.createElement("a");
		link.download = fileName || `${getFormattedDateTime()}-export.json`;
		link.href = url;
		link.click();

		showSuccess(
			__(
				successMsg || "The export was successful.",
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

		if (callbackFunction && !fileData) {
			callbackFunction()
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
							errorMsg ||
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
			className={buttonClasses || "button button-primary link-button"}
			onClick={(event) => exportClickHandler(event)}
		>
			{__(buttonLabel || "Export", "atlas-content-modeler")}
		</button>
	);
}
