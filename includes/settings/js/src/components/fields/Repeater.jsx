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
	SortableContext, sortableKeyboardCoordinates,
	verticalListSortingStrategy,
} from "@dnd-kit/sortable";
import Icon from "../icons"
import Field from "./Field";
import { ModelsContext } from "../../ModelsContext";
import { handleDragEnd } from "./eventHandlers";
import {
	getFieldOrder,
	getPositionAfter,
	getPreviousFieldId,
	getNextFieldId,
} from "../../queries";

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

	return (
		<>
			<div className="break">&nbsp;</div>
			<div className="repeater-fields">
				{hasFields ? (
					<ul className="subfield-list">
						<DndContext
							sensors={sensors}
							collisionDetection={closestCenter}
							onDragEnd={(event) => handleDragEnd(event, fieldOrder, model.slug, dispatch)}
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
