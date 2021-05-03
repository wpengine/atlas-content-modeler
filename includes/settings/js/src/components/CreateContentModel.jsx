import React, { useContext, useState } from "react";
import { useForm } from "react-hook-form";
import { Link, useHistory } from "react-router-dom";
import { ModelsContext } from "../ModelsContext";
import { insertSidebarMenuItem } from "../utils";
import { useApiIdGenerator } from "./fields/useApiIdGenerator";
import { showSuccess } from "../toasts";
import Icon from "./icons";

const { apiFetch } = wp;

export default function CreateContentModel() {
	const { register, handleSubmit, errors, setValue, setError } = useForm();
	const history = useHistory();
	const [singularCount, setSingularCount] = useState(0);
	const [pluralCount, setPluralCount] = useState(0);
	const [descriptionCount, setDescriptionCount] = useState(0);
	const { dispatch } = useContext(ModelsContext);
	const { setApiIdGeneratorInput, apiIdFieldAttributes } = useApiIdGenerator({
		apiFieldId: "postTypeSlug",
		setValue,
	});

	function apiCreateModel(data) {
		apiFetch({
			path: "/wpe/content-model",
			method: "POST",
			_wpnonce: wpApiSettings.nonce,
			data,
		})
			.then((res) => {
				if (res.success) {
					dispatch({ type: "addModel", data: res.model });
					history.push(
						"/wp-admin/admin.php?page=wpe-content-model&view=edit-model&id=" +
							data.postTypeSlug
					);

					// Insert the sidebar menu item below the Comments item, to avoid doing a full page refresh.
					insertSidebarMenuItem(res.model);

					window.scrollTo(0, 0);
					showSuccess(
						`The “${res.model.name}” model was created. Now add your first field.`
					);
				}
			})
			.catch((err) => {
				if (err.code === "wpe_content_model_already_exists") {
					setError("postTypeSlug", {
						type: "idExists",
						message: err.message,
					});
				}
			});
	}

	return (
		<div className="app-card">
			<section className="heading">
				<h2>New Content Model</h2>
				<button
					className="tertiary"
					onClick={() =>
						history.push(
							"/wp-admin/admin.php?page=wpe-content-model"
						)
					}
				>
					View All Models
				</button>
			</section>
			<section className="card-content">
				<form onSubmit={handleSubmit(apiCreateModel)}>
					<div
						className={
							errors.singular ? "field has-error" : "field"
						}
					>
						<label htmlFor="singular">Singular Name</label>
						<br />
						<p className="help">
							Singular display name for your content model, e.g.
							"Rabbit".
						</p>
						<input
							id="singular"
							name="singular"
							placeholder="Rabbit"
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

					<div
						className={errors.plural ? "field has-error" : "field"}
					>
						<label htmlFor="plural">Plural Name</label>
						<br />
						<p className="help">
							Plural display name for your content model, e.g.
							"Rabbits".
						</p>
						<input
							id="plural"
							name="plural"
							placeholder="Rabbits"
							ref={register({ required: true, maxLength: 50 })}
							onChange={(event) => {
								setApiIdGeneratorInput(event.target.value);
								setPluralCount(event.target.value.length);
							}}
						/>
						<p className="field-messages">
							{errors.plural &&
								errors.plural.type === "required" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											This field is required
										</span>
									</span>
								)}
							{errors.plural &&
								errors.plural.type === "maxLength" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											Exceeds max length.
										</span>
									</span>
								)}
							<span>&nbsp;</span>
							<span className="count">{pluralCount}/50</span>
						</p>
					</div>

					<div
						className={
							errors.postTypeSlug ? "field has-error" : "field"
						}
					>
						<label htmlFor="postTypeSlug">API Identifier</label>
						<br />
						<p className="help">
							Auto-generated from the plural name and used for API
							requests.
						</p>
						<input
							id="postTypeSlug"
							name="postTypeSlug"
							ref={register({ required: true, maxLength: 20 })}
							{...apiIdFieldAttributes}
						/>
						<p className="field-messages">
							{errors.postTypeSlug &&
								errors.postTypeSlug.type === "required" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											This field is required
										</span>
									</span>
								)}
							{errors.postTypeSlug &&
								errors.postTypeSlug.type === "maxLength" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											Exceeds max length of 20.
										</span>
									</span>
								)}
							{errors.postTypeSlug &&
								errors.postTypeSlug.type === "idExists" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											{errors.postTypeSlug.message}
										</span>
									</span>
								)}
							<span>&nbsp;</span>
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
						<br />
						<p className="help">
							A hint for content editors and API users.
						</p>
						<textarea
							id="description"
							name="description"
							ref={register({ maxLength: 250 })}
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
							<span className="count">
								{descriptionCount}/250
							</span>
						</p>
					</div>

					<button type="submit" className="primary first">
						Create
					</button>
					<button
						className="tertiary"
						onClick={() =>
							history.push(
								"/wp-admin/admin.php?page=wpe-content-model"
							)
						}
					>
						Cancel
					</button>
				</form>
			</section>
		</div>
	);
}
