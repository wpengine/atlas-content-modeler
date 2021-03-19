import React, {useState} from 'react';
import Form from "./Form";
import Icon from "../icons";
import supportedFields from "./supportedFields";
import {FieldOptionsDropdown} from "./FieldOptionsDropdown";

function Field({
	addAction,
	cancelAction,
	closeAction,
	data = {},
	deleteAction,
	editing,
	id,
	model,
	open = false,
	openAction,
	position,
	positionAfter,
	type = "text",
	updateAction,
}) {
	const [activeForm, setActiveForm] = useState(type);

	// Closed fields appear as a row with a summary of info.
	if (!open) {
		const typeLabel = supportedFields[data.type];
		return (
			<>
				<li key={id}>
					<span className="reorder">
						<button
							onMouseDown={() => alert("Reordering fields is not yet implemented.")}
							aria-label={`Reorder the ${data.name} field`}
						>
							<Icon type="reorder" />
						</button>
					</span>
					<button
						className="edit"
						onClick={() => openAction(data.id)}
						aria-label={`Edit the ${data.name} field`}
					>
						<span className="type"><Icon type={data.type}/>{typeLabel}</span>
						<span className="widest"><strong>{data.name}</strong></span>
					</button>
					<FieldOptionsDropdown field={data} model={model} deleteAction={deleteAction} />
				</li>
				<li className="add-item">
					<button onClick={() => addAction(positionAfter)} aria-label={`Add a new field below the ${data.name} ${data.type} field`} >
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
					cancelAction={cancelAction}
					closeAction={closeAction}
					updateAction={updateAction}
					id={id}
					position={position}
				/>
			</div>
		</li>
	);
}

export default Field;
