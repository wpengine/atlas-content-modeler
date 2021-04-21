const { registerBlockType } = wp.blocks;
const {
	__experimentalNumberControl,
	DateTimePicker,
	TextControl,
	TextareaControl,
} = wp.components;
const NumberControl = __experimentalNumberControl;
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
			const blockProps = useBlockProps({
				className: `wpe-block-field wpe-block-field-${field.type}`,
			});
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
					{field.type === "text" && field?.textLength === "short" && (
						<TextControl
							label={field.name}
							value={metaFieldValue}
							onChange={updateMetaValue}
							tabIndex={100 + index} // TODO: debug this — focus jumps to Block Editor sidebar instead of between fields.
						/>
					)}
					{((field.type === "text" && field?.textLength === "long") ||
						field.type === "richtext") && (
						<TextareaControl
							label={field.name}
							value={metaFieldValue}
							onChange={updateMetaValue}
							tabIndex={100 + index}
						/>
					)}
					{field.type === "date" && (
						<>
							<label>{field.name}</label>
							<DateTimePicker
								currentDate={metaFieldValue}
								onChange={updateMetaValue}
								is12Hour={true}
								tabIndex={100 + index}
							/>
						</>
					)}
					{field.type === "number" && (
						<>
							<NumberControl
								label={field.name}
								isShiftStepEnabled={false}
								onChange={updateMetaValue}
								value={metaFieldValue}
							/>
						</>
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
