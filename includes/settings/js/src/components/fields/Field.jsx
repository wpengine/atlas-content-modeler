import React, { useState, useContext } from "react";
import { Draggable } from "react-beautiful-dnd";
import Form from "./Form";
import Icon from "../../../../../components/icons";
import supportedFields from "./supportedFields";
import { FieldOptionsDropdown } from "./FieldOptionsDropdown";
import { ModelsContext } from "../../ModelsContext";
import FieldButtons from "../FieldButtons";
import { sprintf, __ } from "@wordpress/i18n";

const getItemStyle = (isDragging, draggableStyle) => {
	return {
		...draggableStyle,
	};
};

function Field({
	data = {},
	editing,
	id,
	index,
	model,
	open = false,
	position,
	positionAfter,
	setInfoTag,
	promptToSaveChanges,
	hasDirtyField,
	type = "text",
}) {
	const [activeForm, setActiveForm] = useState(type);
	const { dispatch } = useContext(ModelsContext);

	// Closed fields appear as a row with a summary of info.
	if (!open) {
		const typeLabel = supportedFields[data.type];
		const reorderInfoTag = (
			<>
				Press space to begin reordering the “{data.name}” field. Use{" "}
				<Icon type="downarrow" /> and <Icon type="uparrow" /> keys to
				reorder, then space to finish and save.
			</>
		);
		return (
			<Draggable
				key={id}
				draggableId={String(id)}
				index={index}
				disableInteractiveElementBlocking={true}
			>
				{(provided, snapshot) => (
					<div
						className="draggable"
						ref={provided.innerRef}
						{...provided.draggableProps}
						style={getItemStyle(
							snapshot.isDragging,
							provided.draggableProps.style
						)}
					>
						<li
							key={id}
							className={`${
								snapshot.isDragging
									? "closed dragging"
									: "closed"
							} d-flex flex-column d-sm-flex flex-sm-row justify-content-start`}
						>
							<div className="reorder order-2 order-sm-0">
								<button
									{...provided.dragHandleProps}
									onFocus={() => {
										setInfoTag(reorderInfoTag);
									}}
									onBlur={() => setInfoTag(null)}
								>
									<Icon type="reorder" />
								</button>
							</div>
							<button
								className="edit order-sm-2 order-1 d-flex flex-column d-sm-flex flex-sm-row ml-sm-4 flex-grow-1"
								onClick={() => {
									if (!promptToSaveChanges()) {
										dispatch({
											type: "openField",
											id: data.id,
											model: model.slug,
										});
									}
								}}
								aria-label={`Edit the ${data.name} field`}
							>
								<div
									className="row w-100"
									style={{ "--bs-gutter-x": "0em" }}
								>
									<div className="col-sm-3 col-12 text-center text-sm-start ms-3 ms-sm-0">
										<div className="type mb-3 mb-sm-0">
											<Icon type={data.type} />
											{typeLabel}
										</div>
									</div>
									<div className="col-sm-3 col-12 text-center text-sm-start">
										<div className="widest mb-2 mb-sm-0">
											<strong>{data.name}</strong>
											{"  "}
											<span
												title={__(
													"API Identifier",
													"atlas-content-modeler"
												)}
											>
												({data.slug})
											</span>
										</div>
									</div>
									<div className="col-sm-6 col-12 text-center text-sm-end">
										<div className="tags">
											{data?.isTitle && (
												<span className="tag tag-title">
													entry title
												</span>
											)}
										</div>
									</div>
								</div>
							</button>
							<div className="order-0 my-2 my-sm-0 text-end order-sm-2 ms-sm-auto">
								<FieldOptionsDropdown
									field={data}
									model={model}
								/>
							</div>
						</li>
						<li
							className={
								snapshot.isDragging
									? "add-item dragging"
									: "add-item"
							}
						>
							<button
								onClick={() => {
									if (!promptToSaveChanges()) {
										dispatch({
											type: "addField",
											position: positionAfter,
											model: model.slug,
										});
									}
								}}
								aria-label={`Add a new field below the ${data.name} ${data.type} field`}
							>
								<Icon type="add" size="large" />
							</button>
						</li>
					</div>
				)}
			</Draggable>
		);
	}

	const formFieldTitle = editing
		? `“${data.name}”`
		: supportedFields[activeForm];

	// Open fields appear as a form to enter or edit data.
	return (
		<li className="open-field" key={id}>
			{!editing && (
				<FieldButtons
					activeButton={activeForm}
					clickAction={setActiveForm}
				/>
			)}
			<div className={editing ? "field-form editing" : "field-form"}>
				<div className="d-flex flex-row">
					<div>
						<h3>
							{editing
								? __("Editing", "atlas-content-modeler")
								: __("New", "atlas-content-modeler")}{" "}
							{formFieldTitle} Field
						</h3>
					</div>

					{editing && (
						<div className="ms-auto">
							<FieldOptionsDropdown field={data} model={model} />
						</div>
					)}
				</div>
				<Form
					type={activeForm}
					storedData={data}
					editing={editing}
					id={id}
					position={position}
					hasDirtyField={hasDirtyField}
				/>
			</div>
		</li>
	);
}

export default Field;
