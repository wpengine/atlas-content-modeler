import React from "react";
import { __ } from "@wordpress/i18n";
import ExportFileButton from "./ExportFileButton";
import ImportFileButton from "./ImportFileButton";
import { showError, showSuccess } from "../toasts";
import { stringify } from "postcss";
const { wp } = window;
const { apiFetch } = wp;

export default function Tools() {
	/**
	 * Gets model export data via the REST API.
	 */
	function getModels() {
		return apiFetch({
			path: `/wpe/atlas/content-models/`,
			method: "GET",
			_wpnonce: wpApiSettings.nonce,
		});
	}

	/* MODEL
	api_visibility: "public"
description: ""
fields: []
model_icon: "dashicons-admin-page"
plural: "Tests"
show_in_graphql: true
show_in_rest: true
singular: "Test"
slug: "test"
	 */

	/* MODEL FIELD RELATIONSHIP
	cardinality: "one-to-one"
description: "Test"
id: "1632504552485"
maxChars: ""
minChars: ""
model: "test"
name: "Relationship 1"
position: "60000"
reference: "rabbit"
required: true
slug: "relationship1"
type: "relationship"
	 */

	/**
	 * Validate model field
	 * @param field
	 */
	function validateField(field) {
		if (field.type) {
			switch (field.type) {
				case "number":
					return (
						field.id &&
						field.model &&
						field.name &&
						field.numberType &&
						field.slug
					);
				case "text":
					return (
						field.id &&
						field.inputType &&
						field.model &&
						field.name &&
						field.slug
					);
				case "relationship":
					return (
						field.id &&
						field.model &&
						field.name &&
						field.slug &&
						field.cardinality &&
						field.reference
					);
				default:
					return field.id && field.model && field.name && field.slug;
			}
		}
		return false;
	}

	/**
	 * Validate the individual model
	 * @param model
	 */
	function validateModel(model) {
		let modelIsValid = true;
		let fieldsAreValid = true;

		// check that fields are valid
		if (model.fields?.length) {
			for (let i = 0; i < model.fields.length; i++) {
				if (!validateField(model.fields[i])) {
					fieldsAreValid = false;
					break;
				}
			}
		}

		return modelIsValid && fieldsAreValid;
	}

	/**
	 * Validate model data
	 * @param data
	 */
	function validateModelData(data) {
		let totalModelCount = 0;
		let validModelCount = 0;
		let invalidModelCount = 0;

		console.log(data);
		debugger;

		Object.entries(data).forEach((model) => {
			totalModelCount += 1;
			validateModel(model)
				? (validModelCount += 1)
				: (invalidModelCount += 1);
		});

		return totalModelCount === validModelCount;
	}

	/**
	 * Upload the file to the API
	 * @returns {*}
	 */
	function createModels(formData) {
		const parsedData = JSON.parse(formData);
		const serializedDataArray = Object.entries(parsedData);
		let modelAPICalls = [];

		if (validateModelData(serializedDataArray)) {
			// add each model to the API fetch array
			serializedDataArray.forEach((model) => {
				const modelData = JSON.stringify(model[1]);
				const slug = model[1].plural;
				const apiCall = apiFetch({
					path: `/wpe/atlas/content-model/${slug}`,
					method: "POST",
					_wpnonce: wpApiSettings.nonce,
					modelData,
				});
				modelAPICalls.push(apiCall);
			});

			// process all model api calls
			Promise.all(modelAPICalls)
				.then((response) => {
					console.log(response);
					showSuccess(
						__(
							"The models were successfully imported.",
							"atlas-content-modeler"
						)
					);
				})
				.catch((err) => {
					console.log(err);
					showError(
						__(
							"There were errors during your import.",
							"atlas-content-modeler"
						)
					);
				});
		} else {
			showError(
				__(
					"There were validation errors in your file.",
					"atlas-content-modeler"
				)
			);
		}
	}

	/**
	 * Format filename for export
	 * @returns {string}
	 */
	function getFormattedDateTime() {
		return new Date().toISOString().split(".")[0].replace(/[T:]/g, "-");
	}

	return (
		<div className="app-card tools-view">
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
		</div>
	);
}
