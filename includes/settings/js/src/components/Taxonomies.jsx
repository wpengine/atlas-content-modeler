import React, { useContext, useEffect, useState } from "react";
import { useHistory } from "react-router-dom";
import { __, sprintf } from "@wordpress/i18n";
import { ModelsContext } from "../ModelsContext";
import { useForm } from "react-hook-form";
import Icon from "../../../../components/icons";
import { useApiIdGenerator } from "./fields/useApiIdGenerator";
import { showSuccess } from "../toasts";
import TaxonomiesTable from "./TaxonomiesTable";

const { apiFetch } = wp;

export default function Taxonomies() {
	const { models, taxonomies, taxonomiesDispatch } = useContext(
		ModelsContext
	);
	const history = useHistory();
	const [editingTaxonomy, setEditingTaxonomy] = useState(null);
	const {
		register,
		handleSubmit,
		errors,
		setValue,
		setError,
		clearErrors,
		formState,
		reset,
		formState: { isSubmitting },
	} = useForm({
		defaultValues: {
			api_visibility: "private",
			hierarchical: false,
		},
	});
	const { isDirty } = formState;
	const [singularCount, setSingularCount] = useState(0);
	const [pluralCount, setPluralCount] = useState(0);
	const {
		setApiIdGeneratorInput,
		apiIdFieldAttributes,
		setFieldsAreLinked,
	} = useApiIdGenerator({
		apiFieldId: "slug",
		setValue,
	});
	const successMessage = editingTaxonomy
		? __(
				/* translators: the taxonomy plural name */
				'The "%s" taxonomy was updated.',
				"atlas-content-modeler"
		  )
		: __(
				/* translators: the taxonomy plural name */
				'The "%s" taxonomy was created.',
				"atlas-content-modeler"
		  );

	function apiCreateTaxonomy(data) {
		// Wrap single models as an array.
		if (typeof data.types === "string") {
			data.types = [data.types];
		}

		// Check that at least one model was ticked.
		if (
			typeof data.types === "boolean" ||
			(typeof data.types === "object" && data.types.length < 1)
		) {
			setError("types", {
				type: "noModelSet",
			});
			return false;
		}

		if (editingTaxonomy && !isDirty) {
			window.scrollTo(0, 0);
			cancelEditing();
			return;
		}

		return apiFetch({
			path: "/wpe/atlas/taxonomy",
			method: editingTaxonomy ? "PUT" : "POST",
			_wpnonce: wpApiSettings.nonce,
			data,
		})
			.then((res) => {
				if (!res.success) {
					return;
				}

				taxonomiesDispatch({
					type: "updateTaxonomy",
					data: res.taxonomy,
				});

				window.scrollTo(0, 0);
				editingTaxonomy ? cancelEditing() : resetForm();
				showSuccess(sprintf(successMessage, res.taxonomy.plural));
			})
			.catch((err) => {
				if (err.code === "atlas_content_modeler_taxonomy_exists") {
					setError("slug", {
						type: "idExists",
						message: err.message,
					});
				}
			});
	}

	const resetForm = () => {
		reset();
		setApiIdGeneratorInput("");
		setSingularCount(0);
		setPluralCount(0);
		setFieldsAreLinked(true);
	};

	const cancelEditing = () => {
		setEditingTaxonomy(null);
		resetForm();
		history.push(atlasContentModeler.appPath + "&view=taxonomies");
	};

	// Update form values when editing a taxonomy.
	useEffect(() => {
		if (editingTaxonomy) {
			clearErrors();
			setFieldsAreLinked(false);
			Object.entries(editingTaxonomy).forEach(([key, value]) =>
				setValue(key, value)
			);
			setSingularCount(editingTaxonomy?.singular.length);
			setPluralCount(editingTaxonomy?.plural.length);
		}
	}, [editingTaxonomy]);

	return (
		<div className="app-card taxonomies-view">
			<section className="heading flex-wrap d-flex flex-column d-sm-flex flex-sm-row">
				<h2>
					{editingTaxonomy ? (
						<>
							<a
								href="#"
								onClick={(event) => {
									event.preventDefault();
									cancelEditing();
								}}
							>
								{__("Taxonomies", "atlas-content-modeler")}
							</a>
							{" / "}
							{editingTaxonomy.plural}
						</>
					) : (
						__("Taxonomies", "atlas-content-modeler")
					)}
				</h2>
				{editingTaxonomy ? (
					<button
						className="tertiary"
						onClick={(event) => {
							event.preventDefault();
							cancelEditing();
						}}
					>
						{__("Cancel Editing", "atlas-content-modeler")}
					</button>
				) : (
					<button
						className="tertiary"
						onClick={() =>
							history.push(atlasContentModeler.appPath)
						}
					>
						{__("View Content Models", "atlas-content-modeler")}
					</button>
				)}
			</section>
			<section className="card-content">
				<div className="row">
					<div className="col-xs-10 col-lg-4 order-1 order-lg-0">
						<h3>
							{editingTaxonomy
								? sprintf(
										__(
											"Editing “%s”",
											"atlas-content-modeler"
										),
										editingTaxonomy.plural
								  )
								: __("Add New", "atlas-content-modeler")}
						</h3>
						<form onSubmit={handleSubmit(apiCreateTaxonomy)}>
							{/* Singular Name */}
							<div
								className={
									errors.singular
										? "field has-error"
										: "field"
								}
							>
								<label htmlFor="singular">
									{__(
										"Singular Name",
										"atlas-content-modeler"
									)}
								</label>
								<br />
								<p className="help">
									{__(
										'Singular display name for your taxonomy, e.g. "Ingredient"',
										"atlas-content-modeler"
									)}
									.
								</p>
								<input
									id="singular"
									name="singular"
									placeholder="Ingredient"
									className="w-100"
									ref={register({
										required: true,
										maxLength: 50,
									})}
									onChange={(e) => {
										setApiIdGeneratorInput(e.target.value);
										setSingularCount(e.target.value.length);
									}}
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
										errors.singular.type ===
											"maxLength" && (
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
									<span className="count">
										{singularCount}/50
									</span>
								</p>
							</div>

							{/* Plural Name */}
							<div
								className={
									errors.plural ? "field has-error" : "field"
								}
							>
								<label htmlFor="plural">
									{__("Plural Name", "atlas-content-modeler")}
								</label>
								<br />
								<p className="help">
									{__(
										'Plural display name for your taxonomy, e.g. "Ingredients".',
										"atlas-content-modeler"
									)}
								</p>
								<input
									id="plural"
									name="plural"
									placeholder="Ingredients"
									className="w-100"
									ref={register({
										required: true,
										maxLength: 50,
									})}
									onChange={(event) => {
										setPluralCount(
											event.target.value.length
										);
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
									<span>&nbsp;</span>
									<span className="count">
										{pluralCount}/50
									</span>
								</p>
							</div>

							{/* API Identifier / Slug */}
							<div
								className={
									errors.slug ? "field has-error" : "field"
								}
							>
								<label htmlFor="slug">
									{__(
										"API Identifier",
										"atlas-content-modeler"
									)}
								</label>
								<br />
								<p className="help">
									{__(
										"Auto-generated from the singular name and used for API requests.",
										"atlas-content-modeler"
									)}
								</p>
								<input
									id="slug"
									name="slug"
									className="w-100"
									readOnly={editingTaxonomy !== null}
									ref={register({
										required: true,
										maxLength: 32,
									})}
									{...apiIdFieldAttributes}
								/>
								<p className="field-messages">
									{errors.slug &&
										errors.slug.type === "required" && (
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
									{errors.slug &&
										errors.slug.type === "maxLength" && (
											<span className="error">
												<Icon type="error" />
												<span role="alert">
													{__(
														"Exceeds max length of 32",
														"atlas-content-modeler"
													)}
												</span>
											</span>
										)}
									{errors.slug &&
										errors.slug.type === "idExists" && (
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

							{/* Models / Types */}
							<div
								className={
									errors.types ? "field has-error" : "field"
								}
							>
								<fieldset>
									<legend>
										{__("Models", "atlas-content-modeler")}
									</legend>
									<p className="help">
										{__(
											"The models to make this taxonomy available on.",
											"atlas-content-modeler"
										)}
									</p>
									{Object.values(models).map((model) => {
										return (
											<div
												className="checklist"
												key={model.slug}
											>
												<label className="checkbox">
													<input
														type="checkbox"
														value={model.slug}
														name="types"
														ref={register}
														onChange={() =>
															clearErrors("types")
														}
													/>
													{model.plural}
												</label>
												<br />
											</div>
										);
									})}
								</fieldset>
								<p className="field-messages">
									{errors.types &&
										errors.types.type === "noModelSet" && (
											<span className="error">
												<Icon type="error" />
												<span role="alert">
													{__(
														"Please choose at least one model.",
														"atlas-content-modeler"
													)}
												</span>
											</span>
										)}
								</p>
							</div>

							{/* Hierarchical */}
							<div
								className={
									errors.hierarchical
										? "field has-error"
										: "field"
								}
							>
								<label htmlFor="hierarchical">
									{__(
										"Hierarchical",
										"atlas-content-modeler"
									)}
								</label>
								<br />
								<p>
									<input
										name="hierarchical"
										disabled={editingTaxonomy !== null}
										id="hierarchical"
										type="checkbox"
										ref={register()}
									/>
									<label
										htmlFor="hierarchical"
										className="checkbox"
									>
										{__(
											"Terms can have parent terms",
											"atlas-content-modeler"
										)}
									</label>
								</p>
								<p className="help">
									{__(
										"Enable to allow taxonomy terms to have parents, like WordPress categories. Disable if terms will not have parents, like WordPress tags.",
										"atlas-content-modeler"
									)}
								</p>
							</div>

							{/* API Visibility */}
							<div
								className={
									errors.api_visibility
										? "field has-error"
										: "field"
								}
							>
								<label htmlFor="api_visibility">
									{__(
										"API Visibility",
										"atlas-content-modeler"
									)}
								</label>
								<br />
								<p className="help">
									{__(
										"Whether or not this taxonomy requires authentication to be accessed via REST and GraphQL APIs.",
										"atlas-content-modeler"
									)}
								</p>

								<input
									id="api_visibility_public"
									name="api_visibility"
									type="radio"
									value="public"
									ref={register({ required: true })}
								/>
								<label htmlFor="api_visibility_public">
									{__("Public", "atlas-content-modeler")}
								</label>
								<p className="help">
									{__(
										"No authentication is needed for REST and GraphQL.",
										"atlas-content-modeler"
									)}
								</p>

								<input
									id="api_visibility_private"
									name="api_visibility"
									type="radio"
									value="private"
									ref={register({ required: true })}
								/>
								<label htmlFor="api_visibility_private">
									{__("Private", "atlas-content-modeler")}
								</label>
								<p className="help">
									{__(
										"REST and GraphQL requests require authentication.",
										"atlas-content-modeler"
									)}
								</p>

								<p className="field-messages">
									{errors.api_visibility &&
										errors.api_visibility.type ===
											"required" && (
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
									<span>&nbsp;</span>
								</p>
							</div>
							{!editingTaxonomy && (
								<button
									type="submit"
									disabled={isSubmitting}
									className="primary first"
								>
									{__("Create", "atlas-content-modeler")}
								</button>
							)}
							{editingTaxonomy && (
								<>
									<button
										type="submit"
										disabled={isSubmitting}
										className="primary first"
									>
										{__("Update", "atlas-content-modeler")}
									</button>
									<button
										disabled={isSubmitting}
										className="tertiary"
										onClick={(e) => {
											e.preventDefault();
											scrollTo(0, 0);
											cancelEditing();
										}}
									>
										{__("Cancel", "atlas-content-modeler")}
									</button>
								</>
							)}
						</form>
					</div>
					{!editingTaxonomy && (
						<div className="taxonomy-list col-xs-10 col-lg-8 order-0 order-lg-1">
							<TaxonomiesTable
								taxonomies={taxonomies}
								setEditingTaxonomy={setEditingTaxonomy}
							/>
						</div>
					)}
				</div>
			</section>
		</div>
	);
}
