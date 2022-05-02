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
import { EditModelModal } from "./EditModelModal";
import { DeleteModelModal } from "./DeleteModelModal";
import { getGraphiQLLink, maybeCloseDropdown } from "../utils";
import { __ } from "@wordpress/i18n";
import { Dropdown } from "../../../../shared-assets/js/components/Dropdown";

export const ContentModelDropdown = ({ model }) => {
	const { plural, slug } = model;
	const { models } = useContext(ModelsContext);
	const [dropdownOpen, setDropdownOpen] = useState(false);
	const [modalIsOpen, setModalIsOpen] = useState(false);
	const [editModelModalIsOpen, setEditModelModalIsOpen] = useState(false);
	const timer = useRef(null);

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
					className="add-new-entry"
					href={`/wp-admin/post-new.php?post_type=${slug}`}
					target="_blank"
					rel="noopener noreferrer"
					onBlur={() => maybeCloseDropdown(setDropdownOpen, timer)}
				>
					{__("Add New Entry", "atlas-content-modeler")}
				</a>
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
				{atlasContentModeler.isGraphiQLAvailable &&
					models.hasOwnProperty(slug) && (
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
			<DeleteModelModal
				modalIsOpen={modalIsOpen}
				model={model}
				setModalIsOpen={setModalIsOpen}
			/>
			<EditModelModal
				model={model}
				isOpen={editModelModalIsOpen}
				setIsOpen={setEditModelModalIsOpen}
			/>
		</Dropdown>
	);
};
