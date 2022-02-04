import React, {
	useState,
	useEffect,
	useRef,
	useCallback,
	useContext,
} from "react";
import { useHistory } from "react-router-dom";
import Icon from "../../../../components/icons";
import Modal from "react-modal";
import { maybeCloseDropdown } from "../utils";
import { sprintf, __ } from "@wordpress/i18n";
import { ModelsContext } from "../ModelsContext";
import { showError } from "../toasts";
import {
	WarningButton,
	TertiaryButton,
} from "../../../../shared-assets/js/components/Buttons";
import { Dropdown } from "../../../../shared-assets/js/components/Dropdown";

const { apiFetch } = wp;

export const TaxonomiesDropdown = ({ taxonomy }) => {
	const [dropdownOpen, setDropdownOpen] = useState(false);
	const [modalIsOpen, setModalIsOpen] = useState(false);
	const { taxonomiesDispatch } = useContext(ModelsContext);
	const history = useHistory();

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
						history.push(
							atlasContentModeler.appPath +
								`&view=taxonomies&editing=${taxonomy.slug}`
						);
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
				<WarningButton
					type="submit"
					form={taxonomy.slug}
					className="first"
					data-testid="delete-taxonomy-button"
					onClick={async () => {
						let hasError = false;

						await apiFetch({
							path: `/wpe/atlas/taxonomy/${taxonomy.slug}`,
							method: "DELETE",
							_wpnonce: wpApiSettings.nonce,
						})
							.then((res) => {
								if (res.success) {
									// TODO: Remove taxonomy from sidebar.
									taxonomiesDispatch({
										type: "removeTaxonomy",
										slug: taxonomy.slug,
									});
								} else {
									hasError = true;
								}
							})
							.catch(() => {
								hasError = true;
							});

						if (hasError) {
							showError(
								sprintf(
									__(
										/* translators: the taxonomy plural name */
										"There was an error. The %s taxonomy was not deleted.",
										"atlas-content-modeler"
									),
									taxonomy.plural
								)
							);
						}

						setModalIsOpen(false);
					}}
				>
					{__("Delete", "atlas-content-modeler")}
				</WarningButton>
				<TertiaryButton
					data-testid="delete-taxonomy-cancel-button"
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
