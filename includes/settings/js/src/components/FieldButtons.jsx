import React from "react";
import supportedFields from "./fields/supportedFields";
import Icon from "../../../../components/icons";

const { cloneDeep } = lodash;

const FieldButtons = ({ activeButton, clickAction, parent }) => {
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
		<div className="field-buttons flex-wrap d-flex flex-column d-sm-flex flex-sm-row">
			{Object.keys(fields).map((field) => {
				const fieldTitle = fields[field];
				return (
					<button
						key={field}
						className={`tertiary mb-1 ${isActive(field)}`}
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
