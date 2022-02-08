import React from "react";
import { __ } from "@wordpress/i18n";
import { showSuccess, showError } from "../toasts";
import { DarkButton } from "../../../../shared-assets/js/components/Buttons";

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
	 * Get data and type ready for export
	 * @param data
	 * @returns {{data, type: string}|{data: string, type: string}}
	 */
	function processDataForExport(data) {
		switch (fileType) {
			case "json":
				return {
					data: JSON.stringify(data),
					type: "application/json",
				};
			default:
				return { data: data, type: "text/plain" };
		}
	}

	/**
	 * Export final file - json
	 * @param data
	 */
	function exportFile(data) {
		const readyData = processDataForExport(data);
		const blob = new Blob([readyData.data], {
			type: readyData.type,
		});
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
		<DarkButton
			data-testid="export-models-button"
			onClick={(event) => exportClickHandler(event)}
		>
			{__(buttonTitle || "Export", "atlas-content-modeler")}
		</DarkButton>
	);
}
