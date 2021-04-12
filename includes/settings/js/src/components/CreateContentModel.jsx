import React, { useContext, useState } from "react";
import { useForm } from "react-hook-form";
import { Link, useHistory } from "react-router-dom";
import { ModelsContext } from "../ModelsContext";
import { insertSidebarMenuItem } from "../utils";
import { useApiIdGenerator } from "./fields/useApiIdGenerator";
import { showSuccess } from "../toasts";

const { apiFetch } = wp;

export default function CreateContentModel() {
	const { register, handleSubmit, errors, setValue } = useForm();
	const history = useHistory();
	const [ singularCount, setSingularCount ] = useState(0);
	const [ pluralCount, setPluralCount ] = useState(0);
	const [ descriptionCount, setDescriptionCount ] = useState(0);
	const { dispatch } = useContext(ModelsContext);
	const { setApiIdGeneratorInput, apiIdFieldAttributes } = useApiIdGenerator({
		apiFieldId: "postTypeSlug",
		setValue
	});

	function apiCreateModel( data ) {
		apiFetch( {
			path: '/wpe/content-model',
			method: 'POST',
			_wpnonce: wpApiSettings.nonce,
			data,
		} ).then( res => {
			if ( res.success ) {
				dispatch({type: 'addModel', data: res.model})
				history.push( "/wp-admin/admin.php?page=wpe-content-model&view=edit-model&id=" + data.postTypeSlug );

				// Insert the sidebar menu item below the Comments item, to avoid doing a full page refresh.
				insertSidebarMenuItem(res.model);

				window.scrollTo(0, 0);
				showSuccess(`Your new Content Model “${res.model.name}” was created.`);
			}
		} ).catch( errors => {
			// @todo show errors
			console.log(errors);
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
									setApiIdGeneratorInput(event.target.value);
									setPluralCount(event.target.value.length);
								} }/>
						<p className="field-messages"><span>&nbsp;</span><span className="count">{pluralCount}/50</span></p>
					</div>

					<div className="field">
						<label htmlFor="postTypeSlug">API Identifier</label><br/>
						<p className="help">Auto-generated from the plural name and used for API requests.</p>
						<input
							id="postTypeSlug"
							name="postTypeSlug"
							ref={register({ required: true, maxLength: 20 })}
							{...apiIdFieldAttributes}
						/>
					</div>

					<div className="field">
						<label htmlFor="description">Description</label><br/>
						<p className="help">A hint for content editors and API users.</p>
						<textarea id="description" name="description" ref={register({ maxLength: 250})} onChange={ e => setDescriptionCount(e.target.value.length)} />
						<p className="field-messages"><span>&nbsp;</span><span className="count">{descriptionCount}/250</span></p>
					</div>

					<button type="submit" className="primary first">Create</button>
					<button
						className="tertiary"
						onClick={() => history.push("/wp-admin/admin.php?page=wpe-content-model")}
					>
						Cancel
					</button>
				</form>
			</section>
		</div>
	);
}
