import React, { useRef, useState } from "react";
import { sprintf, __ } from "@wordpress/i18n";
import { showError, showSuccess } from "../toasts";
import { insertSidebarMenuItem } from "../utils";
import { useHistory } from "react-router-dom";
import ExportFileButton from "./ExportFileButton";
import ImportFileButton from "./ImportFileButton";
import { Card } from "../../../../shared-assets/js/components/card";
import LinkList from "./LinkList";
import { saveUsageTrackingSetting } from "../settings.service";
import * as toolService from "../tools.service";

export default function DashboardDisplay({ taxonomies }) {
	const [usageTracking, setUsageTracking] = useState(
		atlasContentModeler.usageTrackingEnabled
	);
	let fileUploaderRef = useRef();
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
	 * Uploads the file data to the API.
	 */
	async function createModels(formData) {
		toolService.createModels(formData, fileUploaderRef);
	}

	/**
	 * Sets and saves tracking data for analytics
	 * @param {*} event
	 */
	function saveTrackingSetting(event) {
		saveUsageTrackingSetting(event);
		setUsageTracking(event.target.value);
		showSuccess("Your setting was updated!");
	}

	/**
	 * Format filename for export
	 * @returns {string}
	 */
	function getFormattedDateTime() {
		return toolService.getFormattedDateTime();
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
								{taxonomies.length > 0 && (
									<div className="list-group list-group-flush">
										{taxonomies.map((entry, index) => {
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
																saveTrackingSetting
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
															saveTrackingSetting
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
												callbackFn={
													toolService.getModels
												}
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
