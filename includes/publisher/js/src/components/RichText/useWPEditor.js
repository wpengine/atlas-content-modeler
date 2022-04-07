import { useEffect, useRef } from "react";
const { wp, atlasContentModelerFormEditingExperience } = window;

/**
 * useWpEditor turns textarea fields into rich text fields by
 * initializing WordPress's built-in TinyMCE implementation on them.
 *
 * @param {array} textareaIds HTML IDs of textareas to initialize as TinyMCE instances.
 */
export default function useWpEditor(textareaIds) {
	const editorReadyTimer = useRef(null);

	/**
	 * The init_instance_callback to focus the field if it is the first
	 * one in the model. Allows users to start typing without having to focus
	 * the field manually on “new post” forms.
	 *
	 * @param {object} editor
	 */
	const maybeFocusEditor = (editor) => {
		let isFirstField =
			document
				.getElementById(editor.id)
				.closest("[data-first-field=true]") !== null &&
			(textareaIds[0] ?? "") === editor.id;

		let isNewPost = document.body.classList.contains("post-new-php");

		if (isFirstField && isNewPost) {
			editor.focus();
		}
	};

	useEffect(() => {
		const editorReadyTime = 500;
		const editorSettingsOverrides = {
			tinymce: {
				height: "125",
				init_instance_callback: maybeFocusEditor,
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
				textareaIds.forEach((textareaId) => {
					wp.oldEditor.initialize(textareaId, {
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
	}, [editorReadyTimer, textareaIds]);
}
