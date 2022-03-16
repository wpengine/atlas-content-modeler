/** @jsx jsx */
import { jsx, css } from "@emotion/react";
import TrashIcon from "../../../../../../components/icons/TrashIcon";
import { colors } from "../../../../../../shared-assets/js/emotion";
import { __ } from "@wordpress/i18n";

const DeleteItemButton = ({ deleteItem }) => {
	return (
		<button
			aria-label={__("Remove item.", "atlas-content-modeler")}
			type="button"
			onClick={deleteItem}
			data-testid="delete-repeatable-row"
			css={css`
				background: transparent;
				border: none;
				cursor: pointer;
				height: 80px;
				width: 80px;
				&:focus,
				&:hover {
					svg {
						path {
							fill: ${colors.warningHover};
						}
					}
				}
			`}
		>
			<TrashIcon size="small" />
		</button>
	);
};

export default DeleteItemButton;
