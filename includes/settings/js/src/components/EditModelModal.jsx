import React, { useEffect, useContext, useState } from "react";
import { useForm } from "react-hook-form";
import Modal from "react-modal";
import { ModelsContext } from "../ModelsContext";
import Icon from "../../../../components/icons";
import IconPicker from "./IconPicker";
import { __ } from "@wordpress/i18n";
import { sendEvent } from "acm-analytics";
import { updateSidebarMenuItem, updateAdminMenuItem } from "../utils";
import {
	Button,
	TertiaryButton,
} from "../../../../shared-assets/js/components/Buttons";

const { apiFetch } = wp;

/**
 * The modal component for editing a content model.
 *
 * @param {Object} model The model to edit.
 * @param {Boolean} isOpen Whether or not the model is open.
 * @param {Function} setIsOpen - Callback for opening and closing modal.
 * @returns {JSX.Element} Modal
 */
export function EditModelModal({ model, isOpen, setIsOpen }) {
	if (typeof model?.singular === "undefined") {
		return ""; // Prevents a crash when a model is deleted from its own page.
	}

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
		setValue,
		setError,
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

		return apiFetch({
			path: `/wpe/atlas/content-model/${slug}`,
			method: "PATCH",
			_wpnonce: wpApiSettings.nonce,
			data,
		})
			.then((res) => {
				sendEvent({
					category: "Models",
					action: "Model Updated",
				});
				return res;
			})
			.catch((err) => {
				if (err.code === "acm_singular_label_exists") {
					setError("singular", {
						type: "exists",
						message: err.message,
					});
				}
				if (err.code === "acm_plural_label_exists") {
					setError("plural", {
						type: "exists",
						message: err.message,
					});
				}
			});
	}

	useEffect(() => {
		Modal.setAppElement("#root");
	}, []);

	return (
		<Modal
			isOpen={isOpen}
			contentLabel={`Editing the ${model.plural} content model`}
			parentSelector={() => {
				return document.getElementById("root");
			}}
			portalClassName="atlas-content-modeler-edit-model-modal-container atlas-content-modeler"
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
					const result = await updateModel(data.slug, mergedData);
					if (result?.success) {
						updateSidebarMenuItem(model, data);
						updateAdminMenuItem(model, data);
						dispatch({ type: "updateModel", data: mergedData });
						setIsOpen(false);
					}
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
							{errors.singular &&
								errors.singular.type === "exists" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											{errors.singular.message}
										</span>
									</span>
								)}
							<span>&nbsp;</span>
							<span className="count">{singularCount}/50</span>
						</p>
					</div>

					<div
						className={
							errors.plural
								? "field has-error col-sm"
								: "field col-sm"
						}
					>
						<label htmlFor="plural">Plural Name</label>
						<p className="help">
							{__(
								'Plural display name for your content model, e.g. "Rabbits".',
								"atlas-content-modeler"
							)}
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
							{errors.plural &&
								errors.plural.type === "required" && (
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
							{errors.plural &&
								errors.plural.type === "maxLength" && (
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
							{errors.plural && errors.plural.type === "exists" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										{errors.plural.message}
									</span>
								</span>
							)}
							<span>&nbsp;</span>
							<span className="count">{pluralCount}/50</span>
						</p>
					</div>
				</div>

				<div className="row">
					<div className="field col-sm">
						<label htmlFor="slug">
							{__("Model ID", "atlas-content-modeler")}
						</label>
						<p className="help">
							{__(
								"Auto-generated and used internally for WordPress to identify the model.",
								"atlas-content-modeler"
							)}
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
								? "field has-error form-check form-check-inline col-sm"
								: "field form-check form-check-inline col-sm"
						}
					>
						<label htmlFor="api_visibility">API Visibility</label>
						<p className="help">
							{__(
								"Whether or not this model requires authentication to be accessed via REST and GraphQL APIs.",
								"atlas-content-modeler"
							)}
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
							{__("Public", "atlas-content-modeler")}
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
							{__("Private", "atlas-content-modeler")}
						</label>
						<br />

						<p className="field-messages">
							{errors.api_visibility &&
								errors.api_visibility.type === "required" && (
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
						</p>
					</div>
				</div>

				<div className="row">
					<div className="field form-check form-check-inline col-sm">
						<label htmlFor="has_archive">Post Type Archive</label>
						<p className="help">
							{__(
								"Whether there should be a post type archive. (Sets “has_archive”.)",
								"atlas-content-modeler"
							)}
						</p>

						<input
							name="has_archive"
							id="has_archive"
							type="checkbox"
							value="1"
							ref={register}
							defaultChecked={model?.has_archive ?? false}
						/>

						<label
							htmlFor="has_archive"
							className="form-check-label"
						>
							{__(
								"Enable post type archive",
								"atlas-content-modeler"
							)}
						</label>
					</div>
					<div
						className={
							errors.with_front
								? "field has-error form-check form-check-inline col-sm"
								: "field form-check form-check-inline col-sm"
						}
					>
						<label htmlFor="with_front">Use Permalink Base</label>
						<p className="help">
							{__(
								"Post URLs will include prefixes from Settings → Permalinks if this is ticked. (Sets “with_front”.)",
								"atlas-content-modeler"
							)}
						</p>

						<input
							name="with_front"
							id="with_front"
							type="checkbox"
							value="1"
							ref={register}
							defaultChecked={model?.with_front ?? true}
						/>

						<label
							htmlFor="with_front"
							className="form-check-label"
						>
							{__(
								"Use front permalink base",
								"atlas-content-modeler"
							)}
						</label>
					</div>
				</div>

				<div className="row">
					<div className="field col-sm">
						<label htmlFor="model_icon">
							{__("Model Icon", "atlas-content-modeler")}
						</label>
						<p className="help">
							{__(
								"Choose an icon to represent your model.",
								"atlas-content-modeler"
							)}
						</p>

						<IconPicker
							setValue={setValue}
							buttonClasses="primary first"
							register={register}
							modelIcon={model.model_icon}
						/>
					</div>
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
						{__(
							"A hint for content editors and API users.",
							"atlas-content-modeler"
						)}
					</p>
					<textarea
						id="description"
						name="description"
						className="w-100"
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
										{__(
											"Exceeds max length.",
											"atlas-content-modeler"
										)}
									</span>
								</span>
							)}
						<span>&nbsp;</span>
						<span className="count">{descriptionCount}/250</span>
					</p>
				</div>

				<Button
					type="submit"
					disabled={isSubmitting}
					className="first"
					data-testid="edit-model-save-button"
				>
					{__("Save", "atlas-content-modeler")}
				</Button>
				<TertiaryButton
					href="#"
					disabled={isSubmitting}
					data-testid="edit-model-cancel-button"
					onClick={(event) => {
						event.preventDefault();
						setIsOpen(false);
					}}
				>
					{__("Cancel", "atlas-content-modeler")}
				</TertiaryButton>
			</form>
		</Modal>
	);
}
