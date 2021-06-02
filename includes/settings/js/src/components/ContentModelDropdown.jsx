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
	const { models, dispatch } = useContext(ModelsContext);
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
					Edit
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
						Open in GraphiQL
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
					Delete
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
				<h2>Delete the {plural} Content Model?</h2>
				<p>
					This is an irreversible action. You will have to recreate
					this model if you delete it.
				</p>
				<p>
					This will NOT delete actual data stored in this model. It
					only deletes the model definition.
				</p>
				<p>{`Are you sure you want to delete the ${plural} content model?`}</p>
				<button
					className="first warning"
					onClick={async () => {
						// delete model and remove related menu sidebar item
						await deleteModel(slug)
							.then((res) => {
								if (res.success) {
									removeSidebarMenuItem(slug);
								}
							})
							.catch(() => {
								showError(
									`There was an error. The ${slug} model type was not deleted.`
								);
							});

						setModalIsOpen(false);
						history.push(
							"/wp-admin/admin.php?page=atlas-content-modeler"
						);
						dispatch({ type: "removeModel", slug });
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
