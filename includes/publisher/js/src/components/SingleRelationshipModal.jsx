import React, { useContext, useState } from "react";
import Icon from "../../../../components/icons";
import { sprintf, __ } from "@wordpress/i18n";
import { ModelsContext } from "../../../../settings/js/src/ModelsContext";
import { useForm } from "react-hook-form";
import Modal from "react-modal";
import IconPicker from "../../../../settings/js/src/components/IconPicker";
const { wp } = window;

/**
 * The modal component for editing a single relationship.
 *
 * @param {Object} model The model to edit.
 * @param {Boolean} isOpen Whether or not the model is open.
 * @param {Function} setIsOpen - Callback for opening and closing modal.
 * @returns {JSX.Element} Modal
 */
export default function SingleRelationshipModal({ model, isOpen, setIsOpen }) {
	const [singularCount, setSingularCount] = useState(0);
	const [pluralCount, setPluralCount] = useState(0);
	const [descriptionCount, setDescriptionCount] = useState(0);
	// const { dispatch } = useContext(ModelsContext);
	const {
		register,
		handleSubmit,
		errors,
		setValue,
		formState: { isSubmitting },
	} = useForm();

	const customStyles = {
		overlay: {
			backgroundColor: "rgba(0, 40, 56, 0.7)",
		},
		content: {
			top: "50%",
			left: "50%",
			right: "auto",
			bottom: "auto",
			transform: "translate(-50%, -50%)",
			border: "none",
			padding: "40px",
			boxSizing: "border-box",
		},
	};

	return (
		<Modal
			isOpen={isOpen}
			contentLabel={`Editing the ${model?.plural} content model`}
			parentSelector={() => {
				return document.getElementById(
					"atlas-content-modeler-fields-app"
				);
			}}
			portalClassName="atlas-content-modeler-edit-model-modal-container atlas-content-modeler"
			onRequestClose={() => {
				setIsOpen(false);
			}}
			model={model}
			style={customStyles}
		>
			<h2>Edit {model?.plural}</h2>
			<form
				onSubmit={handleSubmit(async (data) => {
					const mergedData = { ...model, ...data };
					await updateModel(data.slug, mergedData);
					dispatch({ type: "updateModel", data: mergedData });
					updateSidebarMenuItem(model, data);
					setIsOpen(false);
				})}
			>
				<div className="row">
					<div
						className={
							errors.singular
								? "field has-error col-sm"
								: "field col-sm"
						}
					>
						<label htmlFor="singular">Singular Name</label>
						<p className="help">
							{__(
								'Singular display name for your content model, e.g. "Rabbit".',
								"atlas-content-modeler"
							)}
						</p>
						<input
							id="singular"
							name="singular"
							placeholder="Rabbit"
							defaultValue={model?.singular}
							ref={register({ required: true, maxLength: 50 })}
							onChange={(e) =>
								setSingularCount(e.target.value.length)
							}
						/>
						<p className="field-messages">
							{errors.singular &&
								errors.singular.type === "required" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											{__(
												"This field is required",
												"atlas-content-modeler"
											)}
										</span>
									</span>
								)}
							{errors.singular &&
								errors.singular.type === "maxLength" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											{__(
												"Exceeds max length.",
												"atlas-content-modeler"
											)}
										</span>
									</span>
								)}
							<span>&nbsp;</span>
							<span className="count">{singularCount}/50</span>
						</p>
					</div>
				</div>

				<button
					type="submit"
					disabled={isSubmitting}
					className="primary first"
				>
					{__("Save", "atlas-content-modeler")}
				</button>
				<button
					href="#"
					className="tertiary"
					disabled={isSubmitting}
					onClick={(event) => {
						event.preventDefault();
						setIsOpen(false);
					}}
				>
					{__("Cancel", "atlas-content-modeler")}
				</button>
			</form>
		</Modal>
	);
}
