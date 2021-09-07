import React, { useEffect, useContext } from "react";
import Modal from "react-modal";
import { ModelsContext } from "../ModelsContext";
import { sprintf, __ } from "@wordpress/i18n";
import { removeSidebarMenuItem } from "../utils";
import { getRelationships } from "../queries";
import { showError } from "../toasts";
import { useHistory } from "react-router-dom";

const { apiFetch } = wp;

export function DeleteModelModal({ modalIsOpen, setModalIsOpen, model }) {
	const { models, dispatch, taxonomiesDispatch } = useContext(ModelsContext);
	const { plural, slug } = model;
	const history = useHistory();
	const relationships = getRelationships(models, slug);

	const customStyles = {
		overlay: {
			backgroundColor: "rgba(0, 40, 56, 0.7)",
		},
		content: {
			top: "50%",
			left: "50%",
			right: "auto",
			bottom: "auto",
			marginRight: "-50%",
			transform: "translate(-50%, -50%)",
			border: "none",
			padding: "40px",
		},
	};

	function deleteModel(name = "") {
		if (!name.length) {
			return;
		}

		return apiFetch({
			path: `/wpe/atlas/content-model/${name}`,
			method: "DELETE",
			_wpnonce: wpApiSettings.nonce,
		});
	}

	useEffect(() => {
		Modal.setAppElement("#root");
	}, []);

	return (
		<Modal
			isOpen={modalIsOpen}
			contentLabel={`Delete the ${plural} content model?`}
			portalClassName="atlas-content-modeler-delete-model-modal-container"
			onRequestClose={() => {
				setModalIsOpen(false);
			}}
			style={customStyles}
			model={model}
		>
			<h2>
				{sprintf(
					__("Delete the %s Content Model?", "atlas-content-modeler"),
					plural
				)}
			</h2>
			<p>
				<strong>
					{__(
						"This is an irreversible action.",
						"atlas-content-modeler"
					)}
				</strong>
			</p>
			<ul>
				{relationships?.length > 0 && (
					<li className="warning">
						{sprintf(
							__(
								// translators: plural form of the model name, such as “Cars”.
								"Relationship fields and data linked to %s will be removed.",
								"atlas-content-modeler"
							),
							plural
						)}
					</li>
				)}
				<li>
					{__(
						"The model definition will be deleted.",
						"atlas-content-modeler"
					)}
				</li>
				<li>
					{__(
						"Entries for this model will NOT be deleted.",
						"atlas-content-modeler"
					)}
				</li>
			</ul>

			<p>
				{sprintf(
					__(
						// translators: plural form of the model name, such as “Cars”.
						"Are you sure you want to delete the %s content model?",
						"atlas-content-modeler"
					),
					plural
				)}
			</p>
			<button
				className="first warning"
				onClick={async () => {
					// Optimistically remove the model from the UI.
					dispatch({ type: "removeModel", slug });

					await deleteModel(slug)
						.then((res) => {
							if (res.success) {
								removeSidebarMenuItem(slug);
								taxonomiesDispatch({
									type: "removeModel",
									slug,
								});
								if (relationships?.length > 0) {
									dispatch({
										type: "removeFields",
										fields: relationships,
									});
								}
							} else {
								// Restore the model in the UI since deletion failed.
								dispatch({ type: "addModel", data: model });
							}
						})
						.catch(() => {
							showError(
								sprintf(
									__(
										"There was an error. The %s model type was not deleted.",
										"atlas-content-modeler"
									),
									slug
								)
							);
							// Restore the model in the UI since deletion failed.
							dispatch({ type: "addModel", data: model });
						});

					history.push(atlasContentModeler.appPath);
				}}
			>
				{__("Delete", "atlas-content-modeler")}
			</button>
			<button
				className="tertiary"
				onClick={() => {
					setModalIsOpen(false);
				}}
			>
				{__("Cancel", "atlas-content-modeler")}
			</button>
		</Modal>
	);
}
