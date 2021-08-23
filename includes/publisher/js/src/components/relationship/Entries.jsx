import React from "react";
import Options from "./Options";

export default function Entries({
	entryInfo,
	modelSlug,
	field,
	selectedEntries,
	setSelectedEntries,
}) {
	return (
		<section className="card-content">
			<ul className="model-list">
				{entryInfo?.map((entry) => {
					const { title } = entry;
					return (
						<li
							key={entry.id}
							className="d-flex flex-column d-sm-flex flex-sm-row justify-content-start"
						>
							<div className="relation-model-card flex-wrap d-flex flex-column d-sm-flex flex-sm-row">
								<span className="flex-item mb-3 mb-sm-0 pr-1">
									<p className="value">
										<strong>{title.rendered}</strong>
									</p>
								</span>
							</div>
							<div className="order-0 my-2 my-sm-0 text-end order-sm-2 ms-sm-auto">
								<Options
									entry={entry}
									setSelectedEntries={setSelectedEntries}
								/>
							</div>
						</li>
					);
				})}
			</ul>
			<input
				name={`atlas-content-modeler[${modelSlug}][${field.slug}][relationshipEntryId]`}
				value={selectedEntries}
				type="hidden"
			/>
		</section>
	);
}
