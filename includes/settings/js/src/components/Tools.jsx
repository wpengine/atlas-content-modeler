import React, { useRef } from "react";
import { sprintf, __ } from "@wordpress/i18n";
import ExportFileButton from "./ExportFileButton";
import ImportFileButton from "./ImportFileButton";
import { showError, showSuccess } from "../toasts";
import { insertSidebarMenuItem } from "../utils";
import { Card } from "../../../../shared-assets/js/components/card";
const { wp } = window;
const { apiFetch } = wp;

export default function Tools() {
	const fileUploaderRef = useRef(null);

	/**
	 * Gets model export data via the REST API.
	 */
	async function getModels() {
		return apiFetch({
			path: `/wpe/atlas/content-models/`,
			method: "GET",
			_wpnonce: wpApiSettings.nonce,
		});
	}

	/**
	 * Checks that all field properties are valid based on the field type.
	 *
	 * @param {object} field
	 * @returns {array} Invalid properties or an empty array if no properties were invalid.
	 */
	function validateField(field) {
		let invalidProperties = [];

		const requiredFieldProperties = {
			all: ["type", "id", "name", "slug"],
			number: ["numberType"],
			text: ["inputType"],
			relationship: ["cardinality", "reference"],
		};

		requiredFieldProperties.all.forEach((requiredProperty) => {
			if (!field.hasOwnProperty(requiredProperty)) {
				invalidProperties.push(requiredProperty);
			}
		});

		if (field?.type && requiredFieldProperties.hasOwnProperty(field.type)) {
			requiredFieldProperties[field.type].forEach((requiredProperty) => {
				if (!field.hasOwnProperty(requiredProperty)) {
					invalidProperties.push(requiredProperty);
				}
			});
		}

		return invalidProperties;
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
		let errors = {};

		const existingModels = Object.keys(storedModelData);

		if (model?.slug && existingModels.includes(model.slug)) {
			errors["alreadyExists"] = true;
		}

		const requiredModelProperties = ["slug", "singular", "plural"];
		requiredModelProperties.forEach((requiredProperty) => {
			if (!model.hasOwnProperty(requiredProperty)) {
				if (!errors.hasOwnProperty("missingProperties")) {
					errors["missingProperties"] = [];
				}
				errors["missingProperties"].push(requiredProperty);
			}
		});

		Object.entries(model?.fields || {}).forEach(([fieldId, fieldData]) => {
			const fieldErrors = validateField(fieldData);
			if (fieldErrors?.length > 1) {
				if (!errors.hasOwnProperty("fieldErrors")) {
					errors["fieldErrors"] = [];
				}
				errors["fieldErrors"][fieldId] = fieldErrors;
			}
		});

		return errors;
	}

	/**
	 * Checks that all passed models are valid.
	 *
	 * @param {array} models The models in the import file to validate.
	 * @return {object} Invalid models data, or empty object if no invalid data.
	 */
	async function validateModels(models) {
		let errors = {};

		let storedModelData = await getModels();

		for (const [index, modelData] of Object.entries(models)) {
			const modelErrors = await validateModel(modelData, storedModelData);
			if (Object.keys(modelErrors).length > 0) {
				errors[modelData?.slug || index] = modelErrors;
			}
		}

		return errors;
	}

	function showImportErrors(modelErrors) {
		let message = "";

		Object.entries(modelErrors).forEach(([modelSlug, errors]) => {
			if (errors?.alreadyExists) {
				message +=
					"<li>" +
					sprintf(
						__(
							// translators: The name of the model.
							"The ‘%s’ model already exists.",
							"atlas-content-modeler"
						),
						modelSlug
					);
				+"</li>";
			}

			if (errors?.missingProperties) {
				message +=
					"<li>" +
					sprintf(
						__(
							// Translators: 1: The name of the model. 2: A list of missing properties, such as "id".
							"The ‘%1$s’ model is missing properties: %2$s.",
							"atlas-content-modeler"
						),
						modelSlug,
						errors.missingProperties.join(", ")
					) +
					"</li>";
			}

			if (errors?.fieldErrors) {
				let fieldMessages = "";

				Object.entries(errors.fieldErrors).forEach(
					([fieldKey, missingProperties]) => {
						fieldMessages +=
							"<li>" +
							sprintf(
								__(
									// Translators: 1: The id of the field. 2: A list of missing properties, such as "type".
									"Field ‘%1$s’ is missing: %2$s.",
									"atlas-content-modeler"
								),
								fieldKey,
								missingProperties.join(", ")
							) +
							"</li>";
					}
				);

				message +=
					"<li>" +
					// Translators: 1: The name of the model. 2: A list of missing properties.
					sprintf(
						__(
							"The ‘%1$s’ model has fields with missing properties:\n %2$s",
							"atlas-content-modeler"
						),
						modelSlug,
						"<ul>" + fieldMessages + "</ul>"
					) +
					"</li>";
			}
		});

		showError(
			sprintf(
				"<strong>%1$s</strong><ul>%2$s</ul>",
				__(
					"Errors prevented import. No changes have been made.",
					"atlas-content-modeler"
				),
				message
			)
		);
	}

	/**
	 * Uploads the file data to the API.
	 */
	async function createModels(formData) {
		const parsedData = JSON.parse(formData);
		const modelData = Object.values(parsedData);
		const modelErrors = await validateModels(modelData);

		if (Object.keys(modelErrors).length !== 0) {
			showImportErrors(modelErrors);
			fileUploaderRef.current.value = null;
			return;
		}

		apiFetch({
			path: "/wpe/atlas/content-models",
			method: "PUT",
			_wpnonce: wpApiSettings.nonce,
			data: modelData,
		})
			.then((res) => {
				if (res.success) {
					showSuccess(
						__(
							"The models were successfully imported.",
							"atlas-content-modeler"
						)
					);

					modelData.forEach(insertSidebarMenuItem);
					fileUploaderRef.current.value = null;
				}
			})
			.catch((err) => {
				showError(
					err?.message ||
						__(
							"An unknown import error occurred",
							"atlas-content-modeler"
						)
				);
				fileUploaderRef.current.value = null;
			});
	}

	/**
	 * Format filename for export
	 * @returns {string}
	 */
	function getFormattedDateTime() {
		return new Date().toISOString().split(".")[0].replace(/[T:]/g, "-");
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
