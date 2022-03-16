/* global atlasContentModelerFormEditingExperience */
import { useEffect, useRef } from "react";
const { wp } = window;

/**
 * useWpEditor turns textarea fields into rich text fields by
 * initializing WordPress's built-in TinyMCE implementation on them.
 *
 * @param {array} values Objects with 'id' properties containing the CSS ID of
 *                       textareas to initialize as TinyMCE instances.
 */
export default function useWpEditor(values) {
	const editorReadyTimer = useRef(null);

	useEffect(() => {
		const editorReadyTime = 500;
		const editorSettingsOverrides = {
			tinymce: {
				height: "300",
				toolbar1:
					"undo,redo,formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_add_media",
			},
			mediaButtons: true,
			quicktags: false,
		};

		const initializeEditorWhenReady = () => {
			/**
			 * WP defines getDefaultSettings() in an admin footer script tag after
			 * admin scripts are enqueued, so we must wait for it to be available.
			 * See https://github.com/wpengine/atlas-content-modeler/pull/400.
			 */
			if (typeof wp?.oldEditor?.getDefaultSettings === "function") {
				let editorsToInitialize = values.map(({ id }) => id);

				editorsToInitialize.forEach((editorId) => {
					wp.oldEditor.initialize(editorId, {
						...wp.oldEditor.getDefaultSettings(),
						...editorSettingsOverrides,
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
}
