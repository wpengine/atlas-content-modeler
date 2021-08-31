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

	return (
		<div className="app-card taxonomies-view">
			<section className="heading flex-wrap d-flex flex-column d-sm-flex flex-sm-row">
				<h2>{__("Tools", "atlas-content-modeler")}</h2>
			</section>
			<section className="card-content">
				<div className="row">
					<div className="col-xs-10 col-lg-4 order-1 order-lg-0">
						Tools Body
					</div>
				</div>
			</section>
		</div>
	);
}
