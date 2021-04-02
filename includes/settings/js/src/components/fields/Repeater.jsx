import React, {useContext} from 'react';
import {
	DndContext,
	closestCenter,
	KeyboardSensor,
	PointerSensor,
	useSensor,
	useSensors,
} from "@dnd-kit/core";
import {
	arrayMove,
	SortableContext, sortableKeyboardCoordinates,
	verticalListSortingStrategy,
} from "@dnd-kit/sortable";
import Icon from "../icons"
import Field from "./Field";
import { ModelsContext } from "../../ModelsContext";
import {
	getFieldOrder,
	getPositionAfter,
	getPreviousFieldId,
	getNextFieldId,
} from "../../queries";

const { apiFetch } = wp;

const Repeater = ({fields={}, model, parent, swapAction, setInfoTag}) => {
	const {dispatch } = useContext(ModelsContext);
	const hasFields = Object.keys(fields)?.length > 0;
	const fieldOrder = getFieldOrder(fields);
	const sensors = useSensors(
		useSensor(PointerSensor),
		useSensor(KeyboardSensor, {
			coordinateGetter: sortableKeyboardCoordinates,
		})
	);

	function handleDragEnd(event) {
		const {active, over} = event;
		console.log('setting ordered fields');

		if (active.id !== over.id) {
			const oldIndex = fieldOrder.indexOf(active.id);
			const newIndex = fieldOrder.indexOf(over.id);
			const newOrder = arrayMove(fieldOrder, oldIndex, newIndex);

			let pos = 0;
			const idsAndNewPositions = newOrder.reduce((result, id) => {
				result[id] = {position: pos};
				pos += 10000;
				return result;
			}, {});

			dispatch({type: 'reorderFields', positions: idsAndNewPositions, model: model.slug});

			const updatePositions = async () => {
				await apiFetch({
					path: `/wpe/content-model-fields/${model.slug}`,
					method: "PATCH",
					_wpnonce: wpApiSettings.nonce,
					data: {fields: idsAndNewPositions},
				});
			};

			updatePositions().catch(err => console.error(err));
		}
	}

	return (
		<>
			<div className="break">&nbsp;</div>
			<div className="repeater-fields">
				{hasFields ? (
					<ul className="subfield-list">
						<DndContext
							sensors={sensors}
							collisionDetection={closestCenter}
							onDragEnd={handleDragEnd}
						>
							<SortableContext
								items={fieldOrder}
								strategy={verticalListSortingStrategy}
							>
								{fieldOrder.map((id) => {
									const { type, position, open = false, editing = false } = fields[id];

									return (
										<Field
											key={id}
											id={id}
											model={model}
											type={type}
											open={open}
											editing={editing}
											data={fields[id]}
											swapAction={swapAction}
											setInfoTag={setInfoTag}
											previousFieldId={getPreviousFieldId(id, fields)}
											nextFieldId={getNextFieldId(id, fields)}
											position={position}
											positionAfter={getPositionAfter(id, fields)}
											parent={parent}
										/>
									);
								})}
							</SortableContext>
						</DndContext>
					</ul>
				) : (
					<>
						<ul className="subfield-list">
							<li className="empty">
								<span>&nbsp;</span>
								<span>&nbsp;</span>
								<span>&nbsp;</span>
								<span>&nbsp;</span>
							</li>
							<li className="add-item">
								<button
									onClick={() =>
										dispatch({ type: "addField", position: 0, model: model.slug, parent })
									}
								>
									<Icon type="add" size="small" />
								</button>
							</li>
						</ul>
					</>
				)}
			</div>
		</>
	);
};

export default Repeater;
