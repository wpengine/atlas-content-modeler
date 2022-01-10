import React, { useEffect, useCallback } from "react";
import { sprintf, __ } from "@wordpress/i18n";
import Modal from "react-modal";

/**
 * The modal component to confirm trashing a post.
 *
 * @param {Boolean} isOpen Whether or not the modal is open.
 * @param {Function} setIsOpen Callback for opening and closing modal.
 * @returns {JSX.Element} TrashPostModal
 */
export default function TrashPostModal({ isOpen, setIsOpen }) {
	const { models, postType } = atlasContentModelerFormEditingExperience;

	const modelName =
		models[postType]?.singular ?? __("Entry", "atlas-content-modeler");

	const moveToTrashLink = document.querySelector("#delete-action a");

	const customStyles = {
		overlay: {
			backgroundColor: "rgba(0, 40, 56, 0.7)",
			zIndex: 1000,
		},
		content: {
			top: "50%",
			left: "50%",
			minWidth: "40%",
			right: "auto",
			bottom: "auto",
			transform: "translate(-50%, -50%)",
			border: "none",
			padding: "32px",
			boxSizing: "border-box",
		},
	};

	const handleClick = useCallback((event) => {
		if (atlasContentModelerFormEditingExperience?.postHasReferences) {
			event.preventDefault();
			setIsOpen(true);
		}
	}, []);

	useEffect(() => {
		moveToTrashLink.addEventListener("click", handleClick);
		return () => {
			moveToTrashLink.removeEventListener("click", handleClick);
		};
	}, [handleClick]);

	/**
	 * Hides the main app from screen readers when the modal is open.
	 */
	useEffect(() => {
		Modal.setAppElement("#atlas-content-modeler-fields-app");
	}, []);

	return (
		<Modal
			isOpen={isOpen}
			contentLabel={`Confirm moving this linked entry to the trash`}
			parentSelector={() => {
				return document.getElementById(
					"atlas-content-modeler-fields-app"
				);
			}}
			portalClassName="atlas-content-modeler-trash-modal-container atlas-content-modeler"
			onRequestClose={() => {
				setIsOpen(false);
			}}
			style={customStyles}
		>
			<h2>
				{sprintf(
					// translators: singular model name or the word “entry”.
					__("Trash this %s?", "atlas-content-modeler"),
					modelName
				)}
			</h2>
			<p className="mb-4">
				<strong>
					{__(
						"This entry is linked to another entry via a relationship field.",
						"atlas-content-modeler"
					)}
				</strong>
			</p>

			<p className="mb-4">
				{__(
					"Moving this to the trash will remove the relationship when the trash is cleared.",
					"atlas-content-modeler"
				)}
			</p>

			<div className="d-flex flex-row mt-2">
				<button
					type="submit"
					className="warning"
					onClick={(event) => {
						event.preventDefault();
						setIsOpen(false);
						window.location = moveToTrashLink.getAttribute("href");
					}}
				>
					{__("Move to Trash", "atlas-content-modeler")}
				</button>
				<button
					href="#"
					className="tertiary"
					onClick={(event) => {
						event.preventDefault();
						setIsOpen(false);
					}}
				>
					{__("Cancel", "atlas-content-modeler")}
				</button>
			</div>
		</Modal>
	);
}
