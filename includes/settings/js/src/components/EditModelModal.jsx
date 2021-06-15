import React, { useContext, useState } from "react";
import { useForm } from "react-hook-form";
import Modal from "react-modal";
import { ModelsContext } from "../ModelsContext";
import Icon from "../../../../components/icons";

const { apiFetch } = wp;

/**
 * Updates a model via the REST API.
 *
 * @param slug Model slug.
 * @param data Model data.
 */
function updateModel(slug = "", data = {}) {
	if (!slug.length || Object.keys(data).length === 0) {
		return;
	}

	const updated = apiFetch({
		path: `/wpe/atlas/content-model/${slug}`,
		method: "PATCH",
		_wpnonce: wpApiSettings.nonce,
		data,
	}).then((res) => {
		return res;
	});

	return updated;
}

/**
 * The modal component for editing a content model.
 *
 * @param {Object} model The model to edit.
 * @param {Boolean} isOpen Whether or not the model is open.
 * @param {Function} setIsOpen - Callback for opening and closing modal.
 * @returns {JSX.Element} Modal
 */
export function EditModelModal({ model, isOpen, setIsOpen }) {
	const [singularCount, setSingularCount] = useState(model.singular.length);
	const [pluralCount, setPluralCount] = useState(model.plural.length);
	const [descriptionCount, setDescriptionCount] = useState(
		model.description.length
	);
	const { dispatch } = useContext(ModelsContext);
	const {
		register,
		handleSubmit,
		errors,
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
			contentLabel={`Editing the ${model.plural} content model`}
			parentSelector={() => {
				return document.getElementById("root");
			}}
			portalClassName="atlas-content-modeler-edit-model-modal-container"
			onRequestClose={() => {
				setIsOpen(false);
			}}
			model={model}
			style={customStyles}
		>
			<h2>Edit {model.plural}</h2>
			<form
				onSubmit={handleSubmit(async (data) => {
					const mergedData = { ...model, ...data };
					await updateModel(data.slug, mergedData);
					dispatch({ type: "updateModel", data: mergedData });
					setIsOpen(false);
				})}
			>
				<div className={errors.singular ? "field has-error" : "field"}>
					<label htmlFor="singular">Singular Name</label>
					<p className="help">
						Singular display name for your content model, e.g.
						"Rabbit".
					</p>
					<input
						id="singular"
						name="singular"
						placeholder="Rabbit"
						defaultValue={model.singular}
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
										This field is required
									</span>
								</span>
							)}
						{errors.singular &&
							errors.singular.type === "maxLength" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										Exceeds max length.
									</span>
								</span>
							)}
						<span>&nbsp;</span>
						<span className="count">{singularCount}/50</span>
					</p>
				</div>

				<div className={errors.plural ? "field has-error" : "field"}>
					<label htmlFor="plural">Plural Name</label>
					<p className="help">
						Plural display name for your content model, e.g.
						"Rabbits".
					</p>
					<input
						id="plural"
						name="plural"
						defaultValue={model.plural}
						placeholder="Rabbits"
						ref={register({ required: true, maxLength: 50 })}
						onChange={(event) => {
							setPluralCount(event.target.value.length);
						}}
					/>
					<p className="field-messages">
						{errors.plural && errors.plural.type === "required" && (
							<span className="error">
								<Icon type="error" />
								<span role="alert">This field is required</span>
							</span>
						)}
						{errors.plural && errors.plural.type === "maxLength" && (
							<span className="error">
								<Icon type="error" />
								<span role="alert">Exceeds max length.</span>
							</span>
						)}
						<span>&nbsp;</span>
						<span className="count">{pluralCount}/50</span>
					</p>
				</div>

				<div className="field">
					<label htmlFor="slug">API Identifier</label>
					<p className="help">
						Auto-generated and used for API requests.
					</p>
					<input
						id="slug"
						name="slug"
						ref={register({ required: true, maxLength: 20 })}
						defaultValue={model.slug}
						readOnly="readOnly"
					/>
					<p className="field-messages">
						<span>&nbsp;</span>
					</p>
				</div>

				<div
					className={
						errors.api_visibility
							? "field has-error form-check form-check-inline"
							: "field form-check form-check-inline"
					}
				>
					<label htmlFor="api_visibility">API Visibility</label>
					<br />
					<p className="help">
						Whether or not this model requires authentication to be
						accessed via REST and GraphQL APIs.
					</p>

					<input
						type="radio"
						id="api_visibility_public"
						name="api_visibility"
						value="public"
						className="form-check-input"
						defaultChecked={model?.api_visibility === "public"}
						ref={register({ required: true })}
					/>
					<label
						htmlFor="api_visibility_public"
						className="form-check-label"
					>
						Public
					</label>
					<br />

					<input
						type="radio"
						id="api_visibility_private"
						name="api_visibility"
						value="private"
						className="form-check-input"
						defaultChecked={
							model?.api_visibility === "private" ||
							typeof model?.api_visibility === "undefined"
						}
						ref={register({ required: true })}
					/>
					<label
						htmlFor="api_visibility_private"
						className="form-check-label"
					>
						Private
					</label>
					<br />

					<p className="field-messages">
						{errors.api_visibility &&
							errors.api_visibility.type === "required" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										This field is required
									</span>
								</span>
							)}
					</p>
				</div>

				<div
					className={
						errors.description
							? "field field-description has-error"
							: "field field-description"
					}
				>
					<label htmlFor="description">Description</label>
					<p className="help">
						A hint for content editors and API users.
					</p>
					<textarea
						id="description"
						name="description"
						ref={register({ maxLength: 250 })}
						defaultValue={model.description}
						onChange={(e) =>
							setDescriptionCount(e.target.value.length)
						}
					/>
					<p className="field-messages">
						{errors.description &&
							errors.description.type === "maxLength" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										Exceeds max length.
									</span>
								</span>
							)}
						<span>&nbsp;</span>
						<span className="count">{descriptionCount}/250</span>
					</p>
				</div>

				<button
					type="submit"
					disabled={isSubmitting}
					className="primary first"
				>
					Save
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
					Cancel
				</button>
			</form>
		</Modal>
	);
}
