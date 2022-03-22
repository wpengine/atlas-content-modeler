/** @jsx jsx */
import { jsx, css } from "@emotion/react";
import React from "react";
import AddItemButton from "../shared/repeaters/AddItemButton";
import DeleteItemButton from "../shared/repeaters/DeleteItemButton";
import DateField from "./DateField";
import { colors } from "../../../../../shared-assets/js/emotion";

const RepeatingDate = ({
	modelSlug,
	field,
	values,
	setValues,
	defaultError,
}) => {
	const addItem = () =>
		setValues((oldValues) => [...oldValues, { value: "" }]);

	const deleteItem = (index) =>
		setValues((currentValues) => {
			const newValues = [...currentValues];
			newValues.splice(index, 1);
			return newValues;
		});

	return (
		<>
			{values.map(({ id, value }, index) => {
				return (
					<div
						key={id}
						data-testid="date-repeater-row"
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
						<DateField
							name={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}]`}
							id={id}
							modelSlug={modelSlug}
							field={field}
							defaultError={defaultError}
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

export default RepeatingDate;
