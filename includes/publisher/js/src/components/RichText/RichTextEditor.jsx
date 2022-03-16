/* global atlasContentModelerFormEditingExperience */
import React, { useEffect, useRef, useState } from "react";
import { v4 as uuidv4 } from "uuid";
import SoloRichTextEditorField from "./SoloRichTextEditorField";
import RepeatingRichTextEditorField from "./RepeatingRichTextEditorField";
import { __ } from "@wordpress/i18n";
const { wp } = window;

export default function RichTextEditor({ field, modelSlug }) {
	const editorReadyTimer = useRef(null);
	const initialValues = field?.isRepeatableRichText
		? (field?.value || [""]).map((val) => {
				return { id: uuidv4(), value: val };
		  })
		: [{ id: uuidv4(), value: field?.value }];

	const [values, setValues] = useState(initialValues);

	useEffect(() => {
		const editorReadyTime = 500;
		const initializeEditorWhenReady = () => {
			/**
			 * WP defines getDefaultSettings() in an admin footer script tag after
			 * admin scripts are enqueued, so we must wait for it to be available.
			 */
			if (typeof wp?.oldEditor?.getDefaultSettings === "function") {
				let editorsToInitialize = values.map(({ id }) => id);

				editorsToInitialize.forEach((editorId) => {
					wp.oldEditor.initialize(editorId, {
						...wp.oldEditor.getDefaultSettings(),
						tinymce: {
							height: "300",
							toolbar1:
								"undo,redo,formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_add_media",
						},
						mediaButtons: true,
						quicktags: false,
					});
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
	}, [editorReadyTimer, values]);

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
