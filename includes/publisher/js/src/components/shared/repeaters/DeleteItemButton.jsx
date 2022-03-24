/** @jsx jsx */
import { jsx, css } from "@emotion/react";
import Icon from "acm-icons";
import { colors } from "../../../../../../shared-assets/js/emotion";
import { __ } from "@wordpress/i18n";

const DeleteItemButton = ({ deleteItem, ...props }) => {
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
			{...props}
		>
			<Icon type="trash" />
		</button>
	);
};

export default DeleteItemButton;
