import { useLocation } from "react-router-dom";

/**
 * Parses query string and returns value.
 *
 * @credit https://reactrouter.com/web/example/query-parameters
 * @returns {URLSearchParams}
 */
export function useLocationSearch() {
	return new URLSearchParams(useLocation().search);
}

/**
 * Inserts the content model list item in the wp-admin sidebar menu.
 *
 * @param {Object} model - The content model to be added to the sidebar.
 */
export function insertSidebarMenuItem(model) {
	const postMenuItems = document.querySelectorAll("[id^='menu-posts-']");
	let menuItem =
		postMenuItems.length > 0
			? postMenuItems[postMenuItems.length - 1]
			: document.getElementById("menu-comments");
	const markup = generateSidebarMenuItem(model);
	menuItem.insertAdjacentHTML("afterend", markup);
}

/**
 * Generates the HTML for the content model menu item.
 *
 * @param {Object} model - The content model.
 * @returns {string} - HTML list item markup for the specified content model.
 */
export function generateSidebarMenuItem(model) {
	const {slug, labels: {name}} = model;
	return (
		`<li class="wp-has-submenu wp-not-current-submenu menu-top menu-icon-${slug}" id="menu-posts-${slug}">
				<a href="edit.php?post_type=${slug}" class="wp-has-submenu wp-not-current-submenu menu-top menu-icon-${slug}" aria-haspopup="true">
					<div class="wp-menu-arrow">
						<div></div>
					</div>
					<div class="wp-menu-image dashicons-before dashicons-admin-post" aria-hidden="true"><br/></div>
					<div class="wp-menu-name">${name}</div>
				</a>
				<ul class="wp-submenu wp-submenu-wrap">
					<li class="wp-submenu-head" aria-hidden="true">${name}</li>
					<li class="wp-first-item">
						<a href="edit.php?post_type=${slug}" class="wp-first-item">All ${name}</a>
					</li>
					<li>
						<a href="post-new.php?post_type=${slug}">Add New</a>
					</li>
				</ul>
			</li>`
	);
}
