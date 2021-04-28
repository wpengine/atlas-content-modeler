import React from "react";
import Fields from "./components/Fields";

export default function App({ model, mode }) {
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
				{mode === 'edit' ? (
					<span>Edit </span>
				) : (
					<span>Add </span>
				)}
				{model.labels.singular_name}
			</h3>

			{mode === 'edit' && (
				<button
					className="page-title-action"
					onClick={(e) => clickHandler(e)}
				>
					Add New
				</button>
			)}

			<Fields model={model} />
		</div>
	);
}
