import React, { useState } from "react";
import { __, sprintf } from "@wordpress/i18n";
import { toast } from "react-toastify";

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
	const [exportData, setExportData] = useState(fileData);
	const [fileName, setFileName] = useState(fileTitle);
	const [errorMsg, setErrorMsg] = useState(errorMessage);
	const [successMsg, setSuccessMsg] = useState(successMessage);

	/**
	 * Format filename for export
	 * @returns {string}
	 */
	function getFormattedTime() {
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
	 * Export click handler for generating models .json file
	 * @param event
	 */
	function exportClickHandler(event) {
		event.preventDefault();
		callbackFn()
			.then((res) => {
				if (res) {
					const jsonData = JSON.stringify(res);
					const blob = new Blob([jsonData], { type: "text/plain" });
					const url = URL.createObjectURL(blob);
					const link = document.createElement("a");
					link.download =
						fileName || `${getFormattedTime()}-export.json`;
					link.href = url;
					link.click();

					toast(
						__(
							successMsg || "The export was successful.",
							"atlas-content-modeler"
						)
					);
				} else {
					toast(
						__(
							"There is no data to export.",
							"atlas-content-modeler"
						)
					);
				}
			})
			.catch(() => {
				toast(
					__(
						errorMsg || "There was an error exporting the data.",
						"atlas-content-modeler"
					)
				);
			});
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
