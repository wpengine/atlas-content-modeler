import React from "react";
import { sprintf, __ } from "@wordpress/i18n";
const { wp } = window;
const { date } = wp;

export default function Table({
	pagedEntries,
	page,
	field,
	chosenEntries,
	handleSelect,
}) {
	return (
		<table className="table table-striped mt-2">
			<thead>
				<tr>
					<th></th>
					<th>{__("Title", "atlas-content-modeler")}</th>
					<th>{__("Last modified", "atlas-content-modeler")}</th>
				</tr>
			</thead>
			<tbody>
				{pagedEntries[page].map((entry) => {
					const { modified, id, title } = entry;
					const selectEntryLabel = sprintf(
						__(
							/* translators: %s The name of the entry title. */
							"Link the entry titled “%s” to this entry.",
							"atlas-content-modeler"
						),
						title?.rendered
					);
					return (
						<tr key={id}>
							<td className="checkbox">
								<input
									type={
										field.cardinality == "one-to-many"
											? "checkbox"
											: "radio"
									}
									name="selected-entry"
									id={`entry-${id}`}
									value={id}
									aria-label={selectEntryLabel}
									defaultChecked={chosenEntries.includes(
										id.toString()
									)}
									onChange={handleSelect}
								/>
							</td>
							<td>
								<label
									htmlFor={`entry-${id}`}
									aria-label={selectEntryLabel}
								>
									{title?.rendered}
								</label>
							</td>
							<td>
								<label
									htmlFor={`entry-${id}`}
									aria-label={selectEntryLabel}
								>
									{date.dateI18n("F j, Y", modified)}
								</label>
							</td>
						</tr>
					);
				})}
			</tbody>
		</table>
	);
}
