import React from "react";
import RichTextEditorHeader from "./RichTextEditorHeader";
import AddItemButton from "./AddItemButton";

const RepeatingRichTextEditorField = ({
	modelSlug,
	field,
	fieldId,
	values,
	setValues,
}) => {
	return (
		<div>
			<RichTextEditorHeader modelSlug={modelSlug} field={field} />
			{values.map((item, index) => {
				return (
					<div key={index}>
						<textarea
							name={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}]`}
							id={`${fieldId}-${index}`}
							defaultValue={values[index]}
						/>
						<p>delete button</p>
					</div>
				);
			})}
			<div>
				<AddItemButton setValues={setValues} />
			</div>
		</div>
	);
};

export default RepeatingRichTextEditorField;
