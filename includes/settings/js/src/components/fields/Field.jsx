import React, {useState, useEffect} from 'react';
import TextForm from "./TextForm";

const BooleanForm = () => <p>I will be the boolean form.</p>;
const NumberForm = () => <p>I will be the number form.</p>;

function Field({type='text', open=false, cancelAction}) {
	const [activeForm, setActiveForm] = useState(type);

	useEffect(() => {
		if ( type === 'new' ) {
			type = 'text';
			setActiveForm(type)
			open = true;
		}
	}, [])

	const fieldTitle = activeForm.charAt(0).toUpperCase() + activeForm.slice(1);

	return (
		// TODO: add a key to li when we have unique data to give it.
		<li>
			<div>
				<button onClick={() => setActiveForm('text')}>Text</button>
				<button onClick={() => setActiveForm('number')}>Number</button>
				<button onClick={() => setActiveForm('boolean')}>Boolean</button>
			</div>
			<h3>New {fieldTitle} Block</h3>
			{ activeForm === 'text' && <TextForm cancelAction={cancelAction}/> }
			{ activeForm === 'boolean' && <BooleanForm /> }
			{ activeForm === 'number' && <NumberForm /> }
		</li>
	);
}

export default Field;
