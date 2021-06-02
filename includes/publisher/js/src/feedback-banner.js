const { apiFetch } = window.wp;

apiFetch({
	path: "/wpe/feedback-meta",
	method: "POST",
	_wpnonce: wpApiSettings.nonce,
})
	.then((res) => {
		console.log("response", res);
		if (res.success) {
			// dispatch({ type: "addModel", data: res.model });
			// history.push(
			// 	"/wp-admin/admin.php?page=atlas-content-modeler&view=edit-model&id=" +
			// 	data.slug
			// );
			//
			// // Insert the sidebar menu item below the Comments item, to avoid doing a full page refresh.
			// insertSidebarMenuItem(res.model);
			//
			// window.scrollTo(0, 0);
			// showSuccess(
			// 	`The “${res.model.plural}” model was created. Now add your first field.`
			// );
		}
	})
	.catch((err) => {
		// if (err.code === "atlas_content_modeler_already_exists") {
		// 	setError("slug", {
		// 		type: "idExists",
		// 		message: err.message,
		// 	});
		// }
	});
