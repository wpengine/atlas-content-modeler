import React, {useState} from 'react';
import camelcase from "camelcase";
import {Link} from "react-router-dom";
import { useForm } from "react-hook-form";

function TextForm() {
	const { register, handleSubmit, errors, setValue } = useForm();
	const [ nameCount, setNameCount ] = useState(0);

	function apiAddField() {

	}

	return (
		<form onSubmit={handleSubmit(apiAddField)}>
			<div>
				<label htmlFor="name">Name</label><br/>
				<p className="help">Singular display name for your content model, e.g. "Rabbit".</p>
				<input id="name" name="name" placeholder="Name" ref={register({ required: true, maxLength: 50})} onChange={ e => setNameCount(e.target.value.length)} />
				<p className="limit">{nameCount}/50</p>
			</div>

			<div>
				<label htmlFor="fieldSlug">API Identifier</label><br/>
				<p className="help">Auto-generated and used for API requests.</p>
				<input id="fieldSlug" name="fieldSlug" ref={register({ required: true, maxLength: 20 })} readOnly="readOnly" />
			</div>

			<div>
				<legend>Text Length</legend>
				<fieldset>
					<input type="radio" id="short" name="textLength" value="short" ref={register} checked />
					<label className="radio" htmlFor="short">Short text (maximum 50 characters)</label><br/>
					<input type="radio" id="long" name="textLength" value="long" ref={register} />
					<label className="radio" htmlFor="long">Long text (maximum 500 characters)</label>
				</fieldset>
			</div>

			<button type="submit" className="primary first">Create</button>
			<Link to="/wp-admin/admin.php?page=wpe-content-model">
				<button className="tertiary">Cancel</button>
			</Link>
		</form>
	);
}

export default TextForm;
