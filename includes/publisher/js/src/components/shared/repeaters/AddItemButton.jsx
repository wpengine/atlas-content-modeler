/** @jsx jsx */
import { jsx, css } from "@emotion/react";
import Icon from "acm-icons";
import { colors } from "../../../../../../shared-assets/js/emotion";
import { __ } from "@wordpress/i18n";

const AddItemButton = ({ addItem, buttonRef, ...props }) => {
	return (
		<button
			onClick={addItem}
			ref={buttonRef}
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
			{...props}
		>
			<Icon type="add" noCircle />
			<span>{__("Add Item", "atlas-content-modeler")}</span>
		</button>
	);
};

export default AddItemButton;
