import React, { useRef, useState } from "react";
import { sprintf, __ } from "@wordpress/i18n";
import { dispatch } from "@wordpress/data";
import { showError, showSuccess } from "../toasts";
import { insertSidebarMenuItem } from "../utils";
import { useHistory } from "react-router-dom";
import Highcharts from "highcharts";
import HighchartsReact from "highcharts-react-official";
import ExportFileButton from "./ExportFileButton";
import ImportFileButton from "./ImportFileButton";
import { Card } from "../../../../shared-assets/js/components/card";
import LinkList from "./LinkList";

const { wp } = window;
const { apiFetch } = wp;

export default function DashboardDisplay({ modelChartOptions, taxonomies }) {
	const [usageTracking, setUsageTracking] = useState(
		atlasContentModeler.usageTrackingEnabled
	);
	const fileUploaderRef = useRef(null);
	let history = useHistory();

	/**
	 * Links
	 */
	const socialMediaLinkOptions = {
		options: {
			liStyles: { borderLeft: "none", boxShadow: "none" },
			aStyles: { textDecoration: "none", marginRight: "10px" },
		},
		links: [
			{
				icon: "dashicons dashicons-facebook",
				url: "https://www.facebook.com/wpengine",
				title: "Facebook",
				hideTitle: true,
			},
			{
				icon: "dashicons dashicons-twitter",
				url: "https://twitter.com/wpengine",
				title: "Twitter",
				hideTitle: true,
			},
			{
				icon: "dashicons dashicons-linkedin",
				url: "https://www.linkedin.com/company/wpengine",
				title: "LinkedIn",
				hideTitle: true,
			},
			{
				icon: "dashicons dashicons-youtube",
				url: "https://www.youtube.com/channel/UCJeAEAxX69v24CUBZ0WBYSg",
				title: "YouTube",
				hideTitle: true,
			},
			{
				icon: "dashicons dashicons-instagram",
				url: "https://www.instagram.com/wpengine/",
				title: "Instagram",
				hideTitle: true,
			},
		],
	};

	const resourceLinkOptions = {
		options: {
			liStyles: { borderLeft: "none", boxShadow: "none" },
		},
		links: [
			{
				title: "ACM Feedback",
				url:
					"https://docs.google.com/forms/d/e/1FAIpQLScc2VN-GRSJMz8zVgJLL6kiX3VeV2jkSDnmU1gnuNElEHCEVQ/viewform",
			},
			{
				title: "ACM Plugin Home",
				url: "https://wordpress.org/plugins/atlas-content-modeler/",
			},
			{
				title: "ACM Github",
				url: "https://github.com/wpengine/atlas-content-modeler",
			},
		],
	};

	/**
	 * Sets and saves tracking data for analytics
	 * @param {*} event
	 */
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

	/**
	 * Displays model errors for import
	 * @param {*} modelErrors
	 */
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
				<strong>
					{sprintf(
						__(
							"ACM Plugin Version: %s by ",
							"atlas-content-modeler"
						),
						atlasContentModeler.acm_plugin_data.Version
					)}
				</strong>
				<span
					dangerouslySetInnerHTML={{
						__html: atlasContentModeler.acm_plugin_data.Author,
					}}
				></span>
			</div>

			<div className="container acm-dashboard">
				<div className="stats">
					<div className="d-flex justify-content-between">
						<Card className="flex-grow-1">
							<h1 className="purple-h1">
								{Object.keys(atlasContentModeler.initialState)
									.length || 0}
							</h1>
							<div>
								<a
									title="edit models"
									onClick={(e) =>
										history.push(
											atlasContentModeler.appPath +
												"&view=models-list"
										)
									}
									className="mb-2 pointer"
								>
									{__("Models ", "atlas-content-modeler")}
									<span className="dashicons dashicons-edit"></span>
								</a>
							</div>
							<div className="mt-3">
								<a
									title="add model"
									style={{ textDecoration: "none" }}
									href="/wp-admin/admin.php?page=atlas-content-modeler&view=create-model"
								>
									<span className="dashicons dashicons-plus"></span>
									{__("Add Model", "atlas-content-modeler")}
								</a>
							</div>

							<hr />
							<div className="flex-grow-1">
								<h3>
									{__("Top Five", "atlas-content-modeler")}
								</h3>
								{atlasContentModeler.stats.modelsCounts.length >
									0 && (
									<div className="list-group list-group-flush">
										{atlasContentModeler.stats.modelsCounts.map(
											(entry, index) => {
												if (index <= 4) {
													return (
														<button
															key={entry.model}
															type="button"
															className="list-group-item list-group-item-action pointer pointer"
															onClick={(e) =>
																history.push(
																	atlasContentModeler.appPath +
																		`&view=edit-model&id=${entry.model}`
																)
															}
														>
															<span className="badge badge-secondary me-2">
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
										{__(
											"No data to display. ",
											"atlas-content-modeler"
										)}
										<a href="/wp-admin/admin.php?page=atlas-content-modeler&view=create-model">
											{__(
												"Create your first model!",
												"atlas-content-modeler"
											)}
										</a>
									</p>
								)}
							</div>
						</Card>
						<Card className="flex-grow-1">
							<h1 className="purple-h1">
								{Object.keys(
									atlasContentModeler.stats.taxonomies
								).length || 0}
							</h1>{" "}
							<div>
								<a
									title="edit taxonomies"
									onClick={(e) =>
										history.push(
											atlasContentModeler.appPath +
												"&view=taxonomies"
										)
									}
									className="mb-2 pointer"
								>
									{__("Taxonomies ", "atlas-content-modeler")}
									<span className="dashicons dashicons-edit"></span>
								</a>
							</div>
							<div className="mt-3">
								<a
									style={{ textDecoration: "none" }}
									href="/wp-admin/admin.php?page=atlas-content-modeler&view=taxonomies"
									title="add taxonomy"
								>
									<span className="dashicons dashicons-plus"></span>{" "}
									{__(
										"Add Taxonomy",
										"atlas-content-modeler"
									)}
								</a>
							</div>
							<hr />
							<div className="flex-grow-1">
								<h3>
									{__("Top Five", "atlas-content-modeler")}
								</h3>
								{Object.keys(
									atlasContentModeler.stats.taxonomies
								).length > 0 && (
									<div className="list-group list-group-flush">
										{getTaxonomies().map((entry, index) => {
											// show 5
											if (index <= 4) {
												return (
													<button
														key={entry.name}
														type="button"
														style={{
															cursor: "pointer",
														}}
														className="list-group-item list-group-item-action"
														onClick={(e) =>
															history.push(
																atlasContentModeler.appPath +
																	`&view=taxonomies&editing=${entry.name}`
															)
														}
													>
														<span className="badge badge-secondary me-2">
															{entry.count}
														</span>{" "}
														{entry.name}
													</button>
												);
											}
											return false;
										})}
									</div>
								)}

								{!Object.keys(
									atlasContentModeler.stats.taxonomies
								).length > 0 && (
									<p>
										{__(
											"No data to display. ",
											"atlas-content-modeler"
										)}
										<a href="/wp-admin/admin.php?page=atlas-content-modeler&view=taxonomies">
											{__(
												"Create your first taxonomy!",
												"atlas-content-modeler"
											)}
										</a>
									</p>
								)}
							</div>
						</Card>
						<Card className="flex-grow-1">
							<h1 className="purple-h1">
								{atlasContentModeler.stats.relationships
									.totalRelationshipConnections || 0}
							</h1>
							<span style={{ fontSize: "x-large" }}>
								{__("Relationships", "atlas-content-modeler")}
							</span>{" "}
							<hr />
							<div className="flex-grow-1">
								<h3>
									{__("Top Five", "atlas-content-modeler")}
								</h3>
								{atlasContentModeler.stats.relationships
									.mostConnectedEntries.length > 0 && (
									<div className="list-group list-group-flush">
										{atlasContentModeler.stats.relationships.mostConnectedEntries.map(
											(entry, index) => {
												// show 5
												if (index <= 4) {
													return (
														<button
															key={entry.id1}
															type="button"
															className="list-group-item list-group-item-action pointer"
															onClick={(e) =>
																(window.location.href =
																	entry.admin_link)
															}
														>
															<span className="badge badge-secondary me-2">
																{
																	entry.total_connections
																}
															</span>{" "}
															{entry.post_title}
														</button>
													);
												}
												return false;
											}
										)}
									</div>
								)}

								{!atlasContentModeler.stats.relationships
									.mostConnectedEntries.length > 0 && (
									<p>
										{__(
											"No data to display.",
											"atlas-content-modeler"
										)}
									</p>
								)}
							</div>
						</Card>
						<Card className="flex-grow-1">
							<h3>
								{__(
									"Ten Latest Entries",
									"atlas-content-modeler"
								)}
							</h3>
							{atlasContentModeler.stats.recentModelEntries
								?.length > 0 && (
								<div className="list-group list-group-flush">
									{atlasContentModeler.stats.recentModelEntries.map(
										(entry, index) => {
											// show 10
											if (index <= 9) {
												return (
													<button
														key={entry.ID}
														title={entry.post_title}
														type="button"
														style={{
															cursor: "pointer",
															whiteSpace:
																"nowrap",
															overflow: "hidden",
															textOverflow:
																"ellipsis",
															maxWidth: "200px",
														}}
														className="list-group-item list-group-item-action"
														onClick={(e) => {
															window.location.href = `/wp-admin/post.php?post=${entry.ID}&action=edit`;
														}}
													>
														<span className="dashicons dashicons-admin-post me-2"></span>{" "}
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
									{__(
										"No data to display. ",
										"atlas-content-modeler"
									)}
									<a href="/wp-admin/admin.php?page=atlas-content-modeler&view=create-model">
										{__(
											"Create your first model!",
											"atlas-content-modeler"
										)}
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
								options={modelChartOptions}
							/>
						)}

						{!atlasContentModeler.stats.modelsCounts.length > 0 && (
							<p>
								{__(
									"No chart data to display. ",
									"atlas-content-modeler"
								)}
								<a href="/wp-admin/admin.php?page=atlas-content-modeler&view=create-model">
									{__(
										"Create your first model!",
										"atlas-content-modeler"
									)}
								</a>
							</p>
						)}
					</Card>

					<Card className="flex-grow-1">
						<form>
							<div className="row">
								<h3>
									{__(
										"Quick Settings",
										"atlas-content-modeler"
									)}
								</h3>
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
					<Card className="flex-grow-0">
						<section className="card-content">
							<div className="row">
								<h3 className="mb-4">
									{__("Tools", "atlas-content-modeler")}
								</h3>
								<div className="col-xs-10 col-lg-4 order-1 order-lg-0">
									<div className="d-flex">
										<div className="me-4">
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

					<Card className="flex-grow-1">
						<section className="card-content">
							<div className="row">
								<h3>
									{__("Resources", "atlas-content-modeler")}
								</h3>
								<LinkList
									classes="list"
									linkOptions={resourceLinkOptions}
								/>
								<LinkList
									classes="list list-unstyled d-flex justify-content-start"
									linkOptions={socialMediaLinkOptions}
								/>
							</div>
						</section>
					</Card>
				</div>
			</div>
		</div>
	);
}
