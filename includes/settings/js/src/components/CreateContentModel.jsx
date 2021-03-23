import React, { useContext, useState } from "react";
import { useForm } from "react-hook-form";
import camelcase from "camelcase";
import { Link, useHistory } from "react-router-dom";
import { ModelsContext } from "../ModelsContext";

const { apiFetch } = wp;

export default function CreateContentModel() {
	const { register, handleSubmit, errors, setValue } = useForm();
	const history = useHistory();
	const [ singularCount, setSingularCount ] = useState(0);
	const [ pluralCount, setPluralCount ] = useState(0);
	const [ descriptionCount, setDescriptionCount ] = useState(0);
	const { refreshModels } = useContext(ModelsContext);

	function apiCreateModel( data ) {
		apiFetch( {
			path: '/wpe/content-model',
			method: 'POST',
			_wpnonce: wpApiSettings.nonce,
			data,
		} ).then( res => {
			if ( res.success ) {
				refreshModels();
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
				<button
					className="tertiary"
					onClick={() => history.push("/wp-admin/admin.php?page=wpe-content-model")}>
					View All Models
				</button>
			</section>
			<section className="card-content">
				<form onSubmit={handleSubmit(apiCreateModel)}>
					<div className="field">
						<label htmlFor="singular">Singular Name</label><br/>
						<p className="help">Singular display name for your content model, e.g. "Rabbit".</p>
						<input id="singular" name="singular" placeholder="Rabbit" ref={register({ required: true, maxLength: 50})} onChange={ e => setSingularCount(e.target.value.length)} />
						<p className="field-messages"><span>&nbsp;</span><span className="count">{singularCount}/50</span></p>
					</div>

					<div className="field">
						<label htmlFor="plural">Plural Name</label><br/>
						<p className="help">Plural display name for your content model, e.g. "Rabbits".</p>
						<input
							id="plural"
							name="plural"
							placeholder="Rabbits"
							ref={register({ required: true, maxLength: 50})}
							onChange={
								event => {
									setValue( 'postTypeSlug', camelcase( event.target.value ) );
									setPluralCount(event.target.value.length)
								} }/>
						<p className="field-messages"><span>&nbsp;</span><span className="count">{pluralCount}/50</span></p>
					</div>

					<div className="field">
						<label htmlFor="postTypeSlug">API Identifier</label><br/>
						<p className="help">Auto-generated and used for API requests.</p>
						<input id="postTypeSlug" name="postTypeSlug" ref={register({ required: true, maxLength: 20 })} readOnly="readOnly" />
					</div>

					<div className="field">
						<label htmlFor="description">Description</label><br/>
						<p className="help">A hint for content editors and API users.</p>
						<textarea id="description" name="description" ref={register({ maxLength: 250})} onChange={ e => setDescriptionCount(e.target.value.length)} />
						<p className="field-messages"><span>&nbsp;</span><span className="count">{descriptionCount}/250</span></p>
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
