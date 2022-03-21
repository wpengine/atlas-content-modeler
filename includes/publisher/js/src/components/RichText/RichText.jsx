import React, { useState } from "react";
import { uuid } from "./uuid";
import SoloRichText from "./SoloRichText";
import RepeatingRichText from "./RepeatingRichText";
import useWpEditor from "./useWPEditor";
import { __ } from "@wordpress/i18n";

export default function RichText({ field, modelSlug }) {
	const initialValues = field?.isRepeatableRichText
		? (field?.value || [""]).map((val) => {
				return { id: uuid(), value: val };
		  })
		: [{ id: uuid(), value: field?.value }];

	const [values, setValues] = useState(initialValues);

	let textareaIds = values.map(({ id }) => id);

	useWpEditor(textareaIds);

	return field?.isRepeatableRichText ? (
		<RepeatingRichText
			modelSlug={modelSlug}
			field={field}
			values={values}
			setValues={setValues}
			uuid={uuid}
		/>
	) : (
		<SoloRichText
			modelSlug={modelSlug}
			field={field}
			fieldId={values[0]["id"]}
		/>
	);
}
