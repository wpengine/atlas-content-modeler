import React, { useContext, useEffect, useState, useRef } from "react";
import { Link } from "react-router-dom";
import { useLocationSearch } from "../utils";
import Icon from "./icons"
import Field from "./fields/Field"
import { ModelsContext } from "../ModelsContext";
const { apiFetch, a11y } = wp;

export default function EditContentModel() {
	const [infoTag, setInfoTag] = useState(null);
	const [positionsChanged, setPositionsChanged] = useState(false);
	const { models, dispatch } = useContext(ModelsContext);
	const query = useLocationSearch();
	const id = query.get('id');
	const model = models?.hasOwnProperty(id) ? models[id] : {};
	const fields = model?.fields || {};
	const positionUpdateTimer = useRef(0);
	const positionUpdateDelay = 1000;

	// Send updated field positions to the database when the user reorders them.
	useEffect(() => {
		if (!positionsChanged) return;

		const idsAndNewPositions = fields?.order?.reduce((result, fieldId) => {
			result[fieldId] = { position: fields?.data[fieldId].position };
			return result;
		}, {});

		const updatePositions = async () => {
			await apiFetch({
				path: `/wpe/content-model-fields/${id}`,
				method: "PATCH",
				_wpnonce: wpApiSettings.nonce,
				data: { fields: idsAndNewPositions },
			});
		};

		updatePositions().catch(err => console.error(err));
		setPositionsChanged(false);
	}, [positionsChanged, fields]);

	// Swap field positions to reorder them in the list.
	// Triggers database storage after positionUpdateDelay
	// if no further position changes occur.
	function swapFieldPositions(id1, id2, speak=true) {
		// Prevent database updates if the user changes the order quickly.
		clearTimeout(positionUpdateTimer.current);

		// Invalid IDs should not be swapped.
		if (id1 === 0 || id2 === 0) {
			return;
		}

		setFields((oldFields) => {
			const newData = {
				...oldFields.data,
				[id1]: { ...oldFields.data[id1], position: oldFields.data[id2].position },
				[id2]: { ...oldFields.data[id2], position: oldFields.data[id1].position },
			};
			return { data: newData, order: getFieldOrder(newData) };
		});

		// Speak list order changes to screen reader users.
		if ( speak ) {
			const currentFieldPosition = fields?.order?.indexOf(id2) + 1;
			const fieldCount = fields?.order?.length;
			a11y.speak(
				`${fields?.data[id1]?.name}, new position in list: ${currentFieldPosition} of ${fieldCount}`,
				"assertive"
			);
		}

		// Persist changes to the database after the delay time.
		positionUpdateTimer.current = setTimeout(() => setPositionsChanged(true), positionUpdateDelay);
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

	// Get next field id without wrapping from last to first. 0 means no next item.
	function nextFieldId(id) {
		const fieldOrder = getFieldOrder(fields);
		const myIndex = fieldOrder?.indexOf(id);
		if (myIndex < 0) return 0; // No such id found.
		if (myIndex === fields?.order?.length - 1) return 0; // No item after last.
		return fieldOrder[myIndex + 1];
	}

	// Get previous field id without wrapping from first to last. 0 means no previous item.
	function previousFieldId(id) {
		const fieldOrder = getFieldOrder(fields);
		const myIndex = fieldOrder?.indexOf(id);
		if (myIndex < 0) return 0; // No such id found.
		if (myIndex === 0) return 0; // No item before first.
		return fieldOrder[myIndex - 1];
	}

	// Instead of incrementing field positions by 1, increment with a gap.
	// This allows new fields to be inserted between others without
	// affecting the position values of surrounding fields.
	function getPositionAfter(id) {
		const POSITION_GAP = 10000;
		const fieldOrder = getFieldOrder(fields);

		const myOrder = fieldOrder.indexOf(id);
		const myPosition = parseFloat(fields[id]?.position);

		// Last field. Just add the gap.
		if (myOrder + 1 === Object.keys(fieldOrder)?.length) {
			return myPosition + POSITION_GAP;
		}

		// Otherwise add half the difference between my position and the next field's position.
		const nextFieldId = fieldOrder[myOrder+1];
		const nextFieldPosition = parseFloat(fields[nextFieldId]?.position);

		if (nextFieldPosition) {
			return (myPosition + nextFieldPosition) / 2;
		}

		return 0;
	}

	if ( models === null ) {
		return (
			<div className="app-card">
				<p>Loading…</p>
			</div>
		);
	}

	const fieldCount = Object.keys(fields).length;
	const fieldOrder = getFieldOrder(fields);

	return (
		<div className="app-card">
			<section className="heading">
			<h2><Link to="/wp-admin/admin.php?page=wpe-content-model">Content Models</Link> / {model?.name}</h2>
			<button className="options" aria-label={`Options for ${model?.name} content model`}>
				<Icon type="options" />
			</button>
		</section>
		<section className="card-content">
			{ fieldCount > 0 ?
				(
					<>
						<p className="field-list-info">
							{fieldCount} {fieldCount > 1 ? 'Fields' : 'Field'}.
							&nbsp;
							<span className="info-text">{infoTag}</span>
						</p>
						<ul className="field-list">
							{
								fieldOrder.map( (id) => {
									const {type, position, open=false, editing=false} = fields[id];
									const positionAfter = getPositionAfter(id);

									return <Field
											key={id}
											id={id}
											model={model}
											type={type}
											open={open}
											editing={editing}
											data={fields[id]}
											swapAction={swapFieldPositions}
											setInfoTag={setInfoTag}
											previousFieldID={previousFieldId(id)}
											nextFieldID={nextFieldId(id)}
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

								<button
									onClick={() => dispatch({type: 'addField', position: 0, model: id})}
								>
									<Icon type="add" />
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
