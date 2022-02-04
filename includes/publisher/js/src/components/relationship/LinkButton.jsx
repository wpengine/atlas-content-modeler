import React from "react";
import { __ } from "@wordpress/i18n";
import Icon from "acm-icons";
import { DarkButton } from "../../../../../shared-assets/js/components/Buttons";

export default function LinkButton({
	field,
	models,
	modelSlug,
	selectedEntries,
	setRelationshipModalIsOpen,
}) {
	const buttonLabelBase =
		selectedEntries?.length > 0
			? /* translators: the name of the related model, such as "Car" or "Cars" */
			  __("Change Linked %s", "atlas-content-modeler")
			: /* translators: the name of the related model, such as "Car" or "Cars" */
			  __("Link %s", "atlas-content-modeler");

	let buttonLabelModel =
		models[field.reference]?.singular ??
		__("Reference", "atlas-content-modeler");

	if (
		field?.cardinality === "many-to-many" ||
		field?.cardinality === "one-to-many"
	) {
		buttonLabelModel =
			models[field.reference]?.plural ??
			__("References", "atlas-content-modeler");
	}

	const buttonLabel = sprintf(buttonLabelBase, buttonLabelModel);

	return (
		<div className="d-flex flex-row align-items-center media-btns">
			<DarkButton
				data-testid="content-model-relationship-button"
				style={{ marginTop: "5px" }}
				id={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
				onClick={(e) => {
					e.preventDefault();
					setRelationshipModalIsOpen(true);
				}}
			>
				<div className="d-flex flex-row">
					<Icon type="link" />
					<div className="px-2">{buttonLabel}</div>
				</div>
			</DarkButton>
		</div>
	);
}
