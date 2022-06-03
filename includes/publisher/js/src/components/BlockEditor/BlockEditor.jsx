import React from "react";
import BlockEditorHeader from "./BlockEditorHeader";
import {
	BlockEditorProvider,
	BlockList,
	BlockTools,
	WritingFlow,
	ObserveTyping,
	BlockInspector,
} from "@wordpress/block-editor";
import { ShortcutProvider } from "@wordpress/keyboard-shortcuts";
import { SlotFillProvider, Popover } from "@wordpress/components";
import { useState } from "@wordpress/element";
import { registerCoreBlocks } from "@wordpress/block-library";
registerCoreBlocks();

import "@wordpress/components/build-style/style.css";
import "@wordpress/block-editor/build-style/style.css";

export default function BlockEditorApp() {
	const [blocks, updateBlocks] = useState([]);

	return (
		<ShortcutProvider>
			<BlockEditorProvider
				value={blocks}
				onInput={(blocks) => updateBlocks(blocks)}
				onChange={(blocks) => updateBlocks(blocks)}
			>
				<SlotFillProvider>
					<BlockTools>
						<WritingFlow className="editor-styles-wrapper">
							<ObserveTyping>
								<BlockList />
								<div
									className="sidebar"
									style={{ display: "none" }}
								>
									<BlockInspector />
								</div>
							</ObserveTyping>
						</WritingFlow>
					</BlockTools>
					<Popover.Slot />
				</SlotFillProvider>
			</BlockEditorProvider>
		</ShortcutProvider>
	);
}

export function BlockEditor({
	field,
	errors,
	validate,
	modelSlug,
	defaultError,
}) {
	return (
		<>
			<BlockEditorHeader modelSlug={modelSlug} field={field} />
			<div
				id={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
				name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
				className="atlas-content-modeler-block-editor-field"
			>
				<BlockEditorApp />
			</div>
		</>
	);
}
