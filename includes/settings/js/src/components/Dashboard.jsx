import React from "react";
import DashboardDisplay from "./DashboardDisplay";

export default function Dashboard() {
	/**
	 * Get taxonomies for display in the dashboard
	 */
	function getTaxonomies() {
		let entries = [];
		let keys = Object.keys(atlasContentModeler.stats.taxonomies);

		keys.map((entry) => {
			entries.push({
				name: entry,
				count: parseInt(
					atlasContentModeler.stats.taxonomies[entry].total_terms
				),
			});
		});

		return entries;
	}

	return <DashboardDisplay taxonomies={getTaxonomies()} />;
}
