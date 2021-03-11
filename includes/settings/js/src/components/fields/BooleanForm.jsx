import React, {useState} from 'react';
import camelcase from "camelcase";
import { useForm } from "react-hook-form";
import { useLocationSearch } from "../../utils";
import { ErrorIcon } from "../icons";

const { apiFetch } = wp;

function BooleanForm({cancelAction, updateAction, id, position}) {
	const { register, handleSubmit, errors, setValue, clearErrors, setError } = useForm();
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
			} else {
				console.warn('Unknown error. (200 status but ‘success’ was false.)', res );
			}
		} ).catch( err => {
			if ( err.code === 'wpe_duplicate_content_model_field_id' ) {
				setError("slug", {type: "idExists"});
			}
			if ( err.code === 'wpe_invalid_content_model' ) {
				console.error('Attempted to create a field in a model that no longer exists.');
			}
		});
	}

	return (
		<form onSubmit={handleSubmit(apiAddField)}>
			<input id="type" name="type" type="hidden" ref={register} value="boolean" />
			<input id="id" name="id" type="hidden" ref={register} value={id} />
			<input id="model" name="model" type="hidden" ref={register} value={model} />
			<input id="position" name="position" type="hidden" ref={register} value={position} />
			<div className="columns">
				<div className="left-column">
					<div className="field">
						<label
							className={errors.slug && 'alert'}
							htmlFor="name"
						>
							Name
						</label><br/>
						<p className="help">Display name for your boolean field, e.g. "Accept Terms".</p>
						<input
							aria-invalid={errors.name ? "true" : "false"}
							id="name"
							name="name"
							placeholder="Name"
							ref={register({ required: true, maxLength: 50})}
							onChange={ e => {
								setValue( 'slug', camelcase( e.target.value ) );
								setNameCount(e.target.value.length);
								clearErrors('slug');
							} } />
						<p className="field-messages">
							{errors.name && errors.name.type === "required" && (
								<span className="error"><ErrorIcon /><span role="alert">This field is required</span></span>
							)}
							{errors.name && errors.name.type === "maxLength" && (
								<span className="error"><ErrorIcon /><span role="alert">Exceeds max length.</span></span>
							)}
							<span>&nbsp;</span>
							<span className="count">{nameCount}/50</span>
						</p>
					</div>
				</div>

				<div className="right-column">
					<div className="field">
						<label
							className={errors.slug && 'alert'}
							htmlFor="slug"
						>
							API Identifier
						</label><br/>
						<p className="help">Auto-generated and used for API requests.</p>
						<input
							id="slug"
							name="slug"
							className={errors.slug && 'alert'}
							ref={register({ required: true, maxLength: 20 })}
							readOnly="readOnly" />
						<p className="field-messages">
							{errors.slug && errors.slug.type === "required" && (
								<span className="error"><ErrorIcon /><span role="alert">This field is required</span></span>
							)}
							{errors.slug && errors.slug.type === "maxLength" && (
								<span className="error"><ErrorIcon /><span role="alert">Exceeds max length of 20.</span></span>
							)}
							{errors.slug && errors.slug.type === "idExists" && (
								<span className="error"><ErrorIcon /><span role="alert">Another field in this model has the same API identifier.</span></span>
							)}
						</p>
					</div>
				</div>
			</div>

			<div className="buttons">
				<button type="submit" className="primary first">Create</button>
				<button className="tertiary" onClick={() => cancelAction(id)}>Cancel</button>
			</div>
		</form>
	);
}

export default BooleanForm;
