/* global atlasContentModeler */
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
import { sprintf, __ } from "@wordpress/i18n";
import { sendEvent } from "acm-analytics";
import {
	TertiaryButton,
	WarningButton,
} from "../../../../../shared-assets/js/components/Buttons";
import { Dropdown } from "../../../../../shared-assets/js/components/Dropdown";

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
		<Dropdown>
			<button
				className="options py-sm-0 py-2"
				onBlur={() => maybeCloseDropdown(setDropdownOpen, timer)}
				onClick={() => setDropdownOpen(!dropdownOpen)}
				aria-label={sprintf(
					__("Options for the %s field.", "atlas-content-modeler"),
					field.name
				)}
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
				contentLabel={sprintf(
					__("Delete the %s field from %s?", "atlas-content-modeler"),
					field.name,
					model.plural
				)}
				portalClassName="atlas-content-modeler-delete-field-modal-container"
				onRequestClose={() => {
					setModalIsOpen(false);
				}}
				field={field}
				style={customStyles}
			>
				<h2>
					{sprintf(
						__(
							"Delete the %s field from %s?",
							"atlas-content-modeler"
						),
						field.name,
						model.plural
					)}
				</h2>
				<p>
					{__(
						"This will not delete actual data stored in this field. It only deletes the field definition.",
						"atlas-content-modeler"
					)}
				</p>
				<p>
					{sprintf(
						__(
							"Are you sure you want to delete the %s field from %s? ",
							"atlas-content-modeler"
						),
						field.name,
						model.plural
					)}
				</p>
				<WarningButton
					type="submit"
					form={field.id}
					className="first"
					data-testid="delete-model-field-button"
					onClick={async () => {
						apiFetch({
							path: `/wpe/atlas/content-model-field/${field.id}`,
							method: "DELETE",
							body: JSON.stringify({ model: model.slug }),
							_wpnonce: wpApiSettings.nonce,
						}).then(() => {
							sendEvent({
								category: "Fields",
								action: "Field Deleted",
							});
						});
						setModalIsOpen(false);
						dispatch({
							type: "removeField",
							id: field.id,
							model: model.slug,
						});
					}}
				>
					{__("Delete", "atlas-content-modeler")}
				</WarningButton>
				<TertiaryButton
					data-testid="delete-model-field-cancel-button"
					onClick={() => {
						setModalIsOpen(false);
					}}
				>
					{__("Cancel", "atlas-content-modeler")}
				</TertiaryButton>
			</Modal>
		</Dropdown>
	);
};
