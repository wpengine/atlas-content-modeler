import React from "react";
import Icon from "acm-icons";

function DateField({ field, modelSlug, defaultError, ...props }) {
	return (
		<>
			<input
				type="date"
				name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
				id={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
				required={field.required}
				defaultValue={field.value}
				{...props}
			/>
			<span className="error">
				<Icon type="error" />
				<span role="alert">{defaultError}</span>
			</span>
		</>
	);
}

export default DateField;
