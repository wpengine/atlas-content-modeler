import React, { useState } from "react";
import { v4 as uuidv4 } from "uuid";
import SoloRichTextEditorField from "./SoloRichTextEditorField";
import RepeatingRichTextEditorField from "./RepeatingRichTextEditorField";
import useWpEditor from "./useWPEditor";
import { __ } from "@wordpress/i18n";

export default function RichTextEditor({ field, modelSlug }) {
	// Generates a unique ID for each rich text field for initialization and keying.
	const initialValues = field?.isRepeatableRichText
		? (field?.value || [""]).map((val) => {
				return { id: uuidv4(), value: val };
		  })
		: [{ id: uuidv4(), value: field?.value }];

	const [values, setValues] = useState(initialValues);

	useWpEditor(values);

	return field?.isRepeatableRichText ? (
		<RepeatingRichTextEditorField
			modelSlug={modelSlug}
			field={field}
			values={values}
			setValues={setValues}
		/>
	) : (
		<SoloRichTextEditorField
			modelSlug={modelSlug}
			field={field}
			fieldId={values[0]["id"]}
		/>
	);
}
