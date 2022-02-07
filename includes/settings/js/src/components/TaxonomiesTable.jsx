import React from "react";
import { __ } from "@wordpress/i18n";
import { TaxonomiesDropdown } from "./TaxonomiesDropdown";

const TaxonomiesTableHead = () => {
	return (
		<thead>
			<tr>
				<th>{__("Name", "atlas-content-modeler")}</th>
				<th>{__("ID", "atlas-content-modeler")}</th>
				<th>{__("Models", "atlas-content-modeler")}</th>
				<th className="action">
					{__("Action", "atlas-content-modeler")}
				</th>
			</tr>
		</thead>
	);
};

const TaxonomiesTable = ({ taxonomies = {} }) => {
	if (Object.values(taxonomies).length < 1) {
		return (
			<table className="table table-striped">
				<TaxonomiesTableHead />
				<tbody>
					<tr>
						<td colSpan="4" className="text-center p-3">
							{__(
								"You currently have no taxonomies.",
								"atlas-content-modeler"
							)}
						</td>
					</tr>
				</tbody>
			</table>
		);
	}

	return (
		<>
			<table className="table table-striped">
				<TaxonomiesTableHead />
				<tbody>
					{Object.values(taxonomies).map((taxonomy) => {
						return (
							<tr key={taxonomy?.slug}>
								<td>{taxonomy?.plural}</td>
								<td>{taxonomy?.slug}</td>
								<td>{taxonomy?.types?.join(", ")}</td>
								<td className="action right">
									<div>
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
