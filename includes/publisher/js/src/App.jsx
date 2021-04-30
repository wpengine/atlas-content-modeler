import React from "react";
import Fields from "./components/Fields";

export default function App({ model, mode }) {
	const isEditMode = mode === "edit";

	/**
	 * Navigate to the post new php file for current slug
	 * @param e
	 */
	function clickHandler(e) {
		e.preventDefault();
		window.location.href = `/wp-admin/post-new.php?post_type=${model.slug}`;
	}

	return (
		<div className="app classic-form margin-top-20">
			<div className="flex-parent">
				<div>
					<h3 className="main-title">
						{isEditMode ? <span>Edit </span> : <span>Add </span>}
						{model.labels.singular_name}
					</h3>
				</div>

				{isEditMode && (
					<div
						className="flex-align-v margin-left-20"
					>
						<button
							className="page-title-action"
							onClick={(e) => clickHandler(e)}
						>
							Add New
						</button>
					</div>
				)}
			</div>

			<Fields model={model} />
		</div>
	);
}
