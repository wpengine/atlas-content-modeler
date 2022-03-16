import React from "react";
import AddIcon from "../../../../../components/icons/AddIcon";
import { __ } from "@wordpress/i18n";

const AddItemButton = ({ setValues }) => {
	return (
		<tr className="flex add-container">
			<td>
				<button
					className="add-option mt-0 tertiary no-border"
					onClick={(event) => {
						event.preventDefault();
						// Adds a new empty value to display another field.
						setValues((oldValues) => [...oldValues, ""]);
					}}
				>
					<a>
						<AddIcon noCircle />{" "}
						<span>{__(`Add Item`, "atlas-content-modeler")}</span>
					</a>
				</button>
			</td>
		</tr>
	);
};

export default AddItemButton;
