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
	useEffect(() => {
		console.log(field);
		wp.oldEditor.initialize(
			`wpe-content-model-${modelSlug}-${field.slug}`,
			{
				...wp.oldEditor.getDefaultSettings(),
				tinymce: {
					height: "300",
					toolbar1:
						"undo,redo,formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_add_media",
				},
				mediaButtons: true,
				quicktags: false,
			}
		);
	}, []);

	return (
		<>
			<label htmlFor={`wpe-content-model[${modelSlug}][${field.slug}]`}>
				{field.name}
			</label>
			<br />
			<textarea
				name={`wpe-content-model[${modelSlug}][${field.slug}]`}
				id={`wpe-content-model-${modelSlug}-${field.slug}`}
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
