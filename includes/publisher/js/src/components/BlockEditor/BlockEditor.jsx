import React, { useEffect, useRef, useState } from "react";
import { __ } from "@wordpress/i18n";
import BlockEditorHeader from "./BlockEditorHeader";
export function BlockEditor({
	field,
	errors,
	validate,
	modelSlug,
	defaultError,
}) {
	const iframeRef = useRef();

	useEffect(() => {
		window.addEventListener(
			"message",
			(event) => {
				// Copy existing field value to block editor once it's loaded.
				if (event.data === "acm_block_editor_field_loaded") {
					iframeRef.current.contentWindow.postMessage(field.value);
					return;
				}

				// Copy blocks sent from block editor to hidden field in our app.
				syncBlocksToHiddenField(event.data);
			},
			false
		);
	}, []);

	const syncBlocksToHiddenField = (blocks) => {
		document.getElementById(
			`atlas-content-modeler[${modelSlug}][${field.slug}]`
		).value = blocks;
	};

	return (
		<>
			<BlockEditorHeader modelSlug={modelSlug} field={field} />
			<div className="atlas-content-modeler-block-editor-field">
				<input
					type="hidden"
					id={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
					name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
				/>
				<iframe
					title={__("Block Editor Field", "atlas-content-modeler")}
					src={`${atlasContentModelerFormEditingExperience.adminUrl}/post-new.php?post_type=acm_field_type_block`}
					height="800px"
					width="100%"
					ref={iframeRef}
				/>
			</div>
		</>
	);
}
