import React from "react";
import supportedFields from "./fields/supportedFields";
import Icon from "../../../../components/icons";
import { FieldButton } from "../../../../shared-assets/js/components/Buttons";

const { cloneDeep } = lodash;

const FieldButtons = ({ activeButton, clickAction, parent }) => {
	const fields = cloneDeep(supportedFields);

	return (
		<div className="field-buttons flex-wrap d-flex flex-column d-sm-flex flex-sm-row">
			{Object.keys(fields).map((field) => {
				const fieldTitle = fields[field];
				return (
					<FieldButton
						key={field}
						className={`mb-1`}
						onClick={() => clickAction(field)}
						active={field === activeButton}
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
