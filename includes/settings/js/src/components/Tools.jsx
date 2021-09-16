import React from "react";
import { __ } from "@wordpress/i18n";
import ExportFileButton from "./ExportFileButton";
import ImportFileButton from "./ImportFileButton";
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
	 * Upload the file to the API
	 * @returns {*}
	 */
	function uploadFile(formData) {
		return apiFetch({
			path: `/wpe/atlas/content-models/`,
			method: "POST",
			_wpnonce: wpApiSettings.nonce,
			formData,
		});
	}

	/**
	 * Format filename for export
	 * @returns {string}
	 */
	function getFormattedDateTime() {
		var today = new Date();
		var y = today.getFullYear();
		var m = today.getMonth() + 1;
		var d = today.getDate();
		var h = today.getHours();
		var mi = today.getMinutes();
		var s = today.getSeconds();
		return m + "-" + d + "-" + y + "-" + h + "-" + mi + "-" + s;
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
										"Import Model",
										"atlas-content-modeler"
									)}
								</h4>
								<p className="help">
									{__(
										"Select a .json file containing model/field definitions to import as a content model.",
										"atlas-content-modeler"
									)}
								</p>
								<ImportFileButton callbackFn={uploadFile} />
							</div>
							<div className="col-xs-12 mt-4">
								<h4>
									{__(
										"Export Model",
										"atlas-content-modeler"
									)}
								</h4>
								<p className="help">
									{__(
										"Exporting a model will generate a .json document representing all of the existing models and fields.",
										"atlas-content-modeler"
									)}
								</p>
								<ExportFileButton
									fileTitle={`${getFormattedDateTime()}-model-export.json`}
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
