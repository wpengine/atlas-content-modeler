/** @jsx jsx */
import { jsx, css } from "@emotion/react";
import RichTextHeader from "./RichTextHeader";
import AddItemButton from "./AddItemButton";
import DeleteItemButton from "./DeleteItemButton";
import { colors } from "../../../../../shared-assets/js/emotion";

const RepeatingRichText = ({ modelSlug, field, values, setValues }) => {
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
								index={index}
								setValues={setValues}
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
				<AddItemButton setValues={setValues} />
			</div>
		</>
	);
};

export default RepeatingRichText;
