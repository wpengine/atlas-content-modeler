import React, { useContext, useState, useRef } from "react";
import { useHistory } from "react-router-dom";
import { DragDropContext, Droppable } from "react-beautiful-dnd";
import { useLocationSearch } from "../utils";
import { onDragEnd } from "./fields/eventHandlers";
import Field from "./fields/Field";
import { ModelsContext } from "../ModelsContext";
import { ContentModelDropdown } from "./ContentModelDropdown";
import Modal from "react-modal";
import { sprintf, __ } from "@wordpress/i18n";

import {
	getFieldOrder,
	getPositionAfter,
	sanitizeFields,
	getOpenField,
} from "../queries";
import FieldButtons from "./FieldButtons";
import {
	Button,
	TertiaryButton,
} from "../../../../shared-assets/js/components/Buttons";
import { Card } from "../../../../shared-assets/js/components/card";

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
	const hasDirtyField = useRef(false);

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
		if (Object.keys(openField).length > 0 && hasDirtyField.current) {
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

	return (
		<Card>
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
						{__("Content Models", "atlas-content-modeler")}
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
														hasDirtyField={
															hasDirtyField
														}
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
							contentLabel="Unsaved Changes"
							portalClassName="atlas-content-modeler-unsaved-changes-modal-container"
							onRequestClose={closeUnsavedChangesModal}
							style={customStyles}
							model={model}
						>
							<h2>
								{__("Unsaved Changes", "atlas-content-modeler")}
							</h2>
							<p>
								{__(
									"Would you like to discard your field updates?",
									"atlas-content-modeler"
								)}
							</p>
							<Button
								className="first"
								data-testid="model-field-continue-editing-button"
								onClick={closeUnsavedChangesModal}
							>
								{__(
									"Continue Editing",
									"atlas-content-modeler"
								)}
							</Button>
							<TertiaryButton
								className="tertiary"
								data-testid="model-field-continue-discard-button"
								onClick={() => {
									const action = unsavedChangesModal?.field
										?.editing
										? "closeField"
										: "removeField";
									hasDirtyField.current = false;
									dispatch({
										type: action,
										model: id,
										id: unsavedChangesModal?.field?.id,
									});
									closeUnsavedChangesModal();
								}}
							>
								{__("Discard Changes", "atlas-content-modeler")}
							</TertiaryButton>
						</Modal>
					</>
				) : (
					<>
						<p className="field-list-info">
							{sprintf(
								__(
									"Choose your first field for the %s content model:",
									"atlas-content-modeler"
								),
								model?.name ? model.name : ""
							)}
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
		</Card>
	);
}
