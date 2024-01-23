import React, { useState, useEffect, useRef } from "react";
import RelationshipModal from "./modal";
import { sprintf, __ } from "@wordpress/i18n";
import Entries from "./Entries";
import Loader from "./Loader";
import LinkButton from "./LinkButton";
import usePageVisibility from "../../../../../shared-assets/js/hooks/usePageVisibility";
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
	const pageIsVisible = usePageVisibility();
	const pageLostFocus = useRef(false);

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
	 * Refetches entry data if the page loses and regains focus. The user may
	 * edit or remove a related entry in another tab. Refetching ensures we
	 * display updated titles or remove deleted entries when a user returns.
	 */
	useEffect(() => {
		if (!pageIsVisible) {
			pageLostFocus.current = true;
		}

		if (
			pageLostFocus.current &&
			pageIsVisible &&
			selectedEntries?.length > 0 &&
			!relationshipModalIsOpen
		) {
			pageLostFocus.current = false;
			(async () => {
				getEntries().then(setEntryInfo);
			})();
		}
	}, [pageIsVisible, pageLostFocus, relationshipModalIsOpen]);

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
