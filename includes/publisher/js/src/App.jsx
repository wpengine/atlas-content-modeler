import React from "react";
import ReactGA from "react-ga4";
import Fields from "./components/Fields";
import { sprintf, __ } from "@wordpress/i18n";

ReactGA.initialize("G-S056CLLZ34", { gtagOptions: { anonymize_ip: true } });

export default function App({ model, mode }) {
	const isEditMode = mode === "edit";

	ReactGA.send("pageview publisher");
	// Send a custom event
	ReactGA.event({
		category: "your category",
		action: "your action",
		label: "your label", // optional
		value: 99, // optional, must be a number
		nonInteraction: true, // optional, true/false
		transport: "xhr", // optional, beacon/xhr/image
	});
	/**
	 * Navigate to the post new php file for current slug
	 * @param e
	 */
	function clickHandler(e) {
		e.preventDefault();
		window.location.href = `/wp-admin/post-new.php?post_type=${model.slug}`;
	}

	return (
		<div className="app classic-form" style={{ marginTop: "20px" }}>
			<div className="flex-parent">
				<div>
					<h3 className="main-title">
						{isEditMode ? (
							<span>{__("Edit", "atlas-content-modeler")} </span>
						) : (
							<span>{__("Add", "atlas-content-modeler")} </span>
						)}
						{model.singular}
					</h3>
				</div>

				{isEditMode && (
					<div
						style={{ marginLeft: "20px" }}
						className="flex-align-v"
					>
						<a
							className="page-title-action"
							href={
								"/wp-admin/post-new.php?post_type=" + model.slug
							}
						>
							Add New
						</a>
					</div>
				)}
			</div>
			<div className="d-flex flex-column">
				<Fields model={model} />
			</div>
		</div>
	);
}
