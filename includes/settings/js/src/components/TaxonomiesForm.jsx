import React, { useContext, useState, useEffect } from "react";
import { useForm } from "react-hook-form";
import { __, sprintf } from "@wordpress/i18n";
import { ModelsContext } from "../ModelsContext";
import Icon from "../../../../components/icons";
import { showSuccess } from "../toasts";
import { useInputGenerator } from "../hooks";
import { toSanitizedKey } from "../formats";
import {
	Button,
	TertiaryButton,
} from "../../../../shared-assets/js/components/Buttons";

const { apiFetch } = wp;
const { isEqual, pick } = lodash;

const TaxonomiesForm = ({ editingTaxonomy, cancelEditing }) => {
	const { models, taxonomiesDispatch } = useContext(ModelsContext);
	const {
		register,
		handleSubmit,
		errors,
		setValue,
		setError,
		clearErrors,
		reset,
		formState: { isSubmitting },
	} = useForm({
		defaultValues: {
			api_visibility: "private",
			hierarchical: false,
		},
	});

	const SLUG_MAX_LENGTH = 32;

	const {
		setFieldsAreLinked,
		setInputGeneratorSourceValue,
		onChangeGeneratedValue,
	} = useInputGenerator({
		setGeneratedValue: (value) => setValue("slug", value),
		format: (key) => toSanitizedKey(key, SLUG_MAX_LENGTH),
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

	const [singularCount, setSingularCount] = useState(0);
	const [pluralCount, setPluralCount] = useState(0);

	/**
	 * Resets the form when cancelling edits.
	 */
	const resetForm = () => {
		reset();
		setInputGeneratorSourceValue("");
		setSingularCount(0);
		setPluralCount(0);
		setFieldsAreLinked(true);
	};

	/**
	 * Updates form values when editing a taxonomy.
	 */
	const setupEditForm = () => {
		clearErrors();
		setFieldsAreLinked(false);
		Object.entries(editingTaxonomy).forEach(([key, value]) =>
			setValue(key, value)
		);
		setSingularCount(editingTaxonomy?.singular.length);
		setPluralCount(editingTaxonomy?.plural.length);
	};

	const apiUpdateTaxonomy = (data) => {
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

		/**
		 * Resets the edit form if it was submitted without changes.
		 * Uses isEqual from lodash because isDirty from React Hook Form v6
		 * reports true in error when a checkbox value is toggled twice.
		 */
		if (editingTaxonomy) {
			const originalEditingValues = pick(
				editingTaxonomy,
				Object.keys(data) // Excludes properties not represented as form fields, such as `show_in_graphql`.
			);
			if (isEqual(originalEditingValues, data)) {
				window.scrollTo(0, 0);
				cancelEditing();
				return;
			}
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
				if (editingTaxonomy) {
					cancelEditing();
				} else {
					resetForm();
				}
				showSuccess(sprintf(successMessage, res.taxonomy.plural));
			})
			.catch((err) => {
				if (
					err.code === "acm_taxonomy_exists" ||
					err.code === "acm_reserved_taxonomy_term"
				) {
					setError("slug", {
						type: "invalidSlug",
						message: err.message,
					});
				}
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
	};

	useEffect(() => {
		if (editingTaxonomy === null) {
			resetForm();
		}
		if (editingTaxonomy) {
			setupEditForm();
		}
	}, [editingTaxonomy]);

	return (
		<form onSubmit={handleSubmit(apiUpdateTaxonomy)}>
			{/* Singular Name */}
			<div className={errors.singular ? "field has-error" : "field"}>
				<label htmlFor="singular">
					{__("Singular Name", "atlas-content-modeler")}
				</label>
				<br />
				<p className="help">
					{__(
						"Singular display name for your taxonomy.",
						"atlas-content-modeler"
					)}
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
						setInputGeneratorSourceValue(e.target.value);
						setSingularCount(e.target.value.length);
					}}
				/>
				<p className="field-messages">
					{errors.singular && errors.singular.type === "required" && (
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
					{errors.singular && errors.singular.type === "maxLength" && (
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
					{errors.singular && errors.singular.type === "exists" && (
						<span className="error">
							<Icon type="error" />
							<span role="alert">{errors.singular.message}</span>
						</span>
					)}
					<span>&nbsp;</span>
					<span className="count">{singularCount}/50</span>
				</p>
			</div>

			{/* Plural Name */}
			<div className={errors.plural ? "field has-error" : "field"}>
				<label htmlFor="plural">
					{__("Plural Name", "atlas-content-modeler")}
				</label>
				<br />
				<p className="help">
					{__(
						"Plural display name for your taxonomy.",
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
						setPluralCount(event.target.value.length);
					}}
				/>
				<p className="field-messages">
					{errors.plural && errors.plural.type === "required" && (
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
					{errors.plural && errors.plural.type === "maxLength" && (
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
							<span role="alert">{errors.plural.message}</span>
						</span>
					)}
					<span>&nbsp;</span>
					<span className="count">{pluralCount}/50</span>
				</p>
			</div>

			{/* Taxonomy ID / Slug */}
			<div className={errors.slug ? "field has-error" : "field"}>
				<label htmlFor="slug">
					{__("Taxonomy ID", "atlas-content-modeler")}
				</label>
				<br />
				<p className="help">
					{__(
						"Auto-generated and used internally for WordPress to identify the taxonomy.",
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
						maxLength: SLUG_MAX_LENGTH,
					})}
					onChange={(e) => {
						onChangeGeneratedValue(e.target.value);
					}}
				/>
				<p className="field-messages">
					{errors.slug && errors.slug.type === "required" && (
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
					{errors.slug && errors.slug.type === "maxLength" && (
						<span className="error">
							<Icon type="error" />
							<span role="alert">
								{sprintf(
									__(
										// Translators: the maximum character length.
										"Exceeds max length of %d",
										"atlas-content-modeler"
									),
									SLUG_MAX_LENGTH
								)}
							</span>
						</span>
					)}
					{errors.slug && errors.slug.type === "invalidSlug" && (
						<span className="error">
							<Icon type="error" />
							<span role="alert">{errors.slug.message}</span>
						</span>
					)}
					<span>&nbsp;</span>
				</p>
			</div>

			{/* Models / Types */}
			<div className={errors.types ? "field has-error" : "field"}>
				<fieldset id="model-checklist">
					<legend>{__("Models", "atlas-content-modeler")}</legend>
					<p className="help">
						{__(
							"The models to make this taxonomy available on.",
							"atlas-content-modeler"
						)}
					</p>
					{Object.values(models).map((model) => {
						return (
							<div className="checklist" key={model.slug}>
								<label className="checkbox">
									<input
										type="checkbox"
										value={model.slug}
										name="types"
										ref={register}
										onChange={() => clearErrors("types")}
									/>
									{model.plural}
								</label>
								<br />
							</div>
						);
					})}
				</fieldset>
				<p className="field-messages">
					{errors.types && errors.types.type === "noModelSet" && (
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
			<div className={errors.hierarchical ? "field has-error" : "field"}>
				<label htmlFor="hierarchical">
					{__("Hierarchical", "atlas-content-modeler")}
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
					<label htmlFor="hierarchical" className="checkbox">
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
				className={errors.api_visibility ? "field has-error" : "field"}
			>
				<label htmlFor="api_visibility">
					{__("API Visibility", "atlas-content-modeler")}
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
					<span>&nbsp;</span>
				</p>
			</div>
			{!editingTaxonomy && (
				<Button
					type="submit"
					disabled={isSubmitting}
					className="first"
					data-testid="create-taxonomy-button"
				>
					{__("Create", "atlas-content-modeler")}
				</Button>
			)}
			{editingTaxonomy && (
				<>
					<Button
						type="submit"
						disabled={isSubmitting}
						className="first"
						data-testid="update-taxonomy-button"
					>
						{__("Update", "atlas-content-modeler")}
					</Button>
					<TertiaryButton
						disabled={isSubmitting}
						data-testid="cancel-taxonomy-button"
						onClick={(e) => {
							e.preventDefault();
							scrollTo(0, 0);
							cancelEditing();
						}}
					>
						{__("Cancel", "atlas-content-modeler")}
					</TertiaryButton>
				</>
			)}
		</form>
	);
};

export default TaxonomiesForm;
