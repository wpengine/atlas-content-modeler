/** @jsx jsx */
import React from "react";
import supportedFields from "./fields/supportedFields";
import Icon from "../../../../components/icons";
import { FieldButton } from "../../../../shared-assets/js/components/Buttons";
import { jsx, css } from "@emotion/react";

const { cloneDeep } = lodash;

const FieldButtons = ({ activeButton, clickAction, parent }) => {
	const cssAttributes = css`
		display: flex;
		padding: 16px 32px;
	`;

	/**
	 * Returns if current button is active
	 * @param field
	 * @returns {string}
	 */
	function isActive(field) {
		return field === activeButton ? "active" : "";
	}

	const fields = cloneDeep(supportedFields);

	return (
		<div
			css={cssAttributes}
			className="field-buttons flex-wrap d-flex flex-column d-sm-flex flex-sm-row"
		>
			{Object.keys(fields).map((field) => {
				const fieldTitle = fields[field];
				return (
					<FieldButton
						key={field}
						active={isActive(field)}
						className="mb-1"
						onClick={() => clickAction(field)}
					>
						<Icon type={field} />
						{fieldTitle}
					</FieldButton>
				);
			})}
		</div>
	);
};

export default FieldButtons;
