import React from "react";
import { __ } from "@wordpress/i18n";

export default function Title({ field }) {
	const { models } = atlasContentModelerFormEditingExperience;

	const modelName =
		field?.cardinality === "one-to-one" ||
		field?.cardinality === "many-to-one"
			? models[field.reference]?.singular ??
			  __("Reference", "atlas-content-modeler")
			: models[field.reference]?.plural ??
			  __("References", "atlas-content-modeler");

	/* translators: the referenced model name, such as “Car” or “Cars”. */
	const title = sprintf(__("Select %s"), modelName);

	return (
		<>
			<h2>{title}</h2>
			<p className="mb-4">
				{__(
					"Only published entries are displayed.",
					"atlas-content-modeler"
				)}
			</p>
		</>
	);
}
