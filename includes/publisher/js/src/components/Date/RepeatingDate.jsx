/** @jsx jsx */
import { jsx, css } from "@emotion/react";
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
		<fieldset>
			<table
				className="table"
				css={css`
					width: 100%;
					max-width: 60%;
					margin-top: 15px;
					border-spacing: 0;
					border: solid 1px ${colors.border};
				`}
			>
				{values.map(({ id, value }, index) => {
					return (
						<tr key={id} data-testid="date-repeater-row">
							<td
								css={css`
									padding: 0;
								`}
							>
								<div
									className="d-flex flex-row flex-fill d-lg-flex"
									css={css`
										border-bottom: solid 1px
											${colors.border} !important;
									`}
								>
									<DateField
										name={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}]`}
										id={`atlas-content-modeler[${modelSlug}][${field.slug}][${index}]`}
										modelSlug={modelSlug}
										field={field}
										defaultError={defaultError}
										defaultValue={value}
										css={css`
											margin-top: 0 !important;
											width: 100% !important;
										`}
									/>
									{values.length > 1 && (
										<DeleteItemButton
											data-testid={`remove-date-field-${index}-button`}
											deleteItem={() => deleteItem(index)}
											css={css`
												height: 52px;
												width: 52px;
												margin-right: -0.5rem;
											`}
										/>
									)}
								</div>
							</td>
						</tr>
					);
				})}
				<tr>
					<td>
						<AddItemButton
							data-testid={`add-date-field-button`}
							addItem={addItem}
							css={css`
								margin: 0 !important;
							`}
						/>
					</td>
				</tr>
			</table>
		</fieldset>
	);
};

export default RepeatingDate;
