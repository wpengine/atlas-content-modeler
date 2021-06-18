import React, { useContext, useState } from "react";
import { useForm } from "react-hook-form";
import { Link, useHistory } from "react-router-dom";
import { ModelsContext } from "../ModelsContext";
import { insertSidebarMenuItem } from "../utils";
import { useApiIdGenerator } from "./fields/useApiIdGenerator";
import { showSuccess } from "../toasts";
import Icon from "../../../../components/icons";

const { apiFetch } = wp;

export default function CreateContentModel() {
	const {
		register,
		handleSubmit,
		errors,
		setValue,
		setError,
		formState: { isSubmitting },
	} = useForm();
	const history = useHistory();
	const [singularCount, setSingularCount] = useState(0);
	const [pluralCount, setPluralCount] = useState(0);
	const [descriptionCount, setDescriptionCount] = useState(0);
	const { dispatch } = useContext(ModelsContext);
	const { setApiIdGeneratorInput, apiIdFieldAttributes } = useApiIdGenerator({
		apiFieldId: "slug",
		setValue,
	});

	function apiCreateModel(data) {
		return apiFetch({
			path: "/wpe/atlas/content-model",
			method: "POST",
			_wpnonce: wpApiSettings.nonce,
			data,
		})
			.then((res) => {
				if (res.success) {
					dispatch({ type: "addModel", data: res.model });
					history.push(
						atlasContentModeler.appPath +
							"&view=edit-model&id=" +
							data.slug
					);

					// Insert the sidebar menu item below the Comments item, to avoid doing a full page refresh.
					insertSidebarMenuItem(res.model);

					window.scrollTo(0, 0);
					showSuccess(
						`The “${res.model.plural}” model was created. Now add your first field.`
					);
				}
			})
			.catch((err) => {
				if (err.code === "atlas_content_modeler_already_exists") {
					setError("slug", {
						type: "idExists",
						message: err.message,
					});
				}
			});
	}

	return (
		<div className="app-card">
			<section className="heading flex-wrap d-flex flex-column d-sm-flex flex-sm-row">
				<h2>New Content Model</h2>
				<button
					className="tertiary"
					onClick={() => history.push(atlasContentModeler.appPath)}
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
							className="w-100"
							ref={register({ required: true, maxLength: 50 })}
							onChange={(e) => {
								setApiIdGeneratorInput(event.target.value);
								setSingularCount(e.target.value.length);
							}}
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

					<div className="field">
						<label htmlFor="modelIcon">Model Icon</label>
						<br />
						<input
							className="regular-text"
							id="modelIcon"
							type="text"
						/>
						<input
							className="primary first dashicons-picker"
							type="button"
							value="Choose Icon"
							data-target="#modelIcon"
						/>
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
							className="w-100"
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

					<div className={errors.slug ? "field has-error" : "field"}>
						<label htmlFor="slug">API Identifier</label>
						<br />
						<p className="help">
							Auto-generated from the plural name and used for API
							requests.
						</p>
						<input
							id="slug"
							name="slug"
							className="w-100"
							ref={register({ required: true, maxLength: 20 })}
							{...apiIdFieldAttributes}
						/>
						<p className="field-messages">
							{errors.slug && errors.slug.type === "required" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										This field is required
									</span>
								</span>
							)}
							{errors.slug && errors.slug.type === "maxLength" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										Exceeds max length of 20.
									</span>
								</span>
							)}
							{errors.slug && errors.slug.type === "idExists" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										{errors.slug.message}
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
							className="w-100"
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

					<button
						type="submit"
						disabled={isSubmitting}
						className="primary first"
					>
						Create
					</button>
					<button
						className="tertiary"
						disabled={isSubmitting}
						onClick={() =>
							history.push(atlasContentModeler.appPath)
						}
					>
						Cancel
					</button>
				</form>
			</section>
		</div>
	);
}
