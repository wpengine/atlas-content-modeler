import React from "react";
import { __ } from "@wordpress/i18n";
import Icon from "acm-icons";

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

	const buttonLabelModel =
		field?.cardinality === "one-to-one"
			? models[field.reference]?.singular ??
			  __("Reference", "atlas-content-modeler")
			: models[field.reference]?.plural ??
			  __("References", "atlas-content-modeler");

	const buttonReverseLabelModel =
		field?.cardinality === "one-to-one"
			? models[field.forwardSlug]?.singular ??
			  __("Reference", "atlas-content-modeler")
			: models[field.forwardSlug]?.plural ??
			  __("References", "atlas-content-modeler");

	const buttonLabel = sprintf(buttonLabelBase, buttonLabelModel);

	const buttonReverseLabel = sprintf(
		buttonLabelBase,
		buttonReverseLabelModel
	);

	return (
		<div className="d-flex flex-row align-items-center media-btns">
			<button
				className="button button-primary link-button"
				style={{ marginTop: "5px" }}
				type="button"
				id={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
				onClick={(e) => {
					e.preventDefault();
					setRelationshipModalIsOpen(true);
				}}
			>
				<div className="d-flex flex-row">
					<Icon type="link" />
					<div className="px-2">
						{modelSlug !== field.forwardSlug
							? buttonReverseLabel
							: buttonLabel}
					</div>
				</div>
			</button>
		</div>
	);
}
