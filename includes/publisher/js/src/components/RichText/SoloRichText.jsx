import React from "react";
import RichTextHeader from "./RichTextHeader";

const SoloRichText = ({ modelSlug, field, fieldId }) => {
	return (
		<>
			<RichTextHeader modelSlug={modelSlug} field={field} />
			<textarea
				name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
				id={fieldId}
				defaultValue={field.value}
			/>
		</>
	);
};

export default SoloRichText;
