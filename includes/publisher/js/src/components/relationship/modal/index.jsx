/* global atlasContentModelerFormEditingExperience */
import React, { useEffect, useState, useRef } from "react";
import { sprintf, __ } from "@wordpress/i18n";
import Modal from "react-modal";
import Table from "./Table";
import Pagination from "./Pagination";
import Loader from "../Loader";
const { wp } = window;
const { apiFetch } = wp;

/**
 * The modal component for editing a relationship.
 *
 * @param {Object} field The relationship field.
 * @param {Boolean} isOpen Whether or not the modal is open.
 * @param {Function} setIsOpen Callback for opening and closing modal.
 * @returns {JSX.Element} RelationshipModal
 */
export default function RelationshipModal({
	field,
	isOpen,
	setIsOpen,
	selectedEntries,
	setSelectedEntries,
}) {
	const [page, setPage] = useState(1);
	const [pagedEntries, setPagedEntries] = useState({});
	const [totalEntries, setTotalEntries] = useState(0);
	const [chosenEntries, setChosenEntries] = useState(selectedEntries);
	const [isFetching, setIsFetching] = useState(false);
	const savedEntries = useRef(); // To revert changes if the modal is closed via Cancel.
	const entriesPerPage = 5;
	const totalPages = Math.ceil(totalEntries / entriesPerPage);

	const customStyles = {
		overlay: {
			backgroundColor: "rgba(0, 40, 56, 0.7)",
			zIndex: 1000,
		},
		content: {
			top: "50%",
			left: "50%",
			width: "55%",
			right: "auto",
			bottom: "auto",
			transform: "translate(-50%, -50%)",
			border: "none",
			padding: "32px",
			boxSizing: "border-box",
		},
	};

	/**
	 * Handles the selection of checkbox or radio button.
	 *
	 * @param {object} event
	 */
	function handleSelect(event) {
		const { value, checked, type } = event.target;
		let savedValues = [];
		if (type === "checkbox") {
			if (!checked) {
				savedValues = chosenEntries.filter((item) => item !== value);
			} else {
				savedValues = [...new Set([...chosenEntries, value])];
			}
		} else {
			savedValues = [value];
		}
		setChosenEntries(savedValues);
	}

	/**
	 * Gets the modal title depending on field cardinality.
	 *
	 * @returns {string}
	 */
	function getModalTitle() {
		const { models } = atlasContentModelerFormEditingExperience;
		const modelName =
			field?.cardinality === "one-to-one"
				? models[field.reference]?.singular ??
				  __("Reference", "atlas-content-modeler")
				: models[field.reference]?.plural ??
				  __("References", "atlas-content-modeler");

		/* translators: the referenced model name, such as “Car” or “Cars”. */
		return sprintf(__("Select %s"), modelName);
	}

	async function getEntries(page) {
		const { models } = atlasContentModelerFormEditingExperience;

		const endpoint = `/wp/v2/${
			models[field.reference]?.wp_rest_base
		}?per_page=${entriesPerPage}&page=${page}`;

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

			if (page === 1) {
				setTotalEntries(response.headers.get("X-WP-Total"));
			}

			setIsFetching(false);
			return response.json();
		});
	}

	/**
	 * Hides the main app from screen readers when the modal is open.
	 */
	useEffect(() => {
		Modal.setAppElement("#atlas-content-modeler-fields-app");
	}, []);

	/**
	 * Gets entries whenever the state of 'page' changes.
	 * Caches those entries in the pagedEntries object, keyed by page.
	 */
	useEffect(() => {
		if (isOpen && !(page in pagedEntries)) {
			getEntries(page).then((entries) => {
				setPagedEntries((pagedEntries) => {
					return { ...pagedEntries, [page]: entries };
				});
			});
		}
	}, [isOpen, page]);

	/**
	 * Stores current state of selectedEntries when the modal opens.
	 * Allows us to revert to that state if a user makes changes
	 * to selected entries, but then clicks the Cancel button.
	 */
	useEffect(() => {
		if (isOpen) {
			savedEntries.current = selectedEntries;
		}
	}, [isOpen]);

	return (
		<Modal
			isOpen={isOpen}
			contentLabel={`Creating relationship with ${field.name}`}
			parentSelector={() => {
				return document.getElementById(
					"atlas-content-modeler-fields-app"
				);
			}}
			portalClassName="atlas-content-modeler-edit-model-modal-container atlas-content-modeler"
			onRequestClose={() => {
				setIsOpen(false);
			}}
			field={field}
			style={customStyles}
		>
			<h2>{getModalTitle()}</h2>
			<p className="mb-4">
				{__(
					"Only published entries are displayed.",
					"atlas-content-modeler"
				)}
			</p>
			{isFetching ? (
				<Loader />
			) : page in pagedEntries ? (
				<>
					<Table
						pagedEntries={pagedEntries}
						page={page}
						field={field}
						chosenEntries={chosenEntries}
						handleSelect={handleSelect}
					/>
					<Pagination
						totalPages={totalPages}
						page={page}
						setPage={setPage}
					/>
				</>
			) : (
				<p>{__("No entries found.", "atlas-content-modeler")}</p>
			)}
			<div className="d-flex flex-row-reverse mt-2">
				<button
					type="submit"
					className="action-button mx-3"
					onClick={(event) => {
						event.preventDefault();
						setSelectedEntries(chosenEntries);
						setIsOpen(false);
					}}
				>
					{__("Save", "atlas-content-modeler")}
				</button>
				<button
					href="#"
					className="tertiary mx-0"
					onClick={(event) => {
						event.preventDefault();
						setChosenEntries(savedEntries.current);
						setIsOpen(false);
					}}
				>
					{__("Cancel", "atlas-content-modeler")}
				</button>
			</div>
		</Modal>
	);
}
