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
	const { models, restBases } = atlasContentModelerFormEditingExperience;
	const isBlockEditor = document.body.classList.contains("block-editor-page");

	/**
	 * Retrieves related content information for display.
	 *
	 * @returns {object}
	 */
	async function getEntries() {
		const query = new URLSearchParams({
			include: selectedEntries,
		});

		const endpoint = `/wp/v2/${restBases[field.reference]}/?=${query}`;

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
		if (isBlockEditor) {
			wp.data.dispatch("core/editor").editPost({
				meta: { [field.slug]: selectedEntries.join() },
			});
		}

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
			{field?.required && (
				<p className="required">
					{__("*Required", "atlas-content-modeler")}
				</p>
			)}
			{field?.description && (
				<p className="help mb-0">
					{__(field.description, "atlas-content-modeler")}
				</p>
			)}
			{isFetching ? (
				<Loader />
			) : entryInfo ? (
				<Entries
					entryInfo={entryInfo}
					modelSlug={modelSlug}
					field={field}
					selectedEntries={selectedEntries}
					setSelectedEntries={setSelectedEntries}
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
