document.addEventListener("DOMContentLoaded", function () {
	// When block editor loads, listen for what to put in the block editor.
	window.addEventListener(
		"message",
		(event) => {
			wp.data.dispatch("core/block-editor").insertBlocks(
				wp.blocks.rawHandler({
					HTML: event.data,
					mode: "BLOCKS",
				})
			);
		},
		false
	);

	let editorLoaded = false;
	let debounce = null;
	wp.data.subscribe(() => {
		if (!editorLoaded) {
			window.parent.postMessage("acm_block_editor_field_loaded");
			editorLoaded = true;
		}

		clearTimeout(debounce);
		debounce = setTimeout(() => {
			// When block editor content changes, pass it up to the parent app.
			window.parent.postMessage(
				wp.data.select("core/editor").getEditedPostAttribute("content")
			);
		}, 1000);
	});
});
