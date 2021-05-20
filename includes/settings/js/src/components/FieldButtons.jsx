import React from "react";
import supportedFields from "./fields/supportedFields";
import Icon from "../../../../components/icons";

const { cloneDeep } = lodash;

const FieldButtons = ({ activeButton, clickAction, parent }) => {
	const fields = cloneDeep(supportedFields);
	if (parent) {
		delete fields["repeater"];
	}
	return (
		<div className="field-buttons">
			{Object.keys(fields).map((field) => {
				const fieldTitle = fields[field];
				return (
					<button
						key={field}
						className={
							field === activeButton
								? "tertiary active"
								: "tertiary"
						}
						onClick={() => clickAction(field)}
					>
						<Icon type={field} />
						{fieldTitle}
					</button>
				);
			})}
		</div>
	);
};

export default FieldButtons;
