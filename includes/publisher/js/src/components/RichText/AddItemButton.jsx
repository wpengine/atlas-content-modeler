/** @jsx jsx */
import { jsx, css } from "@emotion/react";
import AddIcon from "../../../../../components/icons/AddIcon";
import { __ } from "@wordpress/i18n";
import { v4 as uuidv4 } from "uuid";

const AddItemButton = ({ setValues }) => {
	return (
		<button
			onClick={(event) => {
				event.preventDefault();
				// Adds a new empty value to display another field.
				setValues((oldValues) => [
					...oldValues,
					{ id: uuidv4(), value: "" },
				]);
			}}
			css={css`
				color: #9db7d1;
				cursor: pointer;
				border: none;
				background: transparent;
				margin: 8px;
				height: 68px;
				svg {
					margin-right: 4px;
				}
				a {
					display: flex;
					align-items: center;
					font-weight: bold;
					color: #7e5cef;
				}
				&:focus,
				&:hover {
					a {
						color: #5c43ae;
					}
					svg {
						path {
							fill: #5c43ae;
						}
					}
				}
			`}
		>
			<a>
				<AddIcon noCircle />{" "}
				<span>{__(`Add Item`, "atlas-content-modeler")}</span>
			</a>
		</button>
	);
};

export default AddItemButton;
