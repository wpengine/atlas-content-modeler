import React from "react";
import TrashIcon from "../../../../../components/icons/TrashIcon";
import { __ } from "@wordpress/i18n";

const DeleteItemButton = ({ index, setValues }) => {
	return (
		<button
			type="button"
			onClick={(event) => {
				event.preventDefault();
				// Removes the value at the given index.
				setValues((currentValues) => {
					const newValues = [...currentValues];
					newValues.splice(index, 1);
					return newValues;
				});
			}}
		>
			<a aria-label={__("Remove item.", "atlas-content-modeler")}>
				<TrashIcon size="small" />{" "}
			</a>
		</button>
	);
};

export default DeleteItemButton;
