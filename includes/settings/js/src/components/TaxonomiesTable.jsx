import React from "react";
import { __, sprintf } from "@wordpress/i18n";
import { TaxonomiesDropdown } from "./TaxonomiesDropdown";

const TaxonomiesTable = ({ taxonomies = {} }) => {
	return (
		<>
			<div className="bulk-actions">
				<select name="bulk-action">
					<option value="-1">Bulk action</option>
					<option value="delete">Delete</option>
				</select>
				<button className="tertiary">Apply</button>
			</div>
			<table className="table table-striped">
				<thead>
					<tr>
						<th className="checkbox">
							<input
								type="checkbox"
								className="check-all"
								aria-label={__(
									"Toggle selection of all taxonomies to apply a bulk action",
									"atlas-content-modeler"
								)}
							/>
						</th>
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
								<td className="checkbox">
									<input
										type="checkbox"
										className="check-all"
										aria-label={sprintf(
											__(
												"Toggle selection of the %s taxonomy.",
												"atlas-content-modeler"
											),
											taxonomy.plural
										)}
									/>
								</td>
								<td>{taxonomy?.plural}</td>
								<td>{taxonomy?.slug}</td>
								<td>{taxonomy?.types?.join(", ")}</td>
								<td className="action right">
									<div className="neg-margin-wrapper">
										<TaxonomiesDropdown
											taxonomy={taxonomy}
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
