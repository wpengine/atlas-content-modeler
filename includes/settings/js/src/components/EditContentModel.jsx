import React, { useContext, useState } from "react";
import { useHistory } from "react-router-dom";
import { DragDropContext, Droppable } from "react-beautiful-dnd";
import { useLocationSearch } from "../utils";
import { onDragEnd } from "./fields/eventHandlers";
import Field from "./fields/Field";
import { ModelsContext } from "../ModelsContext";
import { ContentModelDropdown } from "./ContentModelDropdown";
import Modal from "react-modal";

import {
	getFieldOrder,
	getPositionAfter,
	sanitizeFields,
	getOpenField,
} from "../queries";
import FieldButtons from "./FieldButtons";

Modal.setAppElement("#root");

export default function EditContentModel() {
	const [infoTag, setInfoTag] = useState(null);
	const { models, dispatch } = useContext(ModelsContext);
	const [unsavedChangesModal, updateUnsavedChangesModal] = useState({
		open: false,
		field: {},
	});
	const query = useLocationSearch();
	const id = query.get("id");
	const model = models?.hasOwnProperty(id) ? models[id] : {};
	const fields = model?.fields ? sanitizeFields(model.fields) : {};
	const fieldCount = Object.keys(fields).length;
	const fieldOrder = getFieldOrder(fields);
	const history = useHistory();

	const customStyles = {
		overlay: {
			backgroundColor: "rgba(0, 40, 56, 0.7)",
		},
		content: {
			top: "50%",
			left: "50%",
			right: "auto",
			bottom: "auto",
			marginRight: "-50%",
			transform: "translate(-50%, -50%)",
			border: "none",
			padding: "40px",
		},
	};

	const promptToSaveChanges = () => {
		const openField = getOpenField(fields);
		if (Object.keys(openField).length > 0) {
			updateUnsavedChangesModal({ open: true, field: openField });
			return true;
		}
		return false;
	};

	const closeUnsavedChangesModal = () => {
		updateUnsavedChangesModal({
			open: false,
			field: {},
		});
	};

	console.log(unsavedChangesModal);

	return (
		<div className="app-card">
			<section className="heading">
				<h2 className="pr-1 pr-sm-0">
					<a
						href="#"
						onClick={(event) => {
							event.preventDefault();
							if (!promptToSaveChanges()) {
								history.push(atlasContentModeler.appPath);
							}
						}}
					>
						Content Models
					</a>{" "}
					/ {model?.plural}
				</h2>
				<ContentModelDropdown model={model} />
			</section>
			<section className="card-content">
				{fieldCount > 0 ? (
					<>
						<p className="field-list-info">
							{fieldCount} {fieldCount > 1 ? "Fields" : "Field"}.
							&nbsp;
							<span className="info-text">{infoTag}</span>
						</p>
						<ul className="field-list">
							<DragDropContext
								onDragEnd={(result) =>
									onDragEnd(
										result,
										fieldOrder,
										model?.slug,
										dispatch,
										models
									)
								}
							>
								<Droppable droppableId="droppable">
									{(provided, snapshot) => (
										<div
											{...provided.droppableProps}
											ref={provided.innerRef}
										>
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
														promptToSaveChanges={
															promptToSaveChanges
														}
														position={position}
														positionAfter={getPositionAfter(
															id,
															fields
														)}
													/>
												);
											})}
											{provided.placeholder}
										</div>
									)}
								</Droppable>
							</DragDropContext>
						</ul>
						<Modal
							isOpen={unsavedChangesModal.open}
							contentLabel={`Unsaved Changes`}
							portalClassName="atlas-content-modeler-unsaved-changes-modal-container"
							onRequestClose={() => {
								closeUnsavedChangesModal();
							}}
							style={customStyles}
							model={model}
						>
							<h2>Unsaved Changes</h2>
							<p>
								Would you like to discard your{" "}
								{unsavedChangesModal.field?.name}{" "}
								{unsavedChangesModal.field?.type} field updates?
							</p>
							<button
								className="first primary"
								onClick={() => closeUnsavedChangesModal()}
							>
								Continue Editing
							</button>
							<button
								className="tertiary"
								onClick={() => {
									const action = unsavedChangesModal?.field
										?.editing
										? "closeField"
										: "removeField";
									dispatch({
										type: action,
										model: id,
										id: unsavedChangesModal?.field?.id,
									});
									closeUnsavedChangesModal();
								}}
							>
								Discard Changes
							</button>
						</Modal>
					</>
				) : (
					<>
						<p className="field-list-info">
							Choose your first field for the {model?.name}{" "}
							content model:
						</p>
						<ul className="field-list">
							<li>
								<FieldButtons
									clickAction={(fieldType) => {
										dispatch({
											type: "addField",
											position: 0,
											model: id,
											fieldType,
										});
									}}
								/>
							</li>
						</ul>
					</>
				)}
			</section>
		</div>
	);
}
