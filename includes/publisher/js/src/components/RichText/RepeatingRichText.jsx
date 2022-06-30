/** @jsx jsx */
import { jsx, css } from "@emotion/react";
import React from "react";
import RichTextHeader from "./RichTextHeader";
import AddItemButton from "../shared/repeaters/AddItemButton";
import DeleteItemButton from "../shared/repeaters/DeleteItemButton";
import { colors } from "../../../../../shared-assets/js/emotion";

const RepeatingRichText = ({ modelSlug, field, values, setValues, uuid }) => {
	const addItem = () =>
		setValues((oldValues) => [...oldValues, { id: uuid(), value: "" }]);

	const deleteItem = (index) =>
		setValues((currentValues) => {
			const newValues = [...currentValues];
			newValues.splice(index, 1);
			return newValues;
		});

	return (
		<>
			<RichTextHeader modelSlug={modelSlug} field={field} />
			{values.map(({ id, value }, index) => {
				return (
					<div
						key={id}
						data-testid="rich-text-repeater-row"
						css={css`
							display: flex;
							align-items: center;
							border: solid 1px ${colors.border};
							padding: 12px;
							.classic-form .field & .mce-tinymce {
								width: 100% !important;
							}
							${index === 0 &&
							values.length > 1 &&
							`border-bottom: none;`}
							${values.length > 1 && `padding-right: 0`}
						`}
					>
						<span
							className="px-1 me-2"
							css={css`
								font-family: "Open Sans", sans-serif;
								font-weight: bold;
							`}
						>
							{index + 1}
						</span>
						<textarea
							name={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}]`}
							id={id}
							defaultValue={value}
						/>
						{values.length > 1 && (
							<DeleteItemButton
								deleteItem={() => deleteItem(index)}
							/>
						)}
					</div>
				);
			})}
			<div
				css={css`
					border: solid 1px ${colors.border};
					border-top: none;
				`}
			>
				<AddItemButton addItem={addItem} />
			</div>
		</>
	);
};

export default RepeatingRichText;
