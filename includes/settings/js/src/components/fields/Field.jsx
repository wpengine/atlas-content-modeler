import React, {useState, useContext} from 'react';
import Form from "./Form";
import Icon from "../icons";
import supportedFields from "./supportedFields";
import {FieldOptionsDropdown} from "./FieldOptionsDropdown";
import {ModelsContext} from "../../ModelsContext";

function Field({
	data = {},
	editing,
	id,
	model,
	nextFieldId,
	open = false,
	position,
	positionAfter,
	previousFieldId,
	setInfoTag,
	swapAction,
	type = "text",
}) {
	const [activeForm, setActiveForm] = useState(type);
	const {dispatch} = useContext(ModelsContext);

	// Closed fields appear as a row with a summary of info.
	if (!open) {
		const typeLabel = supportedFields[data.type];
		const reorderInfoText = `Reorder the “${data.name}“ field with the up and down keys. Changes save automatically.`;
		const reorderInfoTag = (
			<>
				Reorder the “{data.name}” field with the <Icon type="downarrow" /> and{" "}
				<Icon type="uparrow" /> keys. Changes save automatically.
			</>
		);
		return (
			<>
				<li key={id}>
					<span className="reorder">
						<button
							onKeyDown={(e) => {
								if (e.code === 'ArrowUp') {
									e.preventDefault();
									if ( previousFieldId !== -1 ) {
										swapAction(id, previousFieldId);
									}
								}
								if (e.code === 'ArrowDown') {
									e.preventDefault();
									if ( nextFieldId !== -1 ) {
										swapAction(id, nextFieldId);
									}
								}
							}}
							onFocus={() => {
								setInfoTag(reorderInfoTag);
							}}
							onBlur={() => setInfoTag(null)}
							aria-label={reorderInfoText}
						>
							<Icon type="reorder" />
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
					<FieldOptionsDropdown field={data} model={model} />
				</li>
				<li className="add-item">
					<button
						onClick={() => dispatch({type: 'addField', position: positionAfter, model: model.slug})}
						aria-label={`Add a new field below the ${data.name} ${data.type} field`} >
						<Icon type="add" />
					</button>
				</li>
			</>
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
			<div className="field-form">
				<h3>
					{editing ? `Editing` : `New`} {formFieldTitle} Field
				</h3>
				<Form
					type={activeForm}
					storedData={data}
					editing={editing}
					id={id}
					position={position}
				/>
			</div>
		</li>
	);
}

export default Field;
