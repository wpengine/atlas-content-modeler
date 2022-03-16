/** @jsx jsx */
import { jsx, css } from "@emotion/react";
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
			css={css`
				display: inline-flex;
				justify-content: center;
				align-items: center;
				background: transparent;
				border: none;
				cursor: pointer;
				padding: 40px;
				height: 30px;
				width: 30px;
				&:focus,
				&:hover {
					svg {
						path {
							fill: #991433;
						}
					}
				}
			`}
		>
			<a aria-label={__("Remove item.", "atlas-content-modeler")}>
				<TrashIcon size="small" />{" "}
			</a>
		</button>
	);
};

export default DeleteItemButton;
