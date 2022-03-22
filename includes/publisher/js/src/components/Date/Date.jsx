import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import DateField from "./DateField";
import RepeatingDate from "./RepeatingDate";

function Date({ field, modelSlug, defaultError }) {
	const initialValues = field?.isRepeatableDate
		? (field?.value || [""]).map((val) => {
				return { value: val };
		  })
		: [{ value: field?.value }];

	const [values, setValues] = useState(initialValues);

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

			{field?.isRepeatableDate ? (
				<RepeatingDate
					modelSlug={modelSlug}
					field={field}
					values={values}
					setValues={setValues}
					defaultError={defaultError}
				/>
			) : (
				<DateField
					field={field}
					modelSlug={modelSlug}
					defaultError={defaultError}
				/>
			)}
		</>
	);
}

export default Date;
