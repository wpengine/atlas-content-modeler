import React from "react";
import DashboardDisplay from "./DashboardDisplay";

export default function Dashboard() {
	let chartData = buildChartData();

	const modelChartOptions = {
		chart: {
			plotBackgroundColor: null,
			plotBorderWidth: null,
			plotShadow: false,
			type: "pie",
		},
		title: {
			text: "Models by Percent",
			style: {
				fontWeight: "bold",
				fontSize: "21px",
			},
		},
		tooltip: {
			pointFormat: "{series.name}: <b>{point.percentage:.1f}%</b>",
		},
		accessibility: {
			point: {
				valueSuffix: "%",
			},
		},
		plotOptions: {
			pie: {
				allowPointSelect: true,
				cursor: "pointer",
				dataLabels: {
					enabled: true,
					format: "<b>{point.name}</b>: {point.percentage:.1f} %",
				},
			},
		},
		series: [
			{
				name: "Models",
				colorByPoint: true,
				data: chartData,
			},
		],
	};

	/**
	 * Builds chart data for display in the dashboard
	 */
	function buildChartData() {
		let data = [];
		atlasContentModeler.stats.modelsCounts.map((entry) => {
			data.push({
				name: entry.plural,
				y: parseInt(entry.count),
			});
		});

		return data;
	}

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

	return (
		<DashboardDisplay
			taxonomies={taxonomies}
			modelChartOptions={modelChartOptions}
		/>
	);
}
