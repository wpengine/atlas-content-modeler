import React, { useContext, useEffect, useState, useRef } from "react";
import { Link } from "react-router-dom";
import {
	DndContext,
	closestCenter,
	KeyboardSensor,
	PointerSensor,
	useSensor,
	useSensors,
} from "@dnd-kit/core";
import {
	SortableContext,
	sortableKeyboardCoordinates,
	verticalListSortingStrategy,
} from "@dnd-kit/sortable";
import { useLocationSearch } from "../utils";
import Icon from "./icons"
import Field from "./fields/Field"
import { ModelsContext } from "../ModelsContext";
import {
	getFieldOrder,
	getPositionAfter,
	getNextFieldId,
	getPreviousFieldId,
	getRootFields,
} from "../queries";
import { handleDragEnd } from "./fields/eventHandlers";

const { apiFetch, a11y } = wp;

export default function EditContentModel() {
	const [infoTag, setInfoTag] = useState(null);
	const [positionsChanged, setPositionsChanged] = useState(false);
	const {models, dispatch} = useContext(ModelsContext);
	const query = useLocationSearch();
	const id = query.get('id');
	const model = models?.hasOwnProperty(id) ? models[id] : {};
	const fields = model?.fields ? getRootFields(model.fields) : {};
	const sensors = useSensors(
		useSensor(PointerSensor),
		useSensor(KeyboardSensor, {
			coordinateGetter: sortableKeyboardCoordinates,
		})
	);
	const positionUpdateTimer = useRef(0);
	const positionUpdateDelay = 1000;

	// Send updated field positions to the database when the user reorders them.
	useEffect(() => {
		if (!positionsChanged) return;
		const idsAndNewPositions = Object.values(model?.fields).reduce((result, field) => {
			result[field.id] = { position: field.position };
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
	}, [positionsChanged, model]);

	/**
	 * Swap field positions to reorder them in the list.
	 * Triggers database storage after positionUpdateDelay
	 * if no further position changes occur.
	 */
	function swapFieldPositions(id1, id2, speak=true) {
		// Prevent database updates if the user changes the order quickly.
		clearTimeout(positionUpdateTimer.current);

		// Invalid IDs should not be swapped.
		if (id1 === -1 || id2 === -1) {
			return;
		}

		dispatch({type: 'swapFieldPositions', id1, id2, model: id})

		// Speak list order changes to screen reader users.
		if ( speak ) {
			const fieldOrder = getFieldOrder(fields);
			const currentFieldPosition = fieldOrder?.indexOf(id2) + 1;
			const fieldCount = fieldOrder?.length;
			a11y.speak(
				`${fields[id1]?.name}, new position in list: ${currentFieldPosition} of ${fieldCount}`,
				"assertive"
			);
		}

		// Persist changes to the database after the delay time.
		positionUpdateTimer.current = setTimeout(() => setPositionsChanged(true), positionUpdateDelay);
	}

	const fieldCount = Object.keys(fields).length;
	const orderedFields = getFieldOrder(fields);

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
							<DndContext
								sensors={sensors}
								collisionDetection={closestCenter}
								onDragEnd={(event) => {handleDragEnd(event, orderedFields, id, dispatch)}}
							>
								<SortableContext
									items={orderedFields}
									strategy={verticalListSortingStrategy}
								>
							{
								orderedFields.map( (id) => {
									const {type, position, open=false, editing=false} = fields[id];
									const positionAfter = getPositionAfter(id, fields);

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
											previousFieldId={getPreviousFieldId(id, fields)}
											nextFieldId={getNextFieldId(id, fields)}
											position={position}
											positionAfter={positionAfter}
									/>
								} )
							}
								</SortableContext>
							</DndContext>
						</ul>
					</>
				)
					:
				(
					<>
						<p>Your current model {name} has no fields at the moment. It might be a good idea to add some now.</p>
						<ul className="field-list">
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
