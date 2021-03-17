import React, {useState} from 'react';
import camelcase from "camelcase";
import { useForm } from "react-hook-form";
import { useLocationSearch } from "../../utils";
import Icon from "../icons";
import TextFields from "./TextFields";
import NumberFields from "./NumberFields";
import MediaFields from "./MediaFields";

const { apiFetch } = wp;

const extraFields = {
	text: TextFields,
	media: MediaFields,
	number: NumberFields,
};

function Form({cancelAction, closeAction, updateAction, id, position, type, editing, storedData}) {
	const { register, handleSubmit, errors, setValue, clearErrors, setError } = useForm();
	const [ nameCount, setNameCount ] = useState(storedData?.name?.length || 0);
	const query = useLocationSearch();
	const model = query.get('id');
	const ExtraFields = extraFields[type] ?? null;

	function apiAddField(data) {
		apiFetch( {
			path: `/wpe/content-model-field`,
			method: editing ? 'PUT' : 'POST',
			_wpnonce: wpApiSettings.nonce,
			data,
		} ).then( res => {
			if ( res.success ) {
				updateAction(data);
			} else {
				// The user pressed “Update” but no data changed.
				// Just close the field as if it was updated.
				closeAction(data.id);
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
			<input id="type" name="type" type="hidden" ref={register} value={type} />
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
						<p className="help">Display name for your {type} field.</p>
						<input
							aria-invalid={errors.name ? "true" : "false"}
							id="name"
							name="name"
							defaultValue={storedData?.name}
							placeholder="Name"
							ref={register({ required: true, maxLength: 50})}
							onChange={ e => {
								setValue( 'slug', camelcase( e.target.value ) );
								setNameCount(e.target.value.length);
								clearErrors('slug');
							} } />
						<p className="field-messages">
							{errors.name && errors.name.type === "required" && (
								<span className="error"><Icon type="error" /><span role="alert">This field is required</span></span>
							)}
							{errors.name && errors.name.type === "maxLength" && (
								<span className="error"><Icon type="error" /><span role="alert">Exceeds max length.</span></span>
							)}
							<span>&nbsp;</span>
							<span className="count">{nameCount}/50</span>
						</p>
					</div>
					{ (type in extraFields) && <ExtraFields editing={editing} data={storedData} register={register} /> }
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
							defaultValue={storedData?.slug}
							className={errors.slug && 'alert'}
							ref={register({ required: true, maxLength: 20 })}
							readOnly="readOnly" />
						<p className="field-messages">
							{errors.slug && errors.slug.type === "required" && (
								<span className="error"><Icon type="error" /><span role="alert">This field is required</span></span>
							)}
							{errors.slug && errors.slug.type === "maxLength" && (
								<span className="error"><Icon type="error" /><span role="alert">Exceeds max length of 20.</span></span>
							)}
							{errors.slug && errors.slug.type === "idExists" && (
								<span className="error"><Icon type="error" /><span role="alert">Another field in this model has the same API identifier.</span></span>
							)}
						</p>
					</div>
				</div>
			</div>

			<div className="buttons">
				<button type="submit" className="primary first">{editing ? 'Update' : 'Create'}</button>
				<button
					className="tertiary"
					onClick={(event) => {
						event.preventDefault();
						editing ? closeAction(id) : cancelAction(id)
					}}
				>
					Cancel
				</button>
			</div>
		</form>
	);
}

export default Form;
