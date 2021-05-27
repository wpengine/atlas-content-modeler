/* global wpeContentModelFormEditingExperience */
import React, { useEffect } from "react";
import Icon from "../../../../components/icons";
const { wp } = window;

export default function RichTextEditor({
	field,
	modelSlug,
	errors,
	validate,
	defaultError,
}) {
	const fieldId = `wpe-content-model-${modelSlug}-${field.slug}`;

	useEffect(() => {
		if (
			wpeContentModelFormEditingExperience?.models ||
			wpeContentModelFormEditingExperience?.models[
				wpeContentModelFormEditingExperience.postType
			]
		) {
			wp.oldEditor.initialize(fieldId, {
				...wp.oldEditor.getDefaultSettings(),
				tinymce: {
					height: "300",
					toolbar1:
						"undo,redo,formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_add_media",
				},
				mediaButtons: true,
				quicktags: false,
			});
		}
	}, []);

	return (
		<>
			<label htmlFor={`wpe-content-model[${modelSlug}][${field.slug}]`}>
				{field.name}
			</label>
			<br />
			<textarea
				name={`wpe-content-model[${modelSlug}][${field.slug}]`}
				id={fieldId}
				defaultValue={field.value}
				required={field.required}
			/>
			<span className="error">
				<Icon type="error" />
				<span role="alert">{defaultError}</span>
			</span>
		</>
	);
}
