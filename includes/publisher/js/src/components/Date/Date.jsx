import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import DateField from "./DateField";

function Date({ field, modelSlug, defaultError }) {
	return (
		<>
			<label
				htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
			>
				{field.name}
			</label>
			{field?.required && (
				<p className="required">
					*{__("Required", "atlas-content-modeler")}
				</p>
			)}
			{field?.description && (
				<p className="help mb-0">{field.description}</p>
			)}

			<DateField
				field={field}
				modelSlug={modelSlug}
				defaultError={defaultError}
			/>
		</>
	);
}

export default Date;
