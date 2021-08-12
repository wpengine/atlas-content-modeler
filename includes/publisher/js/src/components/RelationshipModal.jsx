/* global atlasContentModelerFormEditingExperience */
import React, { useContext, useEffect, useState } from "react";
import { sprintf, __ } from "@wordpress/i18n";
import Modal from "react-modal";
const { wp } = window;
const { date, apiFetch } = wp;

Modal.setAppElement("#atlas-content-modeler-fields-app");

/**
 * The modal component for editing a relationship.
 *
 * @param {Object} field The relationship field.
 * @param {Boolean} isOpen Whether or not the model is open.
 * @param {Function} setIsOpen - Callback for opening and closing modal.
 * @returns {JSX.Element} Modal
 */
export default function RelationshipModal({ field, isOpen, setIsOpen }) {
	const [page, setPage] = useState(1);
	const [pagedEntries, setPagedEntries] = useState({});
	const [totalEntries, setTotalEntries] = useState(0);
	const [selectedEntry, setSelectedEntry] = useState(); // TODO: set initial state value from stored field value.
	const entriesPerPage = 1; // TODO: change to 5. 1 is for testing pagination only.
	const totalPages = Math.ceil(totalEntries / entriesPerPage);

	const customStyles = {
		overlay: {
			backgroundColor: "rgba(0, 40, 56, 0.7)",
			zIndex: 1000,
		},
		content: {
			top: "50%",
			left: "50%",
			right: "auto",
			bottom: "auto",
			transform: "translate(-50%, -50%)",
			border: "none",
			padding: "32px",
			boxSizing: "border-box",
		},
	};

	async function getEntries(page) {
		const { models } = atlasContentModelerFormEditingExperience;

		const endpoint = `/wp/v2/${
			models[field.reference].wp_rest_base
		}?per_page=${entriesPerPage}&page=${page}`;

		const params = {
			path: endpoint,
			parse: false, // So the response status and headers are available.
		};

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
				return;
			}

			if (page === 1) {
				setTotalEntries(response.headers.get("X-WP-Total"));
			}

			return response.json();
		});
	}

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
			<h2>{__("Select Reference", "atlas-content-modeler")}</h2>
			<p>
				{__(
					"Only published entries are displayed.",
					"atlas-content-modeler"
				)}
			</p>

			{page in pagedEntries ? (
				<div>
					<table className="table table-striped">
						<thead>
							<tr>
								<th></th>
								<th>{__("Title", "atlas-content-modeler")}</th>
								<th>
									{__(
										"Last modified",
										"atlas-content-modeler"
									)}
								</th>
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
										<td>
											<input
												type="radio"
												name="selected-entry"
												id={`entry-${id}`}
												value={id}
												aria-label={selectEntryLabel}
												checked={selectedEntry === id}
												onChange={() => {
													setSelectedEntry(id);
												}}
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
												{date.dateI18n(
													"F j, Y",
													modified
												)}
											</label>
										</td>
									</tr>
								);
							})}
						</tbody>
					</table>
					<p>
						{sprintf(
							__("Page %d of %d", "atlas-content-modeler"),
							page,
							totalPages
						)}
					</p>

					<button
						href="#"
						className="tertiary"
						disabled={page === 1}
						aria-label={__("First page", "atlas-content-modeler")}
						onClick={(event) => {
							event.preventDefault();
							setPage(1);
						}}
					>
						{"<<"}
					</button>
					<button
						href="#"
						className="tertiary"
						disabled={page === 1}
						aria-label={__(
							"Previous page",
							"atlas-content-modeler"
						)}
						onClick={(event) => {
							event.preventDefault();
							setPage(page - 1);
						}}
					>
						{"<"}
					</button>
					<button
						href="#"
						className="tertiary"
						disabled={page === totalPages}
						aria-label={__("Next page", "atlas-content-modeler")}
						onClick={(event) => {
							event.preventDefault();
							setPage(page + 1);
						}}
					>
						{">"}
					</button>
					<button
						href="#"
						className="tertiary"
						disabled={page === totalPages}
						aria-label={__("Last page", "atlas-content-modeler")}
						onClick={(event) => {
							event.preventDefault();
							setPage(totalPages);
						}}
					>
						{">>"}
					</button>
				</div>
			) : (
				<p>{__("No entries found.", "atlas-content-modeler")}</p>
			)}

			<button
				href="#"
				className="tertiary button button-large"
				onClick={(event) => {
					event.preventDefault();
					setSelectedEntry(undefined);
					setIsOpen(false);
				}}
			>
				{__("Cancel", "atlas-content-modeler")}
			</button>

			<button
				type="submit"
				disabled={typeof selectedEntry === "undefined"}
				className="button button-primary button-large"
				onClick={(event) => {
					event.preventDefault();
					// TODO: Update the reference field's value here.
					console.log(`Saving field ${selectedEntry}.`);
					setIsOpen(false);
				}}
			>
				{__("Save", "atlas-content-modeler")}
			</button>
		</Modal>
	);
}