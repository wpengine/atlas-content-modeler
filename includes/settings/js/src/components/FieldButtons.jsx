import React from "react";
import supportedFields from "./fields/supportedFields";
import Icon from "./icons";

const FieldButtons = ({ activeButton, clickAction }) => {
	/**
	 * Returns if current button is active
	 * @param field
	 * @returns {string}
	 */
	function isActive(field) {
		return field === activeButton ? "active" : "";
	}

	return (
		<div className="field-buttons flex-wrap d-flex flex-column d-sm-flex flex-sm-row ">
			{Object.keys(supportedFields).map((field) => {
				const fieldTitle = supportedFields[field];
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
