import React, {
	useState,
	useEffect,
	useRef,
	useCallback,
	useContext,
} from "react";
import Icon from "../../../../components/icons";
import Modal from "react-modal";
import { maybeCloseDropdown } from "../utils";
import { sprintf, __ } from "@wordpress/i18n";
import { ModelsContext } from "../ModelsContext";

const { apiFetch } = wp;

export const TaxonomiesDropdown = ({ taxonomy }) => {
	const [dropdownOpen, setDropdownOpen] = useState(false);
	const [modalIsOpen, setModalIsOpen] = useState(false);
	const { taxonomiesDispatch } = useContext(ModelsContext);

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
				className="options py-sm-0 py-2"
				onBlur={() => maybeCloseDropdown(setDropdownOpen, timer)}
				onClick={() => setDropdownOpen(!dropdownOpen)}
				aria-label={sprintf(
					__("Options for the %s taxonomy.", "atlas-content-modeler"),
					taxonomy.plural
				)}
			>
				<Icon type="options" />
			</button>
			<div className={`dropdown-content ${dropdownOpen ? "" : "hidden"}`}>
				<a
					href="#"
					aria-label={sprintf(
						__("Edit the %s taxonomy.", "atlas-content-modeler"),
						taxonomy.plural
					)}
					onBlur={() => maybeCloseDropdown(setDropdownOpen, timer)}
					onClick={(event) => {
						event.preventDefault();
						// TODO: implement editing here.
						alert("TODO: Implement editing here.");
						setDropdownOpen(false);
					}}
				>
					{__("Edit", "atlas-content-modeler")}
				</a>
				<a
					className="delete"
					href="#"
					aria-label={sprintf(
						__("Delete the %s taxonomy.", "atlas-content-modeler"),
						taxonomy.plural
					)}
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
				contentLabel={sprintf(
					__("Delete the %s taxonomy?", "atlas-content-modeler"),
					taxonomy.plural
				)}
				portalClassName="atlas-content-modeler-delete-field-modal-container"
				onRequestClose={() => {
					setModalIsOpen(false);
				}}
				// taxonomy={taxonomy}
				style={customStyles}
			>
				<h2>
					{sprintf(
						__("Delete the %s taxonomy?", "atlas-content-modeler"),
						taxonomy.plural
					)}
				</h2>
				<p>
					{__(
						"This will delete the taxonomy and related data.",
						"atlas-content-modeler"
					)}
				</p>
				<p>
					{sprintf(
						__(
							"Are you sure you want to delete the %s taxonomy? ",
							"atlas-content-modeler"
						),
						taxonomy.plural
					)}
				</p>
				<button
					type="submit"
					form={taxonomy.slug}
					className="first warning"
					onClick={() => {
						apiFetch({
							path: `/wpe/atlas/taxonomy/${taxonomy.slug}`,
							method: "DELETE",
							_wpnonce: wpApiSettings.nonce,
						});
						setModalIsOpen(false);
						taxonomiesDispatch({
							type: "removeTaxonomy",
							slug: taxonomy.slug,
						});
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
		</span>
	);
};
