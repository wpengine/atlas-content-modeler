import React from "react";

const BlockEditorHeader = ({ modelSlug, field }) => {
	return (
		<>
			<label
				htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
			>
				{field.name}
			</label>
			{field?.description && (
				<p className="help mb-4">{field.description}</p>
			)}
		</>
	);
};

export default BlockEditorHeader;
