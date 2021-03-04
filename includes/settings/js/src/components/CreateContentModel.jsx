import React from "react";
import { useForm } from "react-hook-form";
import camelcase from "camelcase";
import { Link, useHistory } from "react-router-dom";
// import apiFetch from "@wordpress/api-fetch";
const { apiFetch } = wp;

export default function CreateContentModel() {
	const { register, handleSubmit, errors, setValue } = useForm();
	const history = useHistory();

	function apiCreateModel( data ) {
		apiFetch( {
			path: '/wpe/content-model',
			method: 'POST',
			_wpnonce: wpApiSettings.nonce,
			data: {
				singular: data.labelSingular,
				plural: data.labelPlural,
				postTypeSlug: data.postTypeSlug
			},
		} ).then( res => {
			if ( res.success ) {
				history.push( "/wp-admin/admin.php?page=wpe-content-model&view=edit-model&model=" + data.postTypeSlug );
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
		<form onSubmit={handleSubmit(apiCreateModel)}>

			<p><label htmlFor="labels">Singular Name</label></p>
			<p className="description">Singular display name for your content model, e.g. "Rabbit".</p>
			<p><input id="labelSingular" name="labelSingular" placeholder="Rabbit" ref={register({ required: true})} /></p>
			{errors.labelSingular?.type === 'required' && <p>This field is required.</p>}

			<p><label htmlFor="labels">Plural Name</label></p>
			<p className="description">Plural display name for your content model, e.g. "Rabbits".</p>
			<p><input id="labelPlural" name="labelPlural" placeholder="Rabbits" ref={register({ required: true})} onChange={ (event) => setValue( 'postTypeSlug', camelcase( event.target.value ) ) }/></p>
			{errors.labelPlural?.type === 'required' && <p>This field is required.</p>}

			<p><label htmlFor="postTypeSlug">API Identifier</label></p>
			<p className="description">Auto-generated and used for accessing this content type via APIs.</p>
			<p><input id="postTypeSlug" name="postTypeSlug" ref={register({ required: true, maxLength: 20 })} readOnly="readOnly" /></p>
			{errors.postTypeSlug?.type === 'required' && <p>This field is required.</p>}
			{errors.postTypeSlug?.type === 'maxLength' && <p>This field is limited to 20 characters.</p>}

			<p className="submit">
				<input className="button button-primary" type="submit" value="Create"/>
				<Link className="button button-secondary" to="/wp-admin/admin.php?page=wpe-content-model">Cancel</Link>
			</p>
		</form>
);
}
