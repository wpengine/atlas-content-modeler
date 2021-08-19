/* global atlasContentModeler */
import React, {
	useContext,
	useEffect,
	useState,
	useRef,
	useCallback,
} from "react";
import { ModelsContext } from "../ModelsContext";
import Icon from "../../../../components/icons";
import Modal from "react-modal";
import { EditModelModal } from "./EditModelModal";
import { useHistory } from "react-router-dom";
import {
	getGraphiQLLink,
	maybeCloseDropdown,
	removeSidebarMenuItem,
} from "../utils";
import { showError } from "../toasts";
import { sprintf, __ } from "@wordpress/i18n";

Modal.setAppElement("#root");

const { apiFetch } = wp;

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

export const ContentModelDropdown = ({ model }) => {
	const { plural, slug } = model;
	const { models, dispatch, taxonomiesDispatch } = useContext(ModelsContext);
	const history = useHistory();
	const [dropdownOpen, setDropdownOpen] = useState(false);
	const [modalIsOpen, setModalIsOpen] = useState(false);
	const [editModelModalIsOpen, setEditModelModalIsOpen] = useState(false);
	const timer = useRef(null);

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

	const handleKeyPress = useCallback(
		(e) => {
			if (e.key === "Escape") {
				setDropdownOpen(false);
			}
		},
		[setDropdownOpen]
	);

	useEffect(() => {
		if (dropdownOpen) {
			document.addEventListener("keydown", handleKeyPress);
		} else {
			document.removeEventListener("keydown", handleKeyPress);
		}

		return () => document.removeEventListener("keydown", handleKeyPress);
	}, [dropdownOpen, handleKeyPress]);

	useEffect(() => {
		return () => clearTimeout(timer.current);
	}, [timer]);

	return (
		<span className="dropdown">
			<button
				className="options"
				aria-label={`Options for ${plural} content model`}
				onClick={() => {
					setDropdownOpen(!dropdownOpen);
				}}
				onBlur={() => maybeCloseDropdown(setDropdownOpen, timer)}
			>
				<Icon type="options" />
			</button>
			<div className={`dropdown-content ${dropdownOpen ? "" : "hidden"}`}>
				<a
					className="edit"
					href="#"
					onBlur={() => maybeCloseDropdown(setDropdownOpen, timer)}
					onClick={(event) => {
						event.preventDefault();
						setDropdownOpen(false);
						setEditModelModalIsOpen(true);
					}}
				>
					{__("Edit", "atlas-content-modeler")}
				</a>
				{atlasContentModeler.isGraphiQLAvailable && (
					<a
						className="show-in-graphiql"
						href={getGraphiQLLink(models[slug])}
						target="_blank"
						rel="noopener noreferrer"
						onBlur={() =>
							maybeCloseDropdown(setDropdownOpen, timer)
						}
						onClick={() => {
							setDropdownOpen(false);
						}}
					>
						{__("Open in GraphiQL", "atlas-content-modeler")}
					</a>
				)}
				<a
					className="delete"
					href="#"
					onBlur={() => maybeCloseDropdown(setDropdownOpen, timer)}
					onClick={(event) => {
						event.preventDefault();
						setDropdownOpen(false);
						setModalIsOpen(true);
					}}
				>
					{__("Delete", "atlas-content-modeler")}
				</a>
			</div>
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
						__(
							"Delete the %s Content Model?",
							"atlas-content-modeler"
						),
						plural
					)}
				</h2>
				<p>
					{__(
						"This is an irreversible action. You will have to recreate this model if you delete it.",
						"atlas-content-modeler"
					)}
				</p>
				<p>
					{__(
						"This will NOT delete actual data stored in this model. It only deletes the model definition.",
						"atlas-content-modeler"
					)}
				</p>
				<p>
					{sprintf(
						__(
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
						// delete model and remove related menu sidebar item
						await deleteModel(slug)
							.then((res) => {
								if (res.success) {
									removeSidebarMenuItem(slug);
									taxonomiesDispatch({
										type: "removeModel",
										slug,
									});
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

						setModalIsOpen(false);
						history.push(atlasContentModeler.appPath);
					}}
				>
					Delete
				</button>
				<button
					className="tertiary"
					onClick={() => {
						setModalIsOpen(false);
					}}
				>
					Cancel
				</button>
			</Modal>
			<EditModelModal
				model={model}
				isOpen={editModelModalIsOpen}
				setIsOpen={setEditModelModalIsOpen}
			/>
		</span>
	);
};
