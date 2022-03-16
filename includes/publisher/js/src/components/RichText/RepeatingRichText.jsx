/** @jsx jsx */
import { jsx, css } from "@emotion/react";
import RichTextHeader from "./RichTextHeader";
import AddItemButton from "./AddItemButton";
import DeleteItemButton from "./DeleteItemButton";
import { colors } from "../../../../../shared-assets/js/emotion";
import { v4 as uuidv4 } from "uuid";

const RepeatingRichText = ({ modelSlug, field, values, setValues }) => {
	const addItem = () =>
		setValues((oldValues) => [
			...oldValues,
			{ id: "field-" + uuidv4(), value: "" },
		]);

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
