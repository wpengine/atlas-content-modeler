import React, { useState } from "react";
import { v4 as uuidv4 } from "uuid";
import SoloRichText from "./SoloRichText";
import RepeatingRichText from "./RepeatingRichText";
import useWpEditor from "./useWPEditor";
import { __ } from "@wordpress/i18n";

export default function RichText({ field, modelSlug }) {
	// Generates a unique ID for each field for initialization and keying.
	const uuid = () => "field-" + uuidv4();

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
