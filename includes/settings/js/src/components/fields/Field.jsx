import React, {useState, useEffect} from 'react';
import TextForm from "./TextForm";
import {AddIcon, OptionsIcon, ReorderIcon, TextIcon, BooleanIcon, NumberIcon} from "../icons";

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

	function showTypeIcon() {
		switch(type) {
			case 'boolean':
				return <BooleanIcon/>;
			case 'number':
				return <NumberIcon/>;
			case 'text':
				return <TextIcon/>;
		}
	}

	const fieldTitle = activeForm.charAt(0).toUpperCase() + activeForm.slice(1);

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
							<ReorderIcon />
						</button>
					</span>
					<button
						className="edit"
						onClick={() => alert("Editing saved fields is not yet implemented.")}
						aria-label={`Edit the ${data.name} field`}
					>
						<span className="type">{showTypeIcon()}{typeLabel}</span>
						<span className="widest"><strong>{data.name}</strong></span>
					</button>
					<span>
						<button
							className="options"
							onClick={() => alert("Field options are not yet implemented.")}
							aria-label={`Options for the ${data.name} field.`}
						>
							<OptionsIcon/>
						</button>
					</span>
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
