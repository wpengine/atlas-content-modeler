/* global atlasContentModeler */
import React, { useState, useContext, useRef, useEffect } from "react";
import { useForm } from "react-hook-form";
import Modal from "react-modal";
import { useLocationSearch } from "../../utils";
import Icon from "acm-icons";
import TextFields from "./TextFields";
import RichTextFields from "./RichTextFields";
import EmailFields from "./EmailFields";
import {
	MediaSettings,
	TextSettings,
	NumberSettings,
	EmailSettings,
} from "./AdvancedSettings";
import NumberFields from "./NumberFields";
import DateFields from "./DateFields";
import MediaFields from "./MediaFields";
import MultipleChoiceFields from "./MultipleChoiceFields";
import RelationshipFields from "./RelationshipFields";
import supportedFields from "./supportedFields";
import { ModelsContext } from "../../ModelsContext";
import { __ } from "@wordpress/i18n";
import { sendEvent } from "acm-analytics";
import { useInputGenerator } from "../../hooks";
import { toValidApiId } from "../../formats";
import { getFeaturedFieldId } from "../../queries";
import {
	Button,
	LinkButton,
	TertiaryButton,
} from "../../../../../shared-assets/js/components/Buttons";

const { apiFetch } = wp;
const { cloneDeep, isEqual } = lodash;

const extraFields = {
	text: TextFields,
	media: MediaFields,
	richtext: RichTextFields,
	email: EmailFields,
	number: NumberFields,
	date: DateFields,
	multipleChoice: MultipleChoiceFields,
	relationship: RelationshipFields,
};

Modal.setAppElement("#root");

function Form({ id, position, type, editing, storedData, hasDirtyField }) {
	const {
		register,
		handleSubmit,
		errors,
		setValue,
		getValues,
		clearErrors,
		control,
		setError,
		reset,
		trigger,
		watch,
	} = useForm({
		mode: "onChange",
		defaultValues: storedData,
	});

	const [nameCount, setNameCount] = useState(storedData?.name?.length || 0);
	const [descriptionCount, setDescriptionCount] = useState(
		storedData?.description?.length || 0
	);
	const [optionsModalIsOpen, setOptionsModalIsOpen] = useState(false);
	const { models, dispatch } = useContext(ModelsContext);
	const query = useLocationSearch();
	const model = query.get("id");
	const ExtraFields = extraFields[type] ?? null;
	const currentNumberType = watch("numberType");
	const isRepeatableMedia = watch("isRepeatableMedia");
	const {
		setInputGeneratorSourceValue,
		onChangeGeneratedValue,
	} = useInputGenerator({
		linked: !editing,
		setGeneratedValue: (value) => setValue("slug", value),
		format: toValidApiId,
	});
	const originalState = useRef(cloneDeep(models[model]["fields"] || {}));
	const [previousState, setPreviousState] = useState(storedData);
	const originalValues = useRef({});
	const isAppropriateType = (value) => {
		if (getValues("numberType") === "integer") {
			const disallowedCharacters = /[.]/g;
			return !disallowedCharacters.test(value);
		}
	};

	const advancedSettings = {
		text: {
			component: TextSettings,
			fields: {
				minChars: {
					min: 0,
					setValueAs: (v) => (v ? parseInt(v) : ""),
				},
				maxChars: {
					min: 1,
					setValueAs: (v) => (v ? parseInt(v) : ""),
					validate: {
						maxBelowMin: (v) => {
							const min = parseInt(getValues("minChars"));
							const max = parseInt(v);
							if (isNaN(min) || isNaN(max)) {
								return true;
							}
							return max > min;
						},
					},
				},
				minRepeatable: {
					min: 0,
					setValueAs: (v) => (v ? parseInt(v) : ""),
				},
				maxRepeatable: {
					min: 1,
					setValueAs: (v) => (v ? parseInt(v) : ""),
					validate: {
						maxBelowMin: (v) => {
							const min = parseInt(getValues("minRepeatable"));
							const max = parseInt(v);
							if (isNaN(min) || isNaN(max)) {
								return true;
							}
							return max >= min;
						},
					},
				},
			},
		},
		email: {
			component: EmailSettings,
			fields: {
				allowedDomains: {
					setValueAs: (value) =>
						value ? value.replace(/\s/g, "") : "",
					validate: {
						formattedCorrectly: (value) => {
							if (!value) {
								return true;
							}

							const domains = value
								.split(",")
								.map((domain) => domain.trim())
								.filter((domain) => domain.length > 0);
							const wildcardDomainRegex = /[A-Za-z0-9-*.]*\.[A-Za-z]+$/;
							const isValidDomain = (domain) => {
								return wildcardDomainRegex.test(domain);
							};

							return domains.every(isValidDomain);
						},
					},
				},
				minRepeatable: {
					min: 0,
					setValueAs: (v) => (v ? parseInt(v) : ""),
				},
				maxRepeatable: {
					min: 1,
					setValueAs: (v) => (v ? parseInt(v) : ""),
					validate: {
						maxBelowMin: (v) => {
							const min = parseInt(getValues("minRepeatable"));
							const max = parseInt(v);
							if (isNaN(min) || isNaN(max)) {
								return true;
							}
							return max >= min;
						},
					},
				},
				exactRepeatable: {
					min: 1,
					setValueAs: (v) => (v ? parseInt(v) : ""),
				},
			},
		},
		number: {
			component: NumberSettings,
			fields: {
				minValue: {
					setValueAs: (v) =>
						v || parseNumber(v) === 0 ? parseNumber(v) : "",
					validate: {
						isAppropriateType: (v) => isAppropriateType(v),
					},
				},
				maxValue: {
					setValueAs: (v) =>
						v || parseNumber(v) === 0 ? parseNumber(v) : "",
					validate: {
						isAppropriateType: (v) => isAppropriateType(v),
						maxBelowMin: (v) => {
							const min = parseNumber(getValues("minValue"));
							const max = parseNumber(v);
							if (isNaN(min) || isNaN(max)) {
								return true;
							}
							return max > min;
						},
					},
				},
				step: {
					min: 0,
					setValueAs: (v) =>
						v || parseNumber(v) === 0 ? parseNumber(v) : "",
					validate: {
						isAppropriateType: (v) => isAppropriateType(v),
						maxBelowStep: (v) => {
							if (getValues("maxValue") === "") {
								return true;
							}
							const max = parseNumber(
								Math.abs(getValues("maxValue"))
							);
							const step = parseNumber(v);
							if (isNaN(step) || isNaN(max)) {
								return true;
							}
							return max >= step;
						},
						minAndStepEqualOrLessThanMax: (v) => {
							const min = parseNumber(getValues("minValue"));
							const max = parseNumber(getValues("maxValue"));
							const step = parseNumber(v);
							if (isNaN(step) || isNaN(max) || isNaN(min)) {
								return true;
							}
							return min + step <= max;
						},
					},
				},
			},
		},
		media: {
			component: MediaSettings,
			fields: {
				allowedTypes: {
					setValueAs: (v) => (v ? v.replace(/\s/g, "") : ""),
					validate: {
						formattedCorrectly: (v) => {
							const types = v;
							const typesRegex = /^[a-z0-9, ]*$/g;
							return typesRegex.test(types);
						},
					},
				},
			},
		},
	};

	const AdvancedSettings = advancedSettings[type]?.component;

	const modalStyles = {
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

	useEffect(() => {
		/**
		 * Register fields that appear in the Advanced Settings modal.
		 * So that values save if the modal is not opened, and values
		 * persist if the modal is mounted then unmounted.
		 */
		if (type in advancedSettings) {
			Object.keys(advancedSettings[type]["fields"]).forEach((field) => {
				register(field, advancedSettings[type]["fields"][field]);
			});
		}
	}, [register, advancedSettings]);

	/**
	 * Store original field values for comparison. Workaround for React Hook
	 * Form v6, whose `isDirty` property is true if a checkbox is toggled twice.
	 */
	useEffect(() => {
		originalValues.current = getValues();
	}, []);

	function parseNumber(num) {
		return currentNumberType === "decimal"
			? parseFloat(num)
			: parseInt(num);
	}

	function apiAddField(data) {
		apiFetch({
			path: `/wpe/atlas/content-model-field`,
			method: editing ? "PUT" : "POST",
			_wpnonce: wpApiSettings.nonce,
			data,
		})
			.then((res) => {
				if (res.success) {
					dispatch({ type: "updateField", data, model });
				} else {
					// The user pressed “Update” but no data changed.
					// Just close the field as if it was updated.
					dispatch({ type: "closeField", id: data.id, model });
				}
				const action = editing ? "Updated" : "Created";
				sendEvent({
					category: "Fields",
					action: ` Field ${action}`,
				});
				hasDirtyField.current = false;
			})
			.catch((err) => {
				if (err.code === "acm_duplicate_content_model_field_id") {
					setError("slug", { type: "idExists" });
				}
				if (err.code === "acm_option_name_undefined") {
					err.data.problemIndex.map((index) => {
						setError("multipleChoice" + index, {
							type: "multipleChoiceNameEmpty" + index,
						});
					});
				}
				if (err.code === "acm_option_slug_undefined") {
					err.data.problemIndex.map((index) => {
						setError("multipleChoice" + index, {
							type: "multipleChoiceSlugEmpty" + index,
						});
					});
				}
				if (err.code === "acm_option_slug_duplicate") {
					err.data.duplicates.map((index) => {
						setError("multipleChoice" + index, {
							type: "multipleChoiceSlugDuplicate" + index,
						});
					});
				}
				if (
					err.code === "acm_duplicate_content_model_multi_option_id"
				) {
					err.data.duplicates.map((index) => {
						setError("multipleChoiceName" + index, {
							type: "multipleChoiceNameDuplicate" + index,
						});
					});
				}
				if (err.code === "acm_invalid_multi_options") {
					setError("multipleChoice", { type: "multipleChoiceEmpty" });
				}
				if (err.code === "acm_invalid_content_model") {
					console.error(
						__(
							"Attempted to create a field in a model that no longer exists.",
							"atlas-content-modeler"
						)
					);
				}
				if (err.code === "acm_invalid_related_content_model") {
					setError("reference", { type: "invalidRelatedModel" });
				}
				if (err.code === "acm_reverse_slug_conflict") {
					setError("reverseSlug", {
						type: "reverseIdConflicts",
						message: err.message,
					});
				}
				if (err.code === "acm_reverse_slug_in_use") {
					setError("reverseSlug", {
						type: "reverseIdInUse",
						message: err.message,
					});
				}
				if (err.code === "acm_reserved_field_slug") {
					setError("slug", { type: "nameReserved" });
				}
			});
	}

	const currentModel = query.get("id");
	const fields = models[currentModel]?.fields;
	const originalMediaFieldId = useRef(getFeaturedFieldId(fields));

	return (
		<form
			onSubmit={handleSubmit(apiAddField)}
			onChange={() => {
				hasDirtyField.current = !isEqual(
					originalValues.current,
					getValues()
				);
			}}
		>
			<input
				id="type"
				name="type"
				type="hidden"
				ref={register}
				value={type}
			/>
			<input id="id" name="id" type="hidden" ref={register} value={id} />
			<input
				id="model"
				name="model"
				type="hidden"
				ref={register}
				value={model}
			/>
			<input
				id="position"
				name="position"
				type="hidden"
				ref={register}
				value={position}
			/>
			<div className="d-flex flex-column d-sm-flex flex-sm-row">
				<div className="d-flex flex-column d-sm-flex flex-sm-row">
					<div
						className={`${
							errors.name ? "field has-error" : "field"
						} me-sm-5`}
					>
						<label htmlFor="name">Name</label>
						<br />
						<p className="help">
							Display name for your {supportedFields[type]} field.
						</p>
						<input
							aria-invalid={errors.name ? "true" : "false"}
							id="name"
							name="name"
							defaultValue={storedData?.name}
							placeholder="Name"
							type="text"
							ref={register({ required: true, maxLength: 50 })}
							onChange={(e) => {
								setInputGeneratorSourceValue(e.target.value);
								setNameCount(e.target.value.length);
								clearErrors("slug");
							}}
						/>
						<p className="field-messages">
							{errors.name && errors.name.type === "required" && (
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
							{errors.name && errors.name.type === "maxLength" && (
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
							<span className="count">{nameCount}/50</span>
						</p>
					</div>

					<div className={errors.slug ? "field has-error" : "field"}>
						<label htmlFor="slug">API Identifier</label>
						<br />
						<p className="help">
							{__(
								"Auto-generated and used for API requests.",
								"atlas-content-modeler"
							)}
						</p>
						<input
							id="slug"
							name="slug"
							type="text"
							defaultValue={storedData?.slug}
							ref={register({ required: true, maxLength: 50 })}
							onChange={(e) =>
								onChangeGeneratedValue(e.target.value)
							}
							readOnly={editing}
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
										{__(
											"Exceeds max length of 50.",
											"atlas-content-modeler"
										)}
									</span>
								</span>
							)}
							{errors.slug && errors.slug.type === "idExists" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										{__(
											"Another field in this model has the same API identifier.",
											"atlas-content-modeler"
										)}
									</span>
								</span>
							)}
							{errors.slug &&
								errors.slug.type === "nameReserved" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											{__(
												"Identifier in use or reserved.",
												"atlas-content-modeler"
											)}
										</span>
									</span>
								)}
						</p>
					</div>
				</div>
			</div>

			<div>
				{type in extraFields && (
					<ExtraFields
						editing={editing}
						data={storedData}
						control={control}
						watch={watch}
						errors={errors}
						clearErrors={clearErrors}
						register={register}
						fieldId={id}
						setValue={setValue}
						model={model}
					/>
				)}
				{!["richtext", "multipleChoice"].includes(type) && (
					<div className="field">
						<legend>Field Options</legend>
						<input
							name="required"
							type="checkbox"
							id={`is-required-${id}`}
							ref={register}
							defaultChecked={storedData?.required === true}
						/>
						<label
							htmlFor={`is-required-${id}`}
							className="checkbox is-required"
						>
							{__(
								"Make this field required",
								"atlas-content-modeler"
							)}
						</label>
						{["media"].includes(type) && (
							<div
								className={
									!!isRepeatableMedia && "read-only editing"
								}
							>
								<input
									name="isFeatured"
									type="checkbox"
									id={`featured-image-${id}`}
									ref={register}
									defaultChecked={
										storedData?.required === true
									}
									disabled={!!isRepeatableMedia}
									onChange={(event) => {
										/**
										 * Unchecks other fields when checking a field.
										 * Only one field can be the featured image field.
										 */
										if (event.target.checked) {
											dispatch({
												type: "setFeaturedImageField",
												id: id,
												model: currentModel,
											});
											return;
										}

										if (!event.target.checked) {
											/**
											 * When unchecking a field that was not the original
											 * media, restore isFeatured on the original media
											 * field if there is one. Prevents an issue where
											 * checking “is featured image then unchecking it removes
											 * isFeatured from the original.
											 */
											if (
												originalMediaFieldId.current &&
												originalMediaFieldId.current !==
													id
											) {
												dispatch({
													type:
														"setFeaturedImageField",
													id:
														originalMediaFieldId.current,
													model: currentModel,
												});
												return;
											}

											/**
											 * At this point we're just unchecking the original
											 * media field.
											 */
											dispatch({
												type: "setFieldProperties",
												id: id,
												model: currentModel,
												properties: [
													{
														name: "isFeatured",
														value: false,
													},
												],
											});
										}
									}}
								/>
								<label
									htmlFor={`featured-image-${id}`}
									className="checkbox featured-image"
								>
									{__(
										"Set as featured image",
										"atlas-content-modeler"
									)}
								</label>
								<p className="help featured-image">
									{__(
										"Limits media selection to image types.",
										"atlas-content-modeler"
									)}
								</p>
							</div>
						)}
					</div>
				)}
			</div>

			<div className="d-flex flex-column d-sm-flex flex-sm-row">
				<div className="d-flex flex-column d-sm-flex flex-sm-row">
					<div
						className={`${
							errors.description ? "field has-error" : "field"
						} me-sm-5`}
					>
						<label htmlFor="description">
							Description{" "}
							<span style={{ fontWeight: "normal" }}>
								(Optional)
							</span>
						</label>
						<br />
						<textarea
							aria-invalid={errors.description ? "true" : "false"}
							className="text-area-single-line mt-4"
							id="description"
							name="description"
							rows="4"
							defaultValue={storedData?.description}
							placeholder={__(
								"Add a description",
								"atlas-content-modeler"
							)}
							ref={register({ maxLength: 250 })}
							onChange={(e) => {
								setDescriptionCount(e.target.value.length);
							}}
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
							<span className="description-count">
								{descriptionCount}/250
							</span>
						</p>
					</div>
				</div>
			</div>

			<div className="buttons d-flex flex-row">
				<Button
					type="submit"
					className="first mr-1 mr-sm-2"
					data-testid="edit-model-update-create-button"
				>
					{editing
						? __("Update", "atlas-content-modeler")
						: __("Create", "atlas-content-modeler")}
				</Button>
				<TertiaryButton
					data-testid="edit-model-update-create-cancel-button"
					onClick={(event) => {
						event.preventDefault();
						hasDirtyField.current = false;
						editing
							? dispatch({
									type: "closeField",
									originalState: originalState.current,
									id,
									model,
							  })
							: dispatch({
									type: "removeField",
									originalState: originalState.current,
									id,
									model,
							  });
					}}
				>
					Cancel
				</TertiaryButton>
				{type in advancedSettings && (
					<>
						<LinkButton
							className="d-flex flex-row"
							data-testid="edit-model-update-create-settings-button"
							onClick={(event) => {
								event.preventDefault();
								setOptionsModalIsOpen(true);
							}}
						>
							<Icon type="settings" />
							{__("Advanced Settings", "atlas-content-modeler")}
						</LinkButton>
						<Modal
							isOpen={optionsModalIsOpen}
							contentLabel="Advanced Settings"
							portalClassName="atlas-content-modeler-modal atlas-content-modeler-field-settings-modal-container atlas-content-modeler atlas-content-modeler-admin-page"
							onRequestClose={() => {
								setOptionsModalIsOpen(false);
							}}
							style={modalStyles}
						>
							<h2 className="mb-5">Advanced Settings</h2>

							<AdvancedSettings
								errors={errors}
								storedData={storedData}
								setValue={setValue}
								getValues={getValues}
								trigger={trigger}
								watch={watch}
							/>

							<div className="d-flex flex-row mt-5">
								<Button
									onClick={async () => {
										const fieldsAreValid = await trigger(
											Object.keys(
												advancedSettings[type]["fields"]
											)
										);
										if (fieldsAreValid) {
											setPreviousState(getValues());
											setOptionsModalIsOpen(false);
										}
									}}
									type="submit"
									className="first mr-1 mr-sm-2"
									data-testid="model-advanced-settings-done-button"
								>
									{__("Done", "atlas-content-modeler")}
								</Button>
								<TertiaryButton
									data-testid="model-advanced-settings-cancel-button"
									onClick={() => {
										const resetValues = getValues();
										Object.keys(
											advancedSettings[type]["fields"]
										).forEach(
											(fieldName) =>
												(resetValues[fieldName] =
													previousState[fieldName])
										);
										reset(resetValues);
										setOptionsModalIsOpen(false);
									}}
								>
									{__("Cancel", "atlas-content-modeler")}
								</TertiaryButton>
							</div>
						</Modal>
					</>
				)}
			</div>
		</form>
	);
}

export default Form;
