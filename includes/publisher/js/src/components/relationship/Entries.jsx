import React from "react";
import Options from "./Options";
import Icon from "acm-icons";
import { __ } from "@wordpress/i18n";

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
				className="hidden"
				id={`atlas-content-modeler[${modelSlug}][${field.slug}][relationshipEntryId]`}
				name={`atlas-content-modeler[${modelSlug}][${field.slug}][relationshipEntryId]`}
				required={field?.required}
				type="text"
				value={selectedEntries}
				onChange={() => {}} // Prevents “You provided a `value` prop to a form field without an `onChange` handler.”
			/>
			<span className="error">
				<Icon type="error" />
				<span role="alert">
					{__("This field is required", "atlas-content-modeler")}
				</span>
			</span>
		</section>
	);
}
