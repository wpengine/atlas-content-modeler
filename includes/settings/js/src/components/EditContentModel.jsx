import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { useLocationSearch } from "../utils";
import { AddIcon, OptionsIcon } from "./icons"
import { Field } from "./fields"
const { apiFetch } = wp;

export default function EditContentModel() {
	const [loading, setLoading] = useState(true);
	const [model, setModel] = useState(null);
	const [fields, setFields] = useState([]);

	const query = useLocationSearch();
	const id = query.get('id');

	useEffect(async () => {
		const model = await apiFetch( {
			path: `/wpe/content-model/${id}`,
			_wpnonce: wpApiSettings.nonce,
		} );

		setModel(model.data);
		setFields(model?.data?.fields ?? []);
		setLoading(false);
	}, [] );

	function addField(e) {
		e.preventDefault();
		setFields(oldFields => [...oldFields, { type: 'new', position: 0}]);
		console.log('Adding item…');
	}

	if ( loading ) {
		return (
			<div className="app-card">
				<p>Loading…</p>
			</div>
		);
	}

	return (
		<div className="app-card">
			<section className="heading">
			<h2><Link to="/wp-admin/admin.php?page=wpe-content-model">Content Models</Link> / {model?.name}</h2>
			<button className="options" aria-label={`Options for ${model?.name} content model`}>
				<OptionsIcon/>
			</button>
		</section>
		<section className="card-content">
			{ fields.length > 0 ?
				(
					<>
						<p>{fields.length} {fields.length > 1 ? 'Fields' : 'Field'}.</p>
						<ul className="model-list">
							{
								fields.map( (field) => { return <Field type={field.type} /> } )
							}
						</ul>
					</>
				)
					:
				(
					<>
						<p>Your current model {name} has no fields at the moment. It might be a good idea to add some now.</p>
						<ul className="model-list">
							<li className="empty"><span>&nbsp;</span><span>&nbsp;</span><span>&nbsp;</span><span>&nbsp;</span></li>
							<li className="add-item">
								<button onClick={addField}>
									<AddIcon/>
								</button>
							</li>
						</ul>
					</>
				)
			}
		</section>
	</div>
	);
}
