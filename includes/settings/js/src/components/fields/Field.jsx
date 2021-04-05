import React, {useState, useContext} from 'react';
import {Draggable} from "react-beautiful-dnd";
import Form from "./Form";
import Icon from "../icons";
import supportedFields from "./supportedFields";
import Repeater from "./Repeater";
import {FieldOptionsDropdown} from "./FieldOptionsDropdown";
import {ModelsContext} from "../../ModelsContext";

const getItemStyle = (isDragging, draggableStyle) => {
	return {
		...draggableStyle
	}
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
	type = "text",
	parent,
}) {
	const [activeForm, setActiveForm] = useState(type);
	const {dispatch} = useContext(ModelsContext);

	// Closed fields appear as a row with a summary of info.
	if (!open) {
		const typeLabel = supportedFields[data.type];
		const reorderInfoTag = (
			<>
				Press space to begin reordering the “{data.name}” field. Use <Icon type="downarrow" /> and{" "}
				<Icon type="uparrow" /> keys to reorder, then space to finish and save.
			</>
		);
		return (
			<Draggable key={id} draggableId={id} index={index} disableInteractiveElementBlocking={true}>
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
						<li className="closed" key={id}>
					<span className="reorder">
						<button
							{...provided.dragHandleProps}
							onFocus={() => {
								setInfoTag(reorderInfoTag);
							}}
							onBlur={() => setInfoTag(null)}
						>
							<Icon type="reorder"/>
						</button>
					</span>
							<button
								className="edit"
								onClick={() => dispatch({type: 'openField', id: data.id, model: model.slug})}
								aria-label={`Edit the ${data.name} field`}
							>
								<span className="type"><Icon type={data.type}/>{typeLabel}</span>
								<span className="widest"><strong>{data.name}</strong></span>
							</button>
							<FieldOptionsDropdown field={data} model={model}/>
							{
								data.type === "repeater" && (
									<Repeater
										fields={data?.subfields}
										model={model} parent={id}
										setInfoTag={setInfoTag}
									/>
								)
							}
						</li>
						<li className="add-item">
							<button
								onClick={() => dispatch({
									type: 'addField',
									position: positionAfter,
									parent,
									model: model.slug
								})}
								aria-label={`Add a new field below the ${data.name} ${data.type} field`}>
								<Icon type="add" size={parent ? 'small' : 'large'}/>
							</button>
						</li>
					</div>)}
			</Draggable>
		);
	}

	const formFieldTitle = editing ? `“${data.name}”` : supportedFields[activeForm];

	// Open fields appear as a form to enter or edit data.
	return (
		<li className="open-field" key={id}>
			{!editing && (
				<div className="field-buttons">
					{Object.keys(supportedFields).map((field) => {
						const fieldTitle = supportedFields[field];
						return (
							<button
								key={field}
								className={field === activeForm ? "tertiary active" : "tertiary"}
								onClick={() => setActiveForm(field)}
							>
								<Icon type={field}/>
								{fieldTitle}
							</button>
						);
					})}
				</div>
			)}
			<div className={editing ? 'field-form editing' : 'field-form'}>
				<h3>
					{editing ? `Editing` : `New`} {formFieldTitle} Field
				</h3>
				<Form
					type={activeForm}
					storedData={data}
					editing={editing}
					id={id}
					position={position}
					parent={parent}
				/>
			</div>
		</li>
	);
}

export default Field;
