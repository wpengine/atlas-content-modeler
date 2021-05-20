import React, {
	useState,
	useContext,
	useEffect,
	useRef,
	useCallback,
} from "react";
import Icon from "../../../../../components/icons";
import Modal from "react-modal";
import { ModelsContext } from "../../ModelsContext";
import { maybeCloseDropdown } from "../../utils";

const { apiFetch } = wp;

export const FieldOptionsDropdown = ({ field, model }) => {
	const [dropdownOpen, setDropdownOpen] = useState(false);
	const [modalIsOpen, setModalIsOpen] = useState(false);
	const { dispatch } = useContext(ModelsContext);
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
				aria-label={`Options for the ${field.name} field.`}
			>
				<Icon type="options" />
			</button>
			<div className={`dropdown-content ${dropdownOpen ? "" : "hidden"}`}>
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
				contentLabel={`Delete the ${field.name} field from ${model.plural}?`}
				portalClassName="wpe-content-model-delete-field-modal-container"
				onRequestClose={() => {
					setModalIsOpen(false);
				}}
				field={field}
				style={customStyles}
			>
				<h2>
					Delete the {field.name} field from {model.plural}?
				</h2>
				<p>
					This will not delete actual data stored in this field. It
					only deletes the field definition.
				</p>
				<p>
					Are you sure you want to delete the {field.name} field from{" "}
					{model.plural}?
				</p>
				<button
					type="submit"
					form={field.id}
					className="first warning"
					onClick={async () => {
						apiFetch({
							path: `/wpe/content-model-field/${field.id}`,
							method: "DELETE",
							body: JSON.stringify({ model: model.slug }),
							_wpnonce: wpApiSettings.nonce,
						});
						setModalIsOpen(false);
						dispatch({
							type: "removeField",
							id: field.id,
							model: model.slug,
						});
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
		</span>
	);
};
