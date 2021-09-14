import React, { useContext, useEffect, useRef } from "react";
import { useHistory } from "react-router-dom";
import { __, sprintf } from "@wordpress/i18n";
import { ModelsContext } from "../ModelsContext";

export default function Tools() {
	const { tools } = useContext(ModelsContext);
	const history = useHistory();

	const cancelEditing = () => {
		history.push(atlasContentModeler.appPath + "&view=tools");
	};

	/**
	 * Gets model export data via the REST API.
	 */
	function exportModels() {
		const models = apiFetch({
			path: `/wpe/atlas/content-model/export-models`,
			method: "GET",
			_wpnonce: wpApiSettings.nonce,
		})
			.then((res) => {
				return res;
			})
			.error((err) => {
				console.log(err);
			});

		return models;
	}

	/**
	 * Export click handler for generating models .json file
	 * @param event
	 */
	function exportClickHandler(event) {
		event.preventDefault();
		exportModels();
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
