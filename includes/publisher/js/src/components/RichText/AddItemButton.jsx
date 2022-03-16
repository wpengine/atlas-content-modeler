/** @jsx jsx */
import { jsx, css } from "@emotion/react";
import AddIcon from "../../../../../components/icons/AddIcon";
import { colors } from "../../../../../shared-assets/js/emotion";
import { __ } from "@wordpress/i18n";
import { v4 as uuidv4 } from "uuid";

const AddItemButton = ({ setValues }) => {
	const addItem = () =>
		setValues((oldValues) => [
			...oldValues,
			{ id: "field-" + uuidv4(), value: "" },
		]);

	return (
		<button
			onClick={addItem}
			type="button"
			data-testid="add-repeatable-row"
			css={css`
				align-items: center;
				background: transparent;
				border: none;
				color: ${colors.primary};
				cursor: pointer;
				display: flex;
				font-weight: bold;
				height: 68px;
				margin: 8px;
				svg {
					margin-right: 4px;
				}
				&:focus,
				&:hover {
					color: ${colors.primaryHover};
					svg {
						path {
							fill: ${colors.primaryHover};
						}
					}
				}
			`}
		>
			<AddIcon noCircle />
			<span>{__("Add Item", "atlas-content-modeler")}</span>
		</button>
	);
};

export default AddItemButton;
