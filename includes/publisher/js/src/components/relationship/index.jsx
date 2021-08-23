import React, { useState, useEffect } from "react";
import Icon from "acm-icons";
import RelationshipModal from "./modal";
import { sprintf, __ } from "@wordpress/i18n";
import Entries from "./Entries";
import Loader from "./Loader";
const { wp } = window;
const { apiFetch } = wp;

export default function Relationship({ field, modelSlug }) {
	const [editSingleRelModalIsOpen, setEditSingleRelModalIsOpen] = useState(
		false
	);
	const [entryInfo, setEntryInfo] = useState();
	const [isFetching, setIsFetching] = useState(false);
	const [selectedEntries, setSelectedEntries] = useState(
		field.value.split(",").filter(Boolean)
	);
	const { models } = atlasContentModelerFormEditingExperience;

	/**
	 * Gets the Reference field button label depending on field
	 * cardinality and current selection state.
	 *
	 * One-to-one with no item chosen => “Link Car”.
	 * One-to-many with items chosen => “Change Linked Cars”.
	 * One-to-one where reference model was deleted => “Link Reference”.
	 *
	 * @returns {string}
	 */
	function getButtonLabel() {
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

		return sprintf(buttonLabelBase, buttonLabelModel);
	}

	/**
	 * Retrieves related content information for display.
	 *
	 * @returns {object}
	 */
	async function getEntries() {
		const query = new URLSearchParams({
			include: selectedEntries,
		});

		const endpoint = `/wp/v2/${
			models[field.reference]?.wp_rest_base
		}/?=${query}`;

		const params = {
			path: endpoint,
			parse: false, // So the response status and headers are available.
		};

		setIsFetching(true);

		return apiFetch(params).then((response) => {
			if (response.status !== 200) {
				console.error(
					sprintf(
						__(
							/* translators: %s The HTTP error code, such as 200. */
							"Received %s error when fetching entries.",
							"atlas-content-modeler"
						),
						response.status
					)
				);
				setIsFetching(false);
				return;
			}

			setIsFetching(false);
			return response.json();
		});
	}

	/**
	 * Gets post information to display to the user outside of the modal.
	 */
	useEffect(() => {
		if (selectedEntries?.length < 1) {
			setEntryInfo([]);
			return;
		}
		getEntries().then(setEntryInfo);
	}, [selectedEntries]);

	return (
		<>
			<label
				htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
			>
				{field.name}
			</label>

			{isFetching ? (
				<Loader />
			) : entryInfo ? (
				<Entries
					entryInfo={entryInfo}
					modelSlug={modelSlug}
					field={field}
					selectedEntries={selectedEntries}
				/>
			) : (
				"" // No linked entries.
			)}

			<div className="d-flex flex-row align-items-center media-btns">
				<button
					className="button button-primary link-button"
					style={{ marginTop: "5px" }}
					id={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
					onClick={(e) => {
						e.preventDefault();
						setEditSingleRelModalIsOpen(true);
					}}
				>
					<div className="d-flex flex-row">
						<Icon type="link" />
						<div className="px-2">{getButtonLabel()}</div>
					</div>
				</button>
			</div>
			<RelationshipModal
				field={field}
				isOpen={editSingleRelModalIsOpen}
				setIsOpen={setEditSingleRelModalIsOpen}
				selectedEntries={selectedEntries}
				setSelectedEntries={setSelectedEntries}
			/>
		</>
	);
}
