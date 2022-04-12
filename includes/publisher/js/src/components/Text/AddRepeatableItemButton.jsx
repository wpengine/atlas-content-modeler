/** @jsx jsx */
import { jsx, css } from "@emotion/react";
import { __ } from "@wordpress/i18n";
import AddIcon from "../../../../../components/icons/AddIcon";

export default function AddRepeatableItemButton({
	field,
	values,
	setValues,
	isMaxInputs,
	buttonRef,
}) {
	const AddButton = React.forwardRef(
		({ handleAddOptionClick, field }, ref) => (
			<button
				className="add-option mt-1 tertiary no-border"
				onClick={handleAddOptionClick}
				ref={ref}
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
		)
	);

	function handleAddOptionClick(event) {
		event.preventDefault();
		setValues((oldValues) => [...oldValues, ""]);
	}

	if (isMaxInputs) {
		return (
			<div
				css={css`
					color: #9db7d1;
					svg {
						color: #9db7d1;
						path {
							fill: #9db7d1;
						}
					}
				`}
				className="d-flex flex-row justify-content-start align-items-center py-4 ps-3"
			>
				<AddIcon noCircle />{" "}
				<strong className="me-2">
					{field.value.length > 0
						? __(`Add Another`, "atlas-content-modeler")
						: __(`Add Item`, "atlas-content-modeler")}
				</strong>
				<span
					css={css`
						color: #d21b46;
					`}
				>
					{values.length}/{field.maxRepeatable} Max Inputs Reached
				</span>
			</div>
		);
	}

	return (
		<AddButton
			ref={buttonRef}
			field={field}
			handleAddOptionClick={handleAddOptionClick}
		/>
	);
}
