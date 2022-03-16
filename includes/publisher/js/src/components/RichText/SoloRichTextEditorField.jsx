import React from "react";
import RichTextEditorHeader from "./RichTextEditorHeader";

const SoloRichTextEditorField = ({ modelSlug, field, fieldId }) => {
	return (
		<>
			<RichTextEditorHeader modelSlug={modelSlug} field={field} />
			<textarea
				name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
				id={fieldId}
				defaultValue={field.value}
				required={field.required}
			/>
		</>
	);
};

export default SoloRichTextEditorField;
