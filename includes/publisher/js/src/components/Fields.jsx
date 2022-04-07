import React from "react";
import Field from "./Field";
import { __ } from "@wordpress/i18n";
import { getFieldOrder } from "../../../../settings/js/src/queries";

export default function Fields(props) {
	const { model } = props;
	const { fields } = model;
	const fieldOrder = getFieldOrder(fields);

	if (fieldOrder?.length < 1) {
		return (
			<p>
				{__(
					"No fields were found for this entry's model.",
					"atlas-content-modeler"
				)}
			</p>
		);
	}

	return fieldOrder.map((fieldKey, i) => {
		const field = fields[fieldKey];
		return (
			<Field
				field={field}
				modelSlug={model.slug}
				key={fieldKey}
				first={i === 0} // To help focus the first field.
			/>
		);
	});
}
