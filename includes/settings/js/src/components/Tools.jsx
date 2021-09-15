import React from "react";
import { __, sprintf } from "@wordpress/i18n";
import { toast } from "react-toastify";
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
		getModels()
			.then((res) => {
				if (res) {
					const jsonData = JSON.stringify(res);
					const blob = new Blob([jsonData], { type: "text/plain" });
					const url = URL.createObjectURL(blob);
					const link = document.createElement("a");
					link.download = `${getFormattedTime()}-model-export.json`;
					link.href = url;
					link.click();

					toast(
						__(
							"The models were successfully exported.",
							"atlas-content-modeler"
						)
					);
				} else {
					toast(
						__(
							"There are no models to export.",
							"atlas-content-modeler"
						)
					);
				}
			})
			.catch(() => {
				toast(
					__(
						"There was an error exporting the models.",
						"atlas-content-modeler"
					)
				);
			});
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
										"IMPORT MODEL",
										"atlas-content-modeler"
									)}
								</h4>
								<p className="help">
									{__(
										"Select a .json file containing model/field definitions to import as a content model.",
										"atlas-content-modeler"
									)}
								</p>
								<button className="button button-primary link-button">
									{__("Select File", "atlas-content-modeler")}
								</button>
							</div>
							<div className="col-xs-12 mt-4">
								<h4>
									{__(
										"EXPORT MODEL",
										"atlas-content-modeler"
									)}
								</h4>
								<p className="help">
									{__(
										"Exporting a model will generate a .json document representing all of the existing models and fields.",
										"atlas-content-modeler"
									)}
								</p>
								<button
									className="button button-primary link-button"
									onClick={(event) =>
										exportClickHandler(event)
									}
								>
									{__("Export", "atlas-content-modeler")}
								</button>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
	);
}
