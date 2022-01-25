import React, { useContext, useEffect, useRef } from "react";
import { useHistory } from "react-router-dom";
import { __, sprintf } from "@wordpress/i18n";
import { useLocationSearch } from "../utils";
import { ModelsContext } from "../ModelsContext";
import TaxonomiesTable from "./TaxonomiesTable";
import TaxonomiesForm from "./TaxonomiesForm";
import { Card } from "../../../../shared-assets/js/components/card";

export default function Taxonomies() {
	const { taxonomies } = useContext(ModelsContext);
	const history = useHistory();
	const query = useLocationSearch();
	const editing = query.get("editing");
	const editingTaxonomy = editing ? taxonomies[editing] : null;

	const prevTaxonomiesRef = useRef();
	useEffect(() => {
		prevTaxonomiesRef.current = taxonomies;
	});
	const prevTaxonomies = prevTaxonomiesRef.current;

	useEffect(() => {
		if (!taxonomies || !prevTaxonomies) {
			// If no prevTaxonomies, then nothing has been changed.
			return;
		}

		const getTaxonomyEditHref = (slug, type) =>
			`edit-tags.php?taxonomy=${slug}&post_type=${type}`;

		// Remove taxonomies that no longer exists or are not associated with a specific model.
		Object.values(prevTaxonomies).forEach(({ slug, types }) => {
			types.forEach((type) => {
				if (
					!taxonomies.hasOwnProperty(slug) ||
					!taxonomies[slug].types.includes(type)
				) {
					const href = getTaxonomyEditHref(slug, type);
					const taxLink = document.querySelector(
						`#adminmenu a[href="${href}"]`
					);

					if (!taxLink) {
						// This model for this "type" must have been deleted.
						return;
					}

					// Just hide the list item so we can preserve order if this type is re-assigned.
					taxLink.parentElement.style.display = "none";
				}
			});
		});

		// Add or update all current taxonomies.
		Object.values(taxonomies).forEach(({ slug, plural, types }) => {
			types.forEach((type) => {
				const href = getTaxonomyEditHref(slug, type);
				const taxLink = document.querySelector(
					`#adminmenu a[href="${href}"`
				);

				// Get or create the list item.
				let listItem = taxLink
					? taxLink.parentElement
					: document.createElement("li");

				// Give the list item an updated edit link.
				listItem.innerHTML = `<a href="${href}">${plural}</a>`;
				// Always show taxonomies that are enabled in case we hid them on removal.
				listItem.style.display = "list-item";

				// If the taxonomy link didn't exist already, we should insert it.
				if (!taxLink) {
					const postTypeSubMenu = document.querySelector(
						`#menu-posts-${type} .wp-submenu`
					);

					if (!postTypeSubMenu) {
						// The model for this "type" must have been deleted.
						return;
					}

					postTypeSubMenu.appendChild(listItem);
				}
			});
		});
	}, [taxonomies]);

	const cancelEditing = () => {
		history.push(atlasContentModeler.appPath + "&view=taxonomies");
	};

	return (
		<Card className="taxonomies-view">
			<section className="heading flex-wrap d-flex flex-column d-sm-flex flex-sm-row">
				<h2>
					{editingTaxonomy ? (
						<>
							<a
								href="#"
								onClick={(event) => {
									event.preventDefault();
									cancelEditing();
								}}
							>
								{__("Taxonomies", "atlas-content-modeler")}
							</a>
							{" / "}
							{editingTaxonomy.plural}
						</>
					) : (
						__("Taxonomies", "atlas-content-modeler")
					)}
				</h2>
			</section>
			<section className="card-content">
				<div className="row">
					<div className="col-xs-10 col-lg-4 order-1 order-lg-0">
						<h3>
							{editingTaxonomy
								? sprintf(
										__(
											"Editing “%s”",
											"atlas-content-modeler"
										),
										editingTaxonomy.plural
								  )
								: __("Add New", "atlas-content-modeler")}
						</h3>
						<TaxonomiesForm
							editingTaxonomy={editingTaxonomy}
							cancelEditing={cancelEditing}
						/>
					</div>
					{!editingTaxonomy && (
						<div className="taxonomy-list col-xs-10 col-lg-8 order-0 order-lg-1">
							<TaxonomiesTable taxonomies={taxonomies} />
						</div>
					)}
				</div>
			</section>
		</Card>
	);
}
