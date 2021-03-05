import React, { useState } from "react";
import { useForm } from "react-hook-form";
import camelcase from "camelcase";
import { Link, useHistory } from "react-router-dom";
// import apiFetch from "@wordpress/api-fetch";
const { apiFetch } = wp;

export default function CreateContentModel() {
	const { register, handleSubmit, errors, setValue } = useForm();
	const history = useHistory();
	const [ singularCount, setSingularCount ] = useState(0);
	const [ pluralCount, setPluralCount ] = useState(0);
	const [ descriptionCount, setDescriptionCount ] = useState(0);
	function apiCreateModel( data ) {
		apiFetch( {
			path: '/wpe/content-model',
			method: 'POST',
			_wpnonce: wpApiSettings.nonce,
			data: {
				singular: data.labelSingular,
				plural: data.labelPlural,
				postTypeSlug: data.postTypeSlug,
				description: data.description
			},
		} ).then( res => {
			if ( res.success ) {
				history.push( "/wp-admin/admin.php?page=wpe-content-model&view=edit-model&id=" + data.postTypeSlug );
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
		<div className="app-card">
			<section className="heading">
				<h2>New Content Model</h2>
				<Link to="/wp-admin/admin.php?page=wpe-content-model">
					<button className="tertiary">View All Models</button>
				</Link>
			</section>
			<section className="card-content">
				<form onSubmit={handleSubmit(apiCreateModel)}>
					<div>
						<label htmlFor="labelSingular">Singular Name</label><br/>
						<p className="help">Singular display name for your content model, e.g. "Rabbit".</p>
						<input id="labelSingular" name="labelSingular" placeholder="Rabbit" ref={register({ required: true, maxLength: 50})} onChange={ e => setSingularCount(e.target.value.length)} />
						<p className="limit">{singularCount}/50</p>
					</div>

					<div>
						<label htmlFor="labelPlural">Plural Name</label><br/>
						<p className="help">Plural display name for your content model, e.g. "Rabbits".</p>
						<input
							id="labelPlural"
							name="labelPlural"
							placeholder="Rabbits"
							ref={register({ required: true, maxLength: 50})}
							onChange={
								event => {
									setValue( 'postTypeSlug', camelcase( event.target.value ) );
									setPluralCount(event.target.value.length)
								} }/>
						<p className="limit">{pluralCount}/50</p>
					</div>

					<div>
						<label htmlFor="postTypeSlug">API Identifier</label><br/>
						<p className="help">Auto-generated and used for API requests.</p>
						<input id="postTypeSlug" name="postTypeSlug" ref={register({ required: true, maxLength: 20 })} readOnly="readOnly" />
					</div>

					<div>
						<label htmlFor="description">Description</label><br/>
						<p className="help">A hint for content editors and API users.</p>
						<textarea id="description" name="description" ref={register({ maxLength: 250})} onChange={ e => setDescriptionCount(e.target.value.length)} />
						<p className="limit">{descriptionCount}/250</p>
					</div>

					<button type="submit" className="primary first">Create</button>
					<Link to="/wp-admin/admin.php?page=wpe-content-model">
						<button className="tertiary">Cancel</button>
					</Link>
				</form>
			</section>
		</div>
	);
}
