import React, { useState, useEffect } from "react";
import RelationshipModal from "./modal";
import { sprintf, __ } from "@wordpress/i18n";
import Entries from "./Entries";
import Loader from "./Loader";
import LinkButton from "./LinkButton";
const { wp } = window;
const { apiFetch } = wp;

export default function Relationship({ field, modelSlug }) {
	const [relationshipModalIsOpen, setRelationshipModalIsOpen] = useState(
		false
	);
	const [entryInfo, setEntryInfo] = useState();
	const [isFetching, setIsFetching] = useState(false);
	const [selectedEntries, setSelectedEntries] = useState(
		field.value.split(",").filter(Boolean)
	);
	const { models } = atlasContentModelerFormEditingExperience;

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
			<LinkButton
				field={field}
				models={models}
				modelSlug={modelSlug}
				selectedEntries={selectedEntries}
				setRelationshipModalIsOpen={setRelationshipModalIsOpen}
			/>
			<RelationshipModal
				field={field}
				isOpen={relationshipModalIsOpen}
				setIsOpen={setRelationshipModalIsOpen}
				selectedEntries={selectedEntries}
				setSelectedEntries={setSelectedEntries}
			/>
		</>
	);
}
