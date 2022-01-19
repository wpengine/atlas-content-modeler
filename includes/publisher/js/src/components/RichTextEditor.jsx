/* global atlasContentModelerFormEditingExperience */
import React, { useEffect, useRef } from "react";
import Icon from "../../../../components/icons";
import { __ } from "@wordpress/i18n";
const { wp } = window;

export default function RichTextEditor({ field, modelSlug, defaultError }) {
	const fieldId = `atlas-content-modeler-${modelSlug}-${field.slug}`;
	const editorReadyTimer = useRef(null);

	useEffect(() => {
		const editorReadyTime = 500;
		const initializeEditorWhenReady = () => {
			/**
			 * WP defines getDefaultSettings() in an admin footer script tag after
			 * admin scripts are enqueued, so we must wait for it to be available.
			 */
			if (typeof wp?.oldEditor?.getDefaultSettings === "function") {
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
			} else {
				editorReadyTimer.current = setTimeout(
					initializeEditorWhenReady,
					editorReadyTime
				);
			}
		};

		if (
			atlasContentModelerFormEditingExperience?.models ||
			atlasContentModelerFormEditingExperience?.models[
				atlasContentModelerFormEditingExperience.postType
			]
		) {
			initializeEditorWhenReady();
		}
		return () => {
			clearTimeout(editorReadyTimer.current);
		};
	}, [fieldId, editorReadyTimer]);

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
			<textarea
				name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
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
