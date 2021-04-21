const { registerBlockType } = wp.blocks;
const { TextControl, DateTimePicker } = wp.components;
const { useSelect } = wp.data;
const { useEntityProp } = wp.coreData;
const { useBlockProps } = wp.blockEditor;

export const registerContentModelBlocks = (fields, modelName) => {
	const topLevelFields = Object.values(fields).filter((field) => {
		return field?.parent === undefined;
	});

	topLevelFields.forEach((field, index) => {
		registerBlock(field, modelName, index);
	});
};

const registerBlock = (field, modelName, index) => {
	const blockName = `${modelName}-${field.id}`;
	const metaKey = `_${field.slug}`;

	registerBlockType(`wpe-content-model/${blockName}`, {
		title: field.name,
		icon: "editor-textcolor", // TODO: use custom content model icon.

		edit({ setAttributes, attributes }) {
			const blockProps = useBlockProps();
			const postType = useSelect(
				(select) => select("core/editor").getCurrentPostType(),
				[]
			);
			const [meta, setMeta] = useEntityProp("postType", postType, "meta");
			const metaFieldValue = meta[metaKey];
			function updateMetaValue(newValue) {
				setMeta({ ...meta, [metaKey]: newValue });
			}

			return (
				<div {...blockProps}>
					{/* TODO: use other controls based on field type. */}
					{ (field.type === 'text' || field.type === 'richtext') && (
						<TextControl
							label={field.name}
							value={metaFieldValue}
							onChange={updateMetaValue}
							tabIndex={100 + index} // TODO: debug this — focus jumps to Block Editor sidebar instead of between fields.
						/>
					)}
					{ field.type === 'date' && (
						<DateTimePicker
							currentDate={metaFieldValue}
							onChange={updateMetaValue}
							is12Hour={true}
							tabIndex={100 + index}
						/>
					)}
				</div>
			);
		},

		// Data is saved to post meta and not post content.
		save() {
			return null;
		},
	});
};
