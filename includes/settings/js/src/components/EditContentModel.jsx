import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { useLocationSearch } from "../utils";
import { AddIcon, OptionsIcon } from "./icons"
import Field from "./fields/Field"
const { apiFetch } = wp;

export default function EditContentModel() {
	const [loading, setLoading] = useState(true);
	const [model, setModel] = useState(null);
	const [fields, setFields] = useState({});

	const query = useLocationSearch();
	const id = query.get('id');

	useEffect(async () => {
		const model = await apiFetch( {
			path: `/wpe/content-model/${id}`,
			_wpnonce: wpApiSettings.nonce,
		} );

		setModel(model.data);
		// TODO: sort fields by their position key here.
		setFields(model?.data?.fields ?? {});
		setLoading(false);
	}, [] );

	function addField(e) {
		e.preventDefault();
		const newId = Date.now();
		setFields(oldFields => {
			return { ...oldFields, [newId]: { id: newId, type: 'new', position: 0, open: true } }
		});
	}

	// Close the field and update its data.
	function updateField(data) {
		setFields(oldFields => {
			return { ...oldFields, [data.id]: { ...data, open: false } }
		});
	}

	function removeField(e) {
		e.preventDefault();
		// TODO: remove the specific field by ID.
		setFields({});
	}

	if ( loading ) {
		return (
			<div className="app-card">
				<p>Loading…</p>
			</div>
		);
	}

	const fieldCount = Object.keys(fields).length;

	return (
		<div className="app-card">
			<section className="heading">
			<h2><Link to="/wp-admin/admin.php?page=wpe-content-model">Content Models</Link> / {model?.name}</h2>
			<button className="options" aria-label={`Options for ${model?.name} content model`}>
				<OptionsIcon/>
			</button>
		</section>
		<section className="card-content">
			{ fieldCount > 0 ?
				(
					<>
						<p>{fieldCount} {fieldCount > 1 ? 'Fields' : 'Field'}.</p>
						<ul className="model-list">
							{
								Object.keys(fields).map( (id) => {
									const {type,open=false} = fields[id];
									return <Field
											key={id}
											id={id}
											type={type}
											open={open}
											data={fields[id]}
											cancelAction={removeField}
											updateAction={updateField}
											addAction={addField}
									/>
								} )
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
