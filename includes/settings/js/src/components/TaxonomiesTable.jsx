import React from "react";
import { __ } from "@wordpress/i18n";
import { TaxonomiesDropdown } from "./TaxonomiesDropdown";

const TaxonomiesTable = ({ taxonomies = {}, setEditingTaxonomy }) => {
	if (Object.values(taxonomies).length < 1) {
		return (
			<p>
				{__(
					"You currently have no taxonomies.",
					"atlas-content-modeler"
				)}
			</p>
		);
	}

	return (
		<>
			<table className="table table-striped">
				<thead>
					<tr>
						<th>{__("Name", "atlas-content-modeler")}</th>
						<th>{__("Slug", "atlas-content-modeler")}</th>
						<th>{__("Models", "atlas-content-modeler")}</th>
						<th className="action">
							{__("Action", "atlas-content-modeler")}
						</th>
					</tr>
				</thead>
				<tbody>
					{Object.values(taxonomies).map((taxonomy) => {
						return (
							<tr key={taxonomy?.slug}>
								<td>{taxonomy?.plural}</td>
								<td>{taxonomy?.slug}</td>
								<td>{taxonomy?.types?.join(", ")}</td>
								<td className="action right">
									<div className="neg-margin-wrapper">
										<TaxonomiesDropdown
											taxonomy={taxonomy}
											setEditingTaxonomy={
												setEditingTaxonomy
											}
										/>
									</div>
								</td>
							</tr>
						);
					})}
				</tbody>
			</table>
		</>
	);
};

export default TaxonomiesTable;
