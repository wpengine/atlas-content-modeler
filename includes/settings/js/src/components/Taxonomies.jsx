import React, { useContext } from "react";
import { useHistory } from "react-router-dom";
import { __ } from "@wordpress/i18n";
import { ModelsContext } from "../ModelsContext";

export default function Taxonomies() {
	const { taxonomies, taxonomiesDispatch } = useContext(ModelsContext);
	const history = useHistory();

	return (
		<div className="app-card">
			<section className="heading flex-wrap d-flex flex-column d-sm-flex flex-sm-row">
				<h2>Taxonomies</h2>
				<button
					className="tertiary"
					onClick={() => history.push(atlasContentModeler.appPath)}
				>
					{__("View Content Models", "atlas-content-modeler")}
				</button>
			</section>
			<section className="card-content">
				<div className="row">
					<div className="col-xs-10 col-lg-4 order-1 order-lg-0">
						Form goes here.
					</div>
					<div className="col-xs-10 col-lg-6 order-0 order-lg-1">
						{Object.values(taxonomies).map((taxonomy) => {
							return (
								<p key={taxonomy?.slug}>{taxonomy?.plural}</p>
							);
						})}
					</div>
				</div>
			</section>
		</div>
	);
}
