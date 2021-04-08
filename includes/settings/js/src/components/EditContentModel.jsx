import React, { useContext, useEffect, useState, useRef } from "react";
import { Link } from "react-router-dom";
import { DragDropContext, Droppable } from "react-beautiful-dnd";
import { useLocationSearch } from "../utils";
import { onDragEnd } from "./fields/eventHandlers";
import Icon from "./icons"
import Field from "./fields/Field"
import { ModelsContext } from "../ModelsContext";
import {
	getFieldOrder,
	getPositionAfter,
	getRootFields,
} from "../queries";

export default function EditContentModel() {
	const [infoTag, setInfoTag] = useState(null);
	const { models, dispatch } = useContext(ModelsContext);
	const query = useLocationSearch();
	const id = query.get('id');
	const model = models?.hasOwnProperty(id) ? models[id] : {};
	const fields = model?.fields ? getRootFields(model.fields) : {};
	const fieldCount = Object.keys(fields).length;
	const fieldOrder = getFieldOrder(fields);

	return (
		<div className="app-card">
			<section className="heading">
				<h2>
					<Link to="/wp-admin/admin.php?page=wpe-content-model">
						Content Models
					</Link>{" "}
					/ {model?.name}
				</h2>
				<button
					className="options"
					aria-label={`Options for ${model?.name} content model`}
				>
					<Icon type="options" />
				</button>
			</section>
			<section className="card-content">
				{fieldCount > 0 ? (
					<>
						<p className="field-list-info">
							{fieldCount} {fieldCount > 1 ? "Fields" : "Field"}. &nbsp;
							<span className="info-text">{infoTag}</span>
						</p>
						<ul className="field-list">
							<DragDropContext
								onDragEnd={(result) =>
									onDragEnd(result, fieldOrder, model?.slug, dispatch, models)
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
													/>
												);
											})}
											{provided.placeholder}
										</div>
									)}
								</Droppable>
							</DragDropContext>
						</ul>
					</>
				) : (
					<>
						<p>
							Your current model {name} has no fields at the moment. It might be a
							good idea to add some now.
						</p>
						<ul className="field-list">
							<li className="empty">
								<span>&nbsp;</span>
								<span>&nbsp;</span>
								<span>&nbsp;</span>
								<span>&nbsp;</span>
							</li>
							<li className="add-item">
								<button
									onClick={() =>
										dispatch({ type: "addField", position: 0, model: id })
									}
								>
									<Icon type="add" />
								</button>
							</li>
						</ul>
					</>
				)}
			</section>
		</div>
	);
}
