import React, {useState} from 'react';
import MediaForm from "./MediaForm";
import TextForm from "./TextForm";
import NumberForm from "./NumberForm";
import BooleanForm from "./BooleanForm";
import Icon from "../icons";

function Field({type='text', position, open=false, cancelAction, id, data={}, addAction, updateAction, positionAfter}) {
	const [activeForm, setActiveForm] = useState(type);
	const supportedFields = ['text', 'number', 'boolean', 'media'];

	// Closed fields appear as a row with a summary of info.
	if (!open) {
		const typeLabel = data.type.charAt(0).toUpperCase() + data.type.slice(1)
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
						onClick={() => alert("Editing saved fields is not yet implemented.")}
						aria-label={`Edit the ${data.name} field`}
					>
						<span className="type"><Icon type={data.type}/>{typeLabel}</span>
						<span className="widest"><strong>{data.name}</strong></span>
					</button>
					<span>
						<button
							className="options"
							onClick={() => alert("Field options are not yet implemented.")}
							aria-label={`Options for the ${data.name} field.`}
						>
							<Icon type="options" />
						</button>
					</span>
				</li>
				<li className="add-item">
					<button onClick={() => addAction(positionAfter)} aria-label={`Add a new field below the ${data.name} ${data.type} field`} >
						<Icon type="add" />
					</button>
				</li>
			</>
		);
	}

	const formFieldTitle = activeForm.charAt(0).toUpperCase() + activeForm.slice(1);

	// Open fields appear as a form to enter or edit data.
	return (
		<li className="open-field" key={id}>
			<div className="field-buttons">
				{supportedFields.map((field) => {
					const fieldTitle = field.charAt(0).toUpperCase() + field.slice(1);
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
			<div className="field-form">
				<h3>New {formFieldTitle} Field</h3>
				{ activeForm === 'text' && <TextForm cancelAction={cancelAction} updateAction={updateAction} id={id} position={position}/> }
				{ activeForm === 'boolean' && <BooleanForm cancelAction={cancelAction} updateAction={updateAction} id={id} position={position}/> }
				{ activeForm === 'number' && <NumberForm cancelAction={cancelAction} updateAction={updateAction} id={id} position={position}/> }
				{ activeForm === 'media' && <MediaForm cancelAction={cancelAction} updateAction={updateAction} id={id} position={position}/> }
			</div>
		</li>
	);
}

export default Field;
