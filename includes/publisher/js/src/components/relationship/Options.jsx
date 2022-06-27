import React, { useState, useEffect, useRef, useCallback } from "react";
import Icon from "acm-icons";
import { maybeCloseDropdown } from "../../../../../settings/js/src/utils";
import { sprintf, __ } from "@wordpress/i18n";
import { Dropdown } from "../../../../../shared-assets/js/components/Dropdown";

const { adminUrl } = atlasContentModelerFormEditingExperience;

const Options = ({ entry, setSelectedEntries }) => {
	const [dropdownOpen, setDropdownOpen] = useState(false);
	const timer = useRef(null);

	const handleKeyPress = useCallback(
		(e) => {
			if (e.key === "Escape") {
				setDropdownOpen(false);
			}
		},
		[setDropdownOpen]
	);

	/**
	 * Removes the entry with the given ID.
	 *
	 * @param {Integer} id The entry to remove.
	 */
	const removeEntry = (id) => {
		setSelectedEntries((selectedEntries) =>
			selectedEntries.filter((item) => parseInt(item) !== parseInt(id))
		);
	};

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
				onClick={(event) => {
					event.preventDefault();
					setDropdownOpen(!dropdownOpen);
				}}
				aria-label={sprintf(
					/* translators: title of the entry to show options for. */
					__("Options for the %s entry.", "atlas-content-modeler"),
					entry?.title?.rendered
				)}
			>
				<Icon type="options" />
			</button>
			<div className={`dropdown-content ${dropdownOpen ? "" : "hidden"}`}>
				<a
					className="view"
					href={`${adminUrl}post.php?post=${entry.id}&action=edit`}
					target="_blank"
					rel="noopener noreferrer"
					onClick={() => {
						setDropdownOpen(false);
					}}
					onBlur={() => maybeCloseDropdown(setDropdownOpen, timer)}
					aria-label={sprintf(
						/* translators: title of the entry to view. */
						__(
							"View the %s entry in a new tab.",
							"atlas-content-modeler"
						),
						entry?.title?.rendered
					)}
				>
					{__("View", "atlas-content-modeler")}
				</a>
				<a
					className="delete"
					href="#"
					onBlur={() => maybeCloseDropdown(setDropdownOpen, timer)}
					onClick={(event) => {
						event.preventDefault();
						removeEntry(entry.id);
						setDropdownOpen(false);
					}}
					aria-label={sprintf(
						/* translators: title of the entry to remove. */
						__("Remove the %s entry.", "atlas-content-modeler"),
						entry?.title?.rendered
					)}
				>
					{__("Remove", "atlas-content-modeler")}
				</a>
			</div>
		</Dropdown>
	);
};

export default Options;
