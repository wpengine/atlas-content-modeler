/** @jsx jsx */
import React, { useRef } from "react";
import { jsx, css } from "@emotion/react";
import AddItemButton from "../shared/repeaters/AddItemButton";
import DeleteItemButton from "../shared/repeaters/DeleteItemButton";
import DateField from "./DateField";
import { colors } from "../../../../../shared-assets/js/emotion";
import useFocusNewFields from "../shared/repeaters/useFocusNewFields";

const RepeatingDate = ({
	modelSlug,
	field,
	values,
	setValues,
	defaultError,
}) => {
	const addButtonRef = useRef();

	useFocusNewFields(modelSlug, field?.slug, values);

	const addItem = () =>
		setValues((oldValues) => [...oldValues, { value: "" }]);

	const deleteItem = (index) =>
		setValues((currentValues) => {
			const newValues = [...currentValues];
			newValues.splice(index, 1);
			return newValues;
		});

	/**
	 * Handle keypress to add new entry and continue entering data
	 * @param {*} event
	 */
	function handleKeyPress(event) {
		if (event.key === "Enter") {
			event.preventDefault();

			const lastFieldIsInFocus =
				document.activeElement.getAttribute("name") ===
				`atlas-content-modeler[${modelSlug}][${field.slug}][${
					values?.length - 1
				}]`;

			if (lastFieldIsInFocus) {
				addButtonRef.current.click();
				return;
			}

			const activeFieldName = document.activeElement.getAttribute("name");

			const activeFieldIndex = [
				...document.querySelectorAll(
					`[name*="atlas-content-modeler[${modelSlug}][${field.slug}]`
				),
			]
				.map((field) => field.getAttribute("name"))
				.indexOf(activeFieldName);

			const nextField = document.querySelector(
				`[name="atlas-content-modeler[${modelSlug}][${field.slug}][${
					activeFieldIndex + 1
				}]`
			);

			if (nextField) {
				nextField.focus();
			}
		}
	}

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
				<tbody>
					{values.map(({ value }, index) => {
						return (
							<tr key={index} data-testid="date-repeater-row">
								<td
									css={css`
										padding: 0 !important;
									`}
								>
									<div
										className="d-flex flex-row flex-fill d-lg-flex"
										css={css`
											border-bottom: solid 1px
												${colors.border} !important;
											padding: 10px;
											align-items: center;
										`}
									>
										<span
											className="px-1 me-2 align-middle"
											css={css`
												font-family: "Open Sans",
													sans-serif;
												font-weight: bold;
											`}
										>
											{index + 1}
										</span>
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
											onKeyPress={(event) => {
												handleKeyPress(event);
											}}
										/>
										{values.length > 1 && (
											<DeleteItemButton
												data-testid={`remove-date-field-${index}-button`}
												deleteItem={() =>
													deleteItem(index)
												}
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
									margin: 0;
								`}
								buttonRef={addButtonRef}
							/>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	);
};

export default RepeatingDate;
