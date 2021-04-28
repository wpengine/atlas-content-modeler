import React from "react";
import Fields from "./components/Fields";
import ActionButtons from "./components/ActionButtons";

export default function App({ model, mode }) {
	const isEditMode = mode === 'edit';

	/**
	 * Navigate to the post new php file for current slug
	 * @param e
	 */
	function clickHandler(e) {
		e.preventDefault();
		window.location.href =
			`/wp-admin/post-new.php?post_type=${model.slug}`;
	}

	return (
		<div className="app classic-form">
			<h3 className="main-title">
				{isEditMode ? (
					<span>Edit </span>
				) : (
					<span>Add </span>
				)}
				{model.labels.singular_name}
			</h3>

			{isEditMode && (
				<button
					className="page-title-action"
					onClick={(e) => clickHandler(e)}
				>
					Add New
				</button>
			)}

			<Fields model={model} />

			<ActionButtons isEditMode={isEditMode} />
		</div>
	);
}
