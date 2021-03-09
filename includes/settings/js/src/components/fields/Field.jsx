import React, {useState, useEffect} from 'react';
import TextForm from "./TextForm";
import {AddIcon} from "../icons";

const BooleanForm = () => <p>I will be the boolean form.</p>;
const NumberForm = () => <p>I will be the number form.</p>;

function Field({type='text', position, open=false, cancelAction, id, data={}, addAction, updateAction, positionAfter}) {
	const [activeForm, setActiveForm] = useState(type);

	useEffect(() => {
		if ( type === 'new' ) {
			type = 'text';
			setActiveForm(type)
		}
	}, [])

	const fieldTitle = activeForm.charAt(0).toUpperCase() + activeForm.slice(1);

	// Closed fields appear as a row with a summary of info.
	if (!open) {
		const typeLabel = data.type.charAt(0).toUpperCase() + data.type.slice(1)
		return (
			<>
				<li key={id}>
					<span>reorder icon</span>
					<span>{typeLabel}</span>
					<span>{data.name}</span>
					<span>option icon</span>
				</li>
				<li className="add-item">
					<button onClick={() => addAction(positionAfter)} aria-label={`Add a new field below the ${data.name} ${data.type} field`} >
						<AddIcon/>
					</button>
				</li>
			</>
		);
	}

	// Open fields appear as a form to enter or edit data.
	return (
		<li key={id}>
			<div>
				<button onClick={() => setActiveForm('text')}>Text</button>
				<button onClick={() => setActiveForm('number')}>Number</button>
				<button onClick={() => setActiveForm('boolean')}>Boolean</button>
			</div>
			<h3>New {fieldTitle} Block</h3>
			{ activeForm === 'text' && <TextForm cancelAction={cancelAction} updateAction={updateAction} id={id} position={position}/> }
			{ activeForm === 'boolean' && <BooleanForm /> }
			{ activeForm === 'number' && <NumberForm /> }
		</li>
	);
}

export default Field;
