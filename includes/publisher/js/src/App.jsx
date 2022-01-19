/* global atlasContentModelerFormEditingExperience */
import React, { useEffect, useState } from "react";
import Fields from "./components/Fields";
import { __ } from "@wordpress/i18n";
import { sendEvent } from "acm-analytics";
import TrashPostModal from "./components/TrashPostModal";

export default function App({ model, mode }) {
	const isEditMode = mode === "edit";
	const [trashPostModalIsOpen, setTrashPostModalIsOpen] = useState(false);

	useEffect(() => {
		const queryParams = new URLSearchParams(window.location.search);
		const newPost = queryParams.get("acm-post-published");
		if (newPost) {
			sendEvent({
				category: "post",
				action: "Post Published",
			});
		}
	}, []);

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
			<TrashPostModal
				isOpen={trashPostModalIsOpen}
				setIsOpen={setTrashPostModalIsOpen}
			/>
		</div>
	);
}
