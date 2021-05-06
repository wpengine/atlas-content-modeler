import React from "react";
import supportedFields from "./fields/supportedFields";
import Icon from "./icons";

const FieldButtons = ({ activeButton, clickAction }) => {
	return (
		<div className="field-buttons d-flex flex-column d-sm-flex flex-sm-row">
			{Object.keys(supportedFields).map((field) => {
				const fieldTitle = supportedFields[field];
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
