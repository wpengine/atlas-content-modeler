const { registerBlockType } = wp.blocks;
const {
	__experimentalNumberControl,
	CheckboxControl,
	DateTimePicker,
	TextControl,
	TextareaControl,
} = wp.components;
const NumberControl = __experimentalNumberControl;
const { useSelect } = wp.data;
const { useEntityProp } = wp.coreData;
const { useBlockProps } = wp.blockEditor;

export const registerContentModelBlock = (fields, modelName) => {
	const topLevelFields = Object.values(fields).filter((field) => {
		return field?.parent === undefined;
	});

	// order top level fields by position.

	registerBlockType(`wpe-content-model/${modelName}`, {
		title: modelName + ' Fields',
		icon: "editor-textcolor", // TODO: use custom content model icon.

		edit({ setAttributes, attributes }) {
			const blockProps = useBlockProps({
				className: `wpe-block wpe-block-${modelName}`,
			});
			const postType = useSelect(
				(select) => select("core/editor").getCurrentPostType(),
				[]
			);
			const [meta, setMeta] = useEntityProp("postType", postType, "meta");
			function updateMetaValue(newValue, metaKey) {
				setMeta({ ...meta, [metaKey]: newValue });
			}

			return (
				<div {...blockProps}>
					{ topLevelFields.map((field, index) => {
						const metaKey = `_${field.slug}`;

						return (
							<div key={metaKey}>
								{field.type === "text" && field?.textLength === "short" && (
									<TextControl
										label={field.name}
										value={meta[metaKey]}
										onChange={(newValue) => updateMetaValue(newValue, metaKey)}
									/>
								)}

								{((field.type === "text" && field?.textLength === "long") ||
									field.type === "richtext") && (
									<TextareaControl
										label={field.name}
										value={meta[metaKey]}
										onChange={(newValue) => updateMetaValue(newValue, metaKey)}
									/>
								)}

								{field.type === "date" && (
									<>
										<label>{field.name}</label>
										<DateTimePicker
											currentDate={meta[metaKey]}
											onChange={(newValue) => updateMetaValue(newValue, metaKey)}
											is12Hour={true}
										/>
									</>
								)}

								{field.type === "boolean" && (
									<>
										<label>{field.name}</label>
										<CheckboxControl
											label={field.name}
											checked={meta[metaKey]}
											onChange={(newValue) => updateMetaValue(newValue, metaKey)}
										/>
									</>
								)}

								{field.type === "number" && (
									<>
										<NumberControl
											label={field.name}
											value={meta[metaKey]}
											isShiftStepEnabled={false}
											onChange={(newValue) => updateMetaValue(newValue, metaKey)}
										/>
									</>
								)}
							</div>
						);

					})}
				</div>
			);
		},

		// Data is saved to post meta and not post content.
		save() {
			return null;
		},
	});
};
