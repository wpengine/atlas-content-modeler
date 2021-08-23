import React from "react";

export default function Entries({
	entryInfo,
	modelSlug,
	field,
	selectedEntries,
}) {
	return (
		<section className="card-content">
			<ul className="model-list">
				{entryInfo?.map((entry) => {
					const { title } = entry;
					return (
						<li key={entry.id}>
							<div className="relation-model-card flex-wrap d-flex flex-column d-sm-flex flex-sm-row">
								<span className="flex-item mb-3 mb-sm-0 pr-1">
									<p className="value">
										<strong>{title.rendered}</strong>
									</p>
								</span>
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
