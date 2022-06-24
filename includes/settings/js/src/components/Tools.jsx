import React, { useRef } from "react";
import { sprintf, __ } from "@wordpress/i18n";
import ExportFileButton from "./ExportFileButton";
import ImportFileButton from "./ImportFileButton";
import { showError, showSuccess } from "../toasts";
import { insertSidebarMenuItem } from "../utils";
import { Card } from "../../../../shared-assets/js/components/card";
import * as toolService from "../tools.service";

const { wp } = window;
const { apiFetch } = wp;

export default function Tools() {
	const fileUploaderRef = useRef(null);

	/**
	 * Checks that all field properties are valid based on the field type.
	 *
	 * @param {object} field
	 * @returns {array} Invalid properties or an empty array if no properties were invalid.
	 */
	function validateField(field) {
		toolService.validateField(field);
	}

	/**
	 * Checks that a model is valid: it does not already exist, it has all
	 * required model properties and its fields are valid.
	 *
	 * @param {object} model The new model data to validate before it is added.
	 * @param {object} storedModelData Existing stored model data.
	 * @return {object} Invalid model data, or empty object if no invalid data.
	 */
	async function validateModel(model, storedModelData) {
		toolService.validateModel(model, storedModelData);
	}

	/**
	 * Checks that all passed models are valid.
	 *
	 * @param {array} models The models in the import file to validate.
	 * @return {object} Invalid models data, or empty object if no invalid data.
	 */
	async function validateModels(models) {
		toolService.validateModels(models);
	}

	function showImportErrors(modelErrors) {
		toolService.showImportErrors(modelErrors);
	}

	/**
	 * Uploads the file data to the API.
	 */
	async function createModels(formData) {
		toolService.createModels(formData, fileUploaderRef);
	}

	/**
	 * Format filename for export
	 * @returns {string}
	 */
	function getFormattedDateTime() {
		return toolService.getFormattedDateTime();
	}

	return (
		<Card className="tools-view">
			<section className="heading flex-wrap d-flex flex-column d-sm-flex flex-sm-row">
				<h2>{__("Tools", "atlas-content-modeler")}</h2>
			</section>
			<section className="card-content">
				<div className="row">
					<div className="col-xs-10 col-lg-4 order-1 order-lg-0">
						<div className="row">
							<div className="col-xs-12">
								<h4>
									{__(
										"Import Models",
										"atlas-content-modeler"
									)}
								</h4>
								<p className="help">
									{__(
										"Select the .json file containing model/field definitions to be imported.",
										"atlas-content-modeler"
									)}
								</p>
								<ImportFileButton
									allowedMimeTypes=".json"
									callbackFn={createModels}
									fileUploaderRef={fileUploaderRef}
								/>
							</div>
							<div className="col-xs-12 mt-4">
								<h4>
									{__(
										"Export Models",
										"atlas-content-modeler"
									)}
								</h4>
								<p className="help">
									{__(
										"Exporting models will generate a .json document representing all of the existing models and fields.",
										"atlas-content-modeler"
									)}
								</p>
								<ExportFileButton
									fileTitle={`acm-models-export-${getFormattedDateTime()}.json`}
									fileType="json"
									callbackFn={getModels}
								/>
							</div>
						</div>
					</div>
				</div>
			</section>
		</Card>
	);
}
