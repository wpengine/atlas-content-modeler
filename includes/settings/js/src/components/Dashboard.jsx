import React, { useRef, useState } from "react";
import { sprintf, __ } from "@wordpress/i18n";
import { dispatch } from "@wordpress/data";
import ExportFileButton from "./ExportFileButton";
import ImportFileButton from "./ImportFileButton";
import { showError, showSuccess } from "../toasts";
import { insertSidebarMenuItem } from "../utils";
import { useHistory } from "react-router-dom";
import { Card } from "../../../../shared-assets/js/components/card";
import Highcharts from "highcharts";
import HighchartsReact from "highcharts-react-official";
const { wp } = window;
const { apiFetch } = wp;

export default function Dashboard() {
	const fileUploaderRef = useRef(null);
	let history = useHistory();
	let chartData = buildChartData();

	function buildChartData() {
		let data = [];
		atlasContentModeler.stats.modelsCounts.map((entry) => {
			data.push({
				name: entry.plural,
				y: parseInt(entry.count),
			});
		});

		return data;
	}

	function getPieColors() {
		var colors = [],
			base = "#7e5cef",
			i;

		for (i = 0; i < 10; i += 1) {
			// Start out with a darkened base color (negative brighten), and end
			// up with a much brighter color
			colors.push(
				Highcharts.color(base)
					.brighten((i - 3) / 7)
					.get()
			);
		}
		return colors;
	}

	// highcharts
	const options = {
		chart: {
			plotBackgroundColor: null,
			plotBorderWidth: null,
			plotShadow: false,
			type: "pie",
		},
		title: {
			text: "Models by Percent",
		},
		tooltip: {
			pointFormat: "{series.name}: <b>{point.percentage:.1f}%</b>",
		},
		accessibility: {
			point: {
				valueSuffix: "%",
			},
		},
		plotOptions: {
			pie: {
				allowPointSelect: true,
				cursor: "pointer",
				dataLabels: {
					enabled: true,
					format: "<b>{point.name}</b>: {point.percentage:.1f} %",
				},
				colors: getPieColors(),
			},
		},
		series: [
			{
				name: "Models",
				colorByPoint: true,
				data: chartData,
			},
		],
	};

	const [usageTracking, setUsageTracking] = useState(
		atlasContentModeler.usageTrackingEnabled
	);

	function saveUsageTrackingSetting(event) {
		// @todo catch save errors
		dispatch("core").saveSite({
			atlas_content_modeler_usage_tracking: event.target.value,
		});
		setUsageTracking(event.target.value);
		showSuccess("Your setting was updated!");
	}

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
		<div>
			<div className="alert alert-primary fade show" role="alert">
				<strong>ACM Plugin Version:</strong>{" "}
				{atlasContentModeler.acm_plugin_data.Version} by{" "}
				<span
					dangerouslySetInnerHTML={{
						__html: atlasContentModeler.acm_plugin_data.Author,
					}}
				></span>
			</div>

			<div className="container">
				<div className="stats">
					<div className="d-flex justify-content-between">
						<Card className="flex-grow-1">
							<h1
								style={{
									color: "#7e5cef",
									fontWeight: "bold",
									fontSize: "60px",
								}}
							>
								{Object.keys(atlasContentModeler.initialState)
									.length || 0}
							</h1>
							<a
								style={{ cursor: "pointer" }}
								onClick={(e) =>
									history.push(atlasContentModeler.appPath)
								}
								className="mb-2"
							>
								Models{" "}
								<span className="dashicons dashicons-admin-links"></span>
							</a>
							<hr />
							<div className="flex-grow-1">
								<h3>Top Models</h3>
								{atlasContentModeler.stats.modelsCounts.length >
									0 && (
									<div className="list-group list-group-flush">
										{atlasContentModeler.stats.modelsCounts.map(
											(entry, index) => {
												if (index <= 4) {
													return (
														<button
															key={entry.title}
															type="button"
															style={{
																cursor:
																	"pointer",
															}}
															className="list-group-item list-group-item-action"
															onClick={(e) =>
																history.push(
																	atlasContentModeler.appPath +
																		`&view=edit-model&id=${entry.model}`
																)
															}
														>
															<span
																className="badge badge-secondary me-2"
																style={{
																	backgroundColor:
																		"#002838",
																}}
															>
																{entry.count}
															</span>{" "}
															{entry.plural}
														</button>
													);
												}
												return false;
											}
										)}
									</div>
								)}

								{!atlasContentModeler.stats.modelsCounts
									.length > 0 && (
									<p>
										No data to display.{" "}
										<a href="/wp-admin/admin.php?page=atlas-content-modeler&view=create-model">
											Create your first Model!
										</a>
									</p>
								)}
							</div>
						</Card>
						<Card className="flex-grow-1">
							<h1
								style={{
									color: "#7e5cef",
									fontWeight: "bold",
									fontSize: "60px",
								}}
							>
								{Object.keys(atlasContentModeler.taxonomies)
									.length || 0}
							</h1>{" "}
							<a
								style={{ cursor: "pointer" }}
								onClick={(e) =>
									history.push(
										atlasContentModeler.appPath +
											"&view=taxonomies"
									)
								}
								className="mb-2"
							>
								Taxonomies{" "}
								<span className="dashicons dashicons-admin-links"></span>
							</a>
							<hr />
							<div className="flex-grow-1">
								<h3>Top Taxonomies</h3>
								{atlasContentModeler.taxonomies && (
									<div className="list-group list-group-flush">
										<button
											type="button"
											style={{
												cursor: "pointer",
												backgroundColor: "#7e5cef",
											}}
											className="list-group-item list-group-item-action active"
											onClick={(e) =>
												history.push(
													atlasContentModeler.appPath +
														"&view=taxonomies&editing=test"
												)
											}
										>
											Taxonomy 1
										</button>
									</div>
								)}

								{atlasContentModeler.taxonomies && (
									<p>
										No data to display.{" "}
										<a href="/wp-admin/admin.php?page=atlas-content-modeler&view=taxonomies">
											Create your first Taxonomy!
										</a>
									</p>
								)}
							</div>
						</Card>
						<Card className="flex-grow-1">
							<h3>Latest Model Entries</h3>
							{atlasContentModeler.stats.recentModelEntries
								?.length > 0 && (
								<div className="list-group list-group-flush">
									{atlasContentModeler.stats.recentModelEntries.map(
										(entry, index) => {
											if (index <= 4) {
												return (
													<button
														key={entry.ID}
														type="button"
														style={{
															cursor: "pointer",
														}}
														className="list-group-item list-group-item-action"
														onClick={(e) => {
															window.location.href = `/wp-admin/post.php?post=${entry.ID}&action=edit`;
														}}
													>
														{entry.post_title}
													</button>
												);
											}
											return false;
										}
									)}
								</div>
							)}

							{!atlasContentModeler.stats.recentModelEntries
								.length > 0 && (
								<p>
									No data to display.{" "}
									<a href="/wp-admin/admin.php?page=atlas-content-modeler&view=create-model">
										Create your first Model!
									</a>
								</p>
							)}
						</Card>
					</div>
				</div>
			</div>

			<div className="tools-view container">
				<div className="d-flex justify-content-between">
					<Card>
						{atlasContentModeler.stats.modelsCounts.length > 0 && (
							<HighchartsReact
								highcharts={Highcharts}
								options={options}
							/>
						)}

						{!atlasContentModeler.stats.modelsCounts.length > 0 && (
							<p>
								No chart data to display.{" "}
								<a href="/wp-admin/admin.php?page=atlas-content-modeler&view=create-model">
									Create your first Model
								</a>
							</p>
						)}
					</Card>

					<Card className="flex-grow-1">
						<form>
							<div className="row">
								<h2>Quick Settings</h2>
								<div className="col-xs-10 col-lg-4 order-1 order-lg-0">
									<div className="row">
										<div className="col-xs-12">
											<h4>
												{__(
													"Analytics",
													"atlas-content-modeler"
												)}
											</h4>
											<p className="help">
												{__(
													"Opt into anonymous usage tracking to help us make Atlas Content Modeler better.",
													"atlas-content-modeler"
												)}
											</p>
											<div className="row">
												<div className="col-xs-12">
													<label
														className="radio"
														htmlFor="atlas-content-modeler-settings[usageTrackingDisabled]"
													>
														<input
															type="radio"
															id="atlas-content-modeler-settings[usageTrackingDisabled]"
															name="atlas-content-modeler-settings[usageTracking]"
															value="0"
															checked={
																usageTracking ===
																	"0" ||
																!usageTracking
															}
															onChange={
																saveUsageTrackingSetting
															}
														></input>
														{__(
															"Disabled",
															"atlas-content-modeler"
														)}
													</label>
												</div>
												<div className="col-xs-12">
													<input
														type="radio"
														id="atlas-content-modeler-settings[usageTrackingEnabled]"
														name="atlas-content-modeler-settings[usageTracking]"
														value="1"
														checked={
															usageTracking ===
															"1"
														}
														onChange={
															saveUsageTrackingSetting
														}
													></input>
													<label
														className="radio"
														htmlFor="atlas-content-modeler-settings[usageTrackingEnabled]"
													>
														{__(
															"Enabled",
															"atlas-content-modeler"
														)}
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</form>
					</Card>
				</div>
			</div>

			<div className="tools-view container">
				<div className="d-flex justify-content-between">
					<Card className="flex-grow-1">
						<section className="card-content">
							<div className="row">
								<h2 className="mb-4">Tools</h2>
								<div className="col-xs-10 col-lg-4 order-1 order-lg-0">
									<div className="d-flex flex-column">
										<div className="mb-3">
											<ImportFileButton
												buttonTitle="Import Models"
												allowedMimeTypes=".json"
												callbackFn={createModels}
												fileUploaderRef={
													fileUploaderRef
												}
											/>
										</div>
										<div>
											<ExportFileButton
												buttonTitle="Export Models"
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

					<Card className="col-xs-12">
						<section className="card-content">
							<div className="row">
								<h2>Resources</h2>
								<ul className="list">
									<li
										style={{
											borderLeft: "none",
											boxShadow: "none",
										}}
									>
										<a
											target="_blank"
											rel="noreferrer"
											style={{
												color: "#7e5cef",
												borderLeft: "0 !important",
											}}
											href="https://docs.google.com/forms/d/e/1FAIpQLScc2VN-GRSJMz8zVgJLL6kiX3VeV2jkSDnmU1gnuNElEHCEVQ/viewform"
										>
											ACM Feedback
										</a>
									</li>
									<li
										style={{
											borderLeft: "none",
											boxShadow: "none",
										}}
									>
										<a
											target="_blank"
											rel="noreferrer"
											style={{ color: "#7e5cef" }}
											href="https://wordpress.org/plugins/atlas-content-modeler/"
										>
											ACM Plugin Home
										</a>
									</li>
									<li
										style={{
											borderLeft: "none",
											boxShadow: "none",
										}}
									>
										<a
											target="_blank"
											rel="noreferrer"
											style={{ color: "#7e5cef" }}
											href="https://github.com/wpengine/atlas-content-modeler"
										>
											ACM Github
										</a>
									</li>
								</ul>
							</div>
						</section>
					</Card>
				</div>
			</div>
		</div>
	);
}
