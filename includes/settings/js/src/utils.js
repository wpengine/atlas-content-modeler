import { useLocation } from "react-router-dom";
import { getFieldOrder, getRootFields } from "./queries";
import { toValidApiId } from "./components/fields/toValidApiId";

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
 * @param {String} slug - The content model item to be removed from the sidebar. Lowercased.
 */
export function removeSidebarMenuItem(slug) {
	const menuItem = document.querySelector(
		`[id="menu-posts-${slug.toLowerCase()}"]`
	);
	if (menuItem) {
		menuItem.remove();
	}
}

/**
 * Generates the HTML for the content model menu item.
 *
 * @param {Object} model - The content model.
 * @returns {string} - HTML list item markup for the specified content model.
 */
export function generateSidebarMenuItem(model) {
	let { slug, plural } = model;
	slug = slug.toLowerCase();
	return `<li class="wp-has-submenu wp-not-current-submenu menu-top menu-icon-${slug}" id="menu-posts-${slug}">
				<a href="edit.php?post_type=${slug}" class="wp-has-submenu wp-not-current-submenu menu-top menu-icon-${slug}" aria-haspopup="true">
					<div class="wp-menu-arrow">
						<div></div>
					</div>
					<div class="wp-menu-image dashicons-before dashicons-admin-post" aria-hidden="true"><br/></div>
					<div class="wp-menu-name">${plural}</div>
				</a>
				<ul class="wp-submenu wp-submenu-wrap">
					<li class="wp-submenu-head" aria-hidden="true">${plural}</li>
					<li class="wp-first-item">
						<a href="edit.php?post_type=${slug}" class="wp-first-item">All ${plural}</a>
					</li>
					<li>
						<a href="post-new.php?post_type=${slug}">Add New</a>
					</li>
				</ul>
			</li>`;
}

/**
 * Closes the options dropdown if dropdown links are not in focus.
 *
 * @param {function} setDropdownOpen Call to toggle dropdown state.
 * @param {object} timer A ref to assign the timeout to. Allows cancellation when the calling component unmounts.
 */
export const maybeCloseDropdown = (setDropdownOpen, timer) => {
	timer.current = setTimeout(() => {
		const dropDownLinkIsInFocus = document?.activeElement?.parentElement.className.startsWith(
			"dropdown-content"
		);
		if (!dropDownLinkIsInFocus) {
			setDropdownOpen(false);
		}
	}, 100);
};

/**
 * Generates a link to open WPGraphQL's GraphiQL query editor in WP admin.
 *
 * Prefills the GraphiQL query with a request for the first 10 posts of the
 * `modelData` post type, including all fields in the saved field order.
 *
 * @param {object} modelData The full model data to generate a query from.
 * @return {string} The GraphiQL URL with query param prefilled.
 */
export const getGraphiQLLink = (modelData) => {
	const modelSingular = modelData.singular.replace(/\s/g, "");
	const fragmentName = `${modelSingular}Fields`;
	const pluralSlug = toValidApiId(modelData.plural);

	const fields = getRootFields(modelData?.fields);
	const fieldSlugs = getFieldOrder(fields)
		.filter((id) => fields[id]?.type !== "repeater") // @todo: handle repeater fields.
		.map((id) => {
			if (fields[id]?.type === "media") {
				return `
${fields[id]?.slug} {
  mediaItemId
  mediaItemUrl
  altText
  caption
  description
  mediaDetails {
    height
    width
    sizes {
      file
      fileSize
      height
      mimeType
      name
      sourceUrl
      width
    }
  }
}
`;
			}

			return fields[id]?.slug;
		});

	if (fieldSlugs.length === 0) {
		fieldSlugs.push("title");
	}

	const query = `
{
  ${pluralSlug}(first: 10) {
    nodes {
      ...${fragmentName}
    }
  }
}

fragment ${fragmentName} on ${modelSingular} {
  ${fieldSlugs.join("\n  ")}
}
`;

	return `/wp-admin/admin.php?page=graphiql-ide&explorerIsOpen=true&query=${encodeURIComponent(
		query
	)}`;
};
