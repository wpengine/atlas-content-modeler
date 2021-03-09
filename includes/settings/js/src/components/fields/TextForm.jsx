import React, {useState} from 'react';
import camelcase from "camelcase";
import {Link} from "react-router-dom";
import { useForm } from "react-hook-form";
import { useLocationSearch } from "../../utils";

const { apiFetch } = wp;

function TextForm({cancelAction, updateAction, id}) {
	const { register, handleSubmit, errors, setValue } = useForm();
	const [ nameCount, setNameCount ] = useState(0);
	const query = useLocationSearch();
	const model = query.get('id');

	function apiAddField(data) {
		apiFetch( {
			path: `/wpe/content-model-field`,
			method: 'POST',
			_wpnonce: wpApiSettings.nonce,
			data,
		} ).then( res => {
			if ( res.success ) {
				updateAction(data);
			}

			// @todo show errors
			if ( ! res.success && res.errors ) {
				res.errors.forEach( ( currentValue ) => {
					if ( typeof currentValue === 'object' ) {
						currentValue.errors.forEach( ( val ) => {
							console.log(val);
						} );
					}
				} );
			}
		} );
	}

	return (
		<form onSubmit={handleSubmit(apiAddField)}>
			<input id="type" name="type" type="hidden" ref={register} value="text" />
			<input id="id" name="id" type="hidden" ref={register} value={id} />
			<input id="model" name="model" type="hidden" ref={register} value={model} />
			{/*TODO: set the real position value here from a position prop, so new fields can be created in between others.*/}
			<input id="position" name="position" type="hidden" ref={register} value={0} />
			<div>
				<label htmlFor="name">Name</label><br/>
				<p className="help">Singular display name for your content model, e.g. "Rabbit".</p>
				<input
					id="name"
					name="name"
					placeholder="Name"
					ref={register({ required: true, maxLength: 50})}
					onChange={ e => {
						setValue( 'slug', camelcase( e.target.value ) );
						setNameCount(e.target.value.length);
					} } />
				<p className="limit">{nameCount}/50</p>
			</div>

			<div>
				<label htmlFor="slug">API Identifier</label><br/>
				<p className="help">Auto-generated and used for API requests.</p>
				<input id="slug" name="slug" ref={register({ required: true, maxLength: 20 })} readOnly="readOnly" />
			</div>

			<div>
				<legend>Text Length</legend>
				<fieldset>
					<input type="radio" id="short" name="textLength" value="short" ref={register} defaultChecked />
					<label className="radio" htmlFor="short">Short text (maximum 50 characters)</label><br/>
					<input type="radio" id="long" name="textLength" value="long" ref={register} />
					<label className="radio" htmlFor="long">Long text (maximum 500 characters)</label>
				</fieldset>
			</div>

			<div className="buttons">
				<button type="submit" className="primary first">Create</button>
				<button className="tertiary" onClick={cancelAction}>Cancel</button>
			</div>
		</form>
	);
}

export default TextForm;
