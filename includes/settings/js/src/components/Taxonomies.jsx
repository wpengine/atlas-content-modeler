import React, { useContext, useState } from "react";
import { useHistory } from "react-router-dom";
import { __, sprintf } from "@wordpress/i18n";
import { useLocationSearch } from "../utils";
import { ModelsContext } from "../ModelsContext";
import TaxonomiesTable from "./TaxonomiesTable";
import TaxonomiesForm from "./TaxonomiesForm";

export default function Taxonomies() {
	const { taxonomies } = useContext(ModelsContext);
	const history = useHistory();
	const query = useLocationSearch();
	const editing = query.get("editing");
	const editingTaxonomy = editing ? taxonomies[editing] : null;

	const cancelEditing = () => {
		history.push(atlasContentModeler.appPath + "&view=taxonomies");
	};

	return (
		<div className="app-card taxonomies-view">
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
				{editingTaxonomy ? (
					<button
						className="tertiary"
						onClick={(event) => {
							event.preventDefault();
							cancelEditing();
						}}
					>
						{__("Cancel Editing", "atlas-content-modeler")}
					</button>
				) : (
					<button
						className="tertiary"
						onClick={() =>
							history.push(atlasContentModeler.appPath)
						}
					>
						{__("View Content Models", "atlas-content-modeler")}
					</button>
				)}
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
		</div>
	);
}
