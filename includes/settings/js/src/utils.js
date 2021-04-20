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
 * Removes the content model list item in the wp-admin sidebar menu.
 *
 * @param {String} slug - The content model to be removed from the sidebar.
 */
export function removeSidebarMenuItem(slug) {
	const menuItem = document.querySelector(`[id="menu-posts-${slug}"]`);
	console.log(menuItem);
	menuItem.remove();
}

/**
 * Generates the HTML for the content model menu item.
 *
 * @param {Object} model - The content model.
 * @returns {string} - HTML list item markup for the specified content model.
 */
export function generateSidebarMenuItem(model) {
	let {slug, labels: {name}} = model;
	slug = slug.toLowerCase();
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

/**
 * Closes the options dropdown if dropdown links are not in focus.
 *
 * @param {function} setDropdownOpen Call to toggle dropdown state.
 */
export const maybeCloseDropdown = (setDropdownOpen) => {
	setTimeout(() => {
		const dropDownLinkIsInFocus = document?.activeElement?.parentElement.className.startsWith("dropdown-content");
		if (!dropDownLinkIsInFocus) {
			setDropdownOpen(false);
		}
	}, 100)
};
