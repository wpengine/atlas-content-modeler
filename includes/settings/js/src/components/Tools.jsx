import React from "react";
import { __ } from "@wordpress/i18n";
import ExportFileButton from "./ExportFileButton";
import ImportFileButton from "./ImportFileButton";
import { showError, showSuccess } from "../toasts";
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

	/* MODEL FIELD BOOLEAN
	id: "1632504523751"
maxChars: ""
minChars: ""
model: "test"
name: "Boolean 1"
position: "50000"
required: true
slug: "boolean1"
type: "boolean"
	 */

	/* MODEL FIELD MEDIA
	allowedTypes: "jpeg,png"
id: "1632504485917"
maxChars: ""
minChars: ""
model: "test"
name: "Media 1"
position: "40000"
required: true
slug: "media1"
type: "media"
	 */

	/* MODEL FIELD DATE
	id: "1632504449606"
maxChars: ""
maxValue: ""
minChars: ""
minValue: ""
model: "test"
name: "Date 1"
position: "30000"
required: true
slug: "date1"
step: ""
type: "date"
	 */

	/* MODEL FIELD RICH TEXT
	id: "1632504421213"
maxChars: ""
minChars: ""
model: "test"
name: "Rich Text 1"
position: "20000"
required: true
slug: "richText1"
type: "richtext"
	 */

	/* MODEL FIELD TEXT
	id: "1632504375104"
inputType: "single"
isTitle: true
maxChars: 10
minChars: 5
model: "test"
name: "Text 1"
position: "10000"
required: true
slug: "text1"
type: "text"
	 */

	/* MODEL FIELD NUMBER
	id: "1632504324736"
maxValue: 5
minValue: 1
model: "test"
name: "Num1"
numberType: "integer"
position: "0"
required: true
slug: "num1"
step: 1
type: "number"
	 */

	/**
	 * Validate model data
	 * @param data
	 */
	function validateModelData(data) {
		let totalModelCount = 0;
		let validModelCount = 0;
		let invalidModelCount = 0;

		return totalModelCount === invalidModelCount;
	}

	/**
	 * Upload the file to the API
	 * @returns {*}
	 */
	function createModels(formData) {
		const serializedData = JSON.parse(formData);
		let modelAPICalls = null;

		if (validateModelData(serializedData)) {
			// add each model to the API fetch array
			modelAPICount.push(
				serializedData.forEach((model) => {
					apiFetch({
						path: `/wpe/atlas/content-model/`,
						method: "POST",
						_wpnonce: wpApiSettings.nonce,
						model,
					});
				})
			);
		}

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
