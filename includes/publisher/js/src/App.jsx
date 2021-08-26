import React, { useEffect } from "react";
import Fields from "./components/Fields";
import { __ } from "@wordpress/i18n";
import { initializeAnalytics, sendPageView } from "acm-analytics";

export default function App({ model, mode }) {
	const isEditMode = mode === "edit";

	useEffect(() => {
		initializeAnalytics();
		sendPageView("ACM Publisher Entry");
	}, []);

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
