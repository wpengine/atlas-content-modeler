import React from "react";
import { __ } from "@wordpress/i18n";

const TaxonomiesTable = ({ taxonomies = {} }) => {
	return (
		<>
			<p>TODO: add Bulk Action here</p>
			<table className="table table-striped">
				<thead>
					<tr>
						<th className="checkbox">
							<input type="checkbox" className="check-all" />
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
									/>
								</td>
								<td>{taxonomy?.plural}</td>
								<td>{taxonomy?.slug}</td>
								<td>{taxonomy?.types?.join(", ")}</td>
								<td className="action">
									<button>...</button>
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
