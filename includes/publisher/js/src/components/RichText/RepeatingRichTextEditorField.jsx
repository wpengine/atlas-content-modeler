import React from "react";
import RichTextEditorHeader from "./RichTextEditorHeader";
import AddItemButton from "./AddItemButton";
import DeleteItemButton from "./DeleteItemButton";

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
			{values.map(({ id, value }, index) => {
				return (
					<div key={id}>
						<textarea
							name={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}]`}
							id={id}
							defaultValue={value}
						/>
						{values.length > 1 && (
							<DeleteItemButton
								index={index}
								setValues={setValues}
							/>
						)}
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
