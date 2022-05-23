import React, { useRef } from "react";
import { sprintf, __ } from "@wordpress/i18n";
import ExportFileButton from "./ExportFileButton";
import ImportFileButton from "./ImportFileButton";
import { showError, showSuccess } from "../toasts";
import { insertSidebarMenuItem } from "../utils";
import { Card } from "../../../../shared-assets/js/components/card";
const { wp } = window;
const { apiFetch } = wp;

export default function Dashboard() {
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
