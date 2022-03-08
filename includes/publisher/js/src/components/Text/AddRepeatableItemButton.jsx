/** @jsx jsx */
import { jsx, css } from "@emotion/react";
import { __ } from "@wordpress/i18n";
import AddIcon from "../../../../../components/icons/AddIcon";

export default function AddRepeatableItemButton({
	field,
	values,
	setValues,
	isMaxInputs,
}) {
	function handleAddOptionClick(event) {
		event.preventDefault();
		setValues((oldValues) => [...oldValues, ""]);
	}

	if (isMaxInputs) {
		return (
			<div
				css={(theme) => {
					const { grayLight } = theme.colors;
					return {
						color: grayLight,
						svg: {
							color: grayLight,
							path: {
								fill: grayLight,
							},
						},
					};
				}}
				className="d-flex flex-row justify-content-start align-items-center py-4 ps-3"
			>
				<AddIcon noCircle />{" "}
				<strong className="me-2">
					{field.value.length > 0
						? __(`Add Another`, "atlas-content-modeler")
						: __(`Add Item`, "atlas-content-modeler")}
				</strong>
				<span
					css={(theme) => ({
						color: theme.colors.grayLight,
					})}
				>
					{values.length}/{field.maxRepeatable} Max Inputs Reached
				</span>
			</div>
		);
	}

	return (
		<button
			className="add-option mt-1 tertiary no-border"
			onClick={handleAddOptionClick}
		>
			<a>
				<AddIcon noCircle />{" "}
				<span>
					{field.value.length > 0
						? __(`Add Another`, "atlas-content-modeler")
						: __(`Add Item`, "atlas-content-modeler")}
				</span>
			</a>
		</button>
	);
}
