import React, {useContext} from 'react';
import Icon from "../icons"
import Field from "./Field";
import { onDragEnd } from "./eventHandlers";
import { ModelsContext } from "../../ModelsContext";
import {
	getFieldOrder,
	getPositionAfter,
} from "../../queries";
import {DragDropContext, Droppable} from "react-beautiful-dnd";

const Repeater = ({fields={}, model, parent, setInfoTag}) => {
	const {dispatch } = useContext(ModelsContext);
	const hasFields = Object.keys(fields)?.length > 0;
	const fieldOrder = getFieldOrder(fields);

	return (
		<>
			<div className="break">&nbsp;</div>
			<div className="repeater-fields">
				{hasFields ? (
					<ul className="subfield-list">
						<DragDropContext
							onDragEnd={(result) =>
								onDragEnd(result, fieldOrder, model?.slug, dispatch)
							}
						>
							<Droppable droppableId="droppable">
								{(provided, snapshot) => (
									<div {...provided.droppableProps} ref={provided.innerRef}>
										{fieldOrder.map((id, index) => {
											const {
												type,
												position,
												open = false,
												editing = false,
											} = fields[id];

											return (
												<Field
													key={id}
													id={id}
													index={index}
													model={model}
													type={type}
													open={open}
													editing={editing}
													data={fields[id]}
													setInfoTag={setInfoTag}
													position={position}
													positionAfter={getPositionAfter(id, fields)}
													parent={parent}
												/>
											);
										})}
										{provided.placeholder}
									</div>
								)}
							</Droppable>
						</DragDropContext>
					</ul>
				) : (
					<>
						<ul className="subfield-list empty">
							<li className="empty">
								<span>&nbsp;</span>
								<span>&nbsp;</span>
								<span>&nbsp;</span>
								<span>&nbsp;</span>
							</li>
							<li className="add-item">
								<button
									onClick={() =>
										dispatch({
											type: "addField",
											position: 0,
											model: model.slug,
											parent,
										})
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
