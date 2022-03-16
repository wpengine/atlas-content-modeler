/* global atlasContentModelerFormEditingExperience */
import React, { useEffect, useRef, useState } from "react";
import AddIcon from "../../../../components/icons/AddIcon";
import { __ } from "@wordpress/i18n";
const { wp } = window;

const RichTextEditorHeader = ({ modelSlug, field }) => {
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
		</>
	);
};

const SoloRichTextEditorField = ({ modelSlug, field, fieldId }) => {
	return (
		<>
			<RichTextEditorHeader modelSlug={modelSlug} field={field} />
			<textarea
				name={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
				id={fieldId}
				defaultValue={field.value}
				required={field.required}
			/>
		</>
	);
};

const RepeatingRichTextEditorField = ({
	modelSlug,
	field,
	fieldId,
	values,
	setValues,
}) => {
	return (
		<>
			<RichTextEditorHeader modelSlug={modelSlug} field={field} />
			{values.map((item, index) => {
				return (
					<textarea
						key={index}
						name={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}]`}
						id={`${fieldId}-${index}`}
						defaultValue={values[index]}
					/>
				);
			})}
			<AddItemButton setValues={setValues} />
		</>
	);
};

const AddItemButton = ({ setValues }) => {
	return (
		<tr className="flex add-container">
			<td>
				<button
					className="add-option mt-0 tertiary no-border"
					onClick={(event) => {
						event.preventDefault();
						// Adds a new empty value to display another field.
						setValues((oldValues) => [...oldValues, ""]);
					}}
				>
					<a>
						<AddIcon noCircle />{" "}
						<span>{__(`Add Item`, "atlas-content-modeler")}</span>
					</a>
				</button>
			</td>
		</tr>
	);
};

export default function RichTextEditor({ field, modelSlug }) {
	const fieldId = `atlas-content-modeler-${modelSlug}-${field.slug}`;
	const editorReadyTimer = useRef(null);
	const [values, setValues] = useState(field?.value || [""]);

	useEffect(() => {
		const editorReadyTime = 500;
		const initializeEditorWhenReady = () => {
			/**
			 * WP defines getDefaultSettings() in an admin footer script tag after
			 * admin scripts are enqueued, so we must wait for it to be available.
			 */
			if (typeof wp?.oldEditor?.getDefaultSettings === "function") {
				let editorsToInitialize = field?.isRepeatableRichText
					? values.map((_, index) => `${fieldId}-${index}`)
					: [fieldId];

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
	}, [fieldId, editorReadyTimer, values]);

	return field?.isRepeatableRichText ? (
		<RepeatingRichTextEditorField
			modelSlug={modelSlug}
			field={field}
			fieldId={fieldId}
			values={values}
			setValues={setValues}
		/>
	) : (
		<SoloRichTextEditorField
			modelSlug={modelSlug}
			field={field}
			fieldId={fieldId}
		/>
	);
}
