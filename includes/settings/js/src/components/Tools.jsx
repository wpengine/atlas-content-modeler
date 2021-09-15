import React from "react";
import { __ } from "@wordpress/i18n";
import ExportFileButton from "./ExportFileButton";
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
								<button className="button button-primary link-button">
									{__("Select File", "atlas-content-modeler")}
								</button>
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
									buttonTitle="Export Stuff"
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
