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

	useEffect(() => {
		const getModel = async () => {
			const model = await apiFetch({
				path: `/wpe/content-model/${id}`,
				_wpnonce: wpApiSettings.nonce,
			});

			setModel(model.data);
			setFields({
				data: model?.data?.fields ?? {},
				order: getFieldOrder(model?.data?.fields)
			});
			setLoading(false);
		}

		getModel();
	}, [] );

	function addField(position) {
		const newId = Date.now();
		setFields(oldFields => {
			const newData = { ...oldFields.data, [newId]: { id: newId, type: 'text', open: true, position } }
			return { data: newData, order: getFieldOrder(newData) };
		});
	}

	// Close the field and update its data.
	function updateField(data) {
		setFields(oldFields => {
			const newData = { ...oldFields.data, [data.id]: { ...data, open: false } };
			return { data: newData, order: getFieldOrder(newData) };
		});
	}

	// Remove field with the given ID. Does not persist data.
	// Used to remove a field that has not yet been saved.
	function removeField(id) {
		setFields(oldFields => {
			delete oldFields.data[id];
			const newData = {...oldFields.data};
			return { data: newData, order: getFieldOrder(newData) };
		});
	}

	// Gives an array of field IDs in the order they should appear based
	// on their position property, with the ID of the lowest position first.
	function getFieldOrder(fields) {
		if (typeof fields !== 'object') {
			return [];
		}

		return Object
			.keys(fields)
			.map((key) => {
				return {
					position: fields[key]['position'],
					id: fields[key]['id'],
				}
			})
			.sort((field1, field2) => field1.position - field2.position)
			.map(field => field.id);
	}

	// Instead of incrementing field positions by 1, increment with a gap.
	// This allows new fields to be inserted between others without
	// affecting the position values of surrounding fields.
	function getPositionAfter(id) {
		const POSITION_GAP = 10000;

		const myOrder = fields?.order?.indexOf(id);
		const myPosition = parseFloat(fields?.data[id]?.position);

		// Last field. Just add the gap.
		if (myOrder + 1 === Object.keys(fields?.data)?.length) {
			return myPosition + POSITION_GAP;
		}

		// Otherwise add half the difference between my position and the next field's position.
		const nextFieldId = fields?.order[myOrder+1];
		const nextFieldPosition = parseFloat(fields?.data[nextFieldId]?.position);

		if (nextFieldPosition) {
			return (myPosition + nextFieldPosition) / 2;
		}

		return 0;
	}

	if ( loading ) {
		return (
			<div className="app-card">
				<p>Loading…</p>
			</div>
		);
	}

	const fieldCount = Object.keys(fields.data).length;

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
						<ul className="field-list">
							{
								fields.order.map( (id) => {
									const {type, position, open=false} = fields.data[id];
									const positionAfter = getPositionAfter(id);

									return <Field
											key={id}
											id={id}
											type={type}
											open={open}
											data={fields?.data[id]}
											cancelAction={removeField}
											updateAction={updateField}
											addAction={addField}
											position={position}
											positionAfter={positionAfter}
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
								<button onClick={() => addField(0)}>
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
