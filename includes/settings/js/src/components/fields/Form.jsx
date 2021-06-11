import React, { useState, useContext, useRef, useEffect } from "react";
import { useForm } from "react-hook-form";
import Modal from "react-modal";
import { useLocationSearch } from "../../utils";
import Icon from "../../../../../components/icons";
import TextFields from "./TextFields";
import { TextSettings, NumberSettings } from "./AdvancedSettings";
import NumberFields from "./NumberFields";
import supportedFields from "./supportedFields";
import { ModelsContext } from "../../ModelsContext";
import { useApiIdGenerator } from "./useApiIdGenerator";

const { apiFetch } = wp;
const { cloneDeep } = lodash;

const extraFields = {
	text: TextFields,
	number: NumberFields,
};

Modal.setAppElement("#root");

function Form({ id, position, type, editing, storedData }) {
	const {
		register,
		handleSubmit,
		errors,
		setValue,
		getValues,
		clearErrors,
		setError,
		reset,
		trigger,
		watch,
	} = useForm({
		mode: "onChange",
		defaultValues: storedData,
	});
	const [nameCount, setNameCount] = useState(storedData?.name?.length || 0);
	const [optionsModalIsOpen, setOptionsModalIsOpen] = useState(false);
	const { models, dispatch } = useContext(ModelsContext);
	const query = useLocationSearch();
	const model = query.get("id");
	const ExtraFields = extraFields[type] ?? null;
	const currentNumberType = watch("numberType");
	const { setApiIdGeneratorInput, apiIdFieldAttributes } = useApiIdGenerator({
		setValue,
		editing,
		storedData,
	});
	const originalState = useRef(cloneDeep(models[model]["fields"] || {}));
	const [previousState, setPreviousState] = useState(storedData);

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
			},
		},
		number: {
			component: NumberSettings,
			fields: {
				minValue: {
					setValueAs: (v) => (v ? parseNumber(v) : ""),
				},
				maxValue: {
					setValueAs: (v) => (v ? parseNumber(v) : ""),
					validate: {
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
					setValueAs: (v) => (v ? parseNumber(v) : ""),
					validate: {
						maxBelowStep: (v) => {
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
			})
			.catch((err) => {
				if (err.code === "wpe_duplicate_content_model_field_id") {
					setError("slug", { type: "idExists" });
				}
				if (err.code === "wpe_invalid_content_model") {
					console.error(
						"Attempted to create a field in a model that no longer exists."
					);
				}
			});
	}

	return (
		<form onSubmit={handleSubmit(apiAddField)}>
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
								setApiIdGeneratorInput(e.target.value);
								setNameCount(e.target.value.length);
								clearErrors("slug");
							}}
						/>
						<p className="field-messages">
							{errors.name && errors.name.type === "required" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										This field is required
									</span>
								</span>
							)}
							{errors.name && errors.name.type === "maxLength" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										Exceeds max length.
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
							Auto-generated and used for API requests.
						</p>
						<input
							id="slug"
							name="slug"
							type="text"
							defaultValue={storedData?.slug}
							ref={register({ required: true, maxLength: 50 })}
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
										Exceeds max length of 50.
									</span>
								</span>
							)}
							{errors.slug && errors.slug.type === "idExists" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										Another field in this model has the same
										API identifier.
									</span>
								</span>
							)}
						</p>
					</div>
				</div>
			</div>

			<div>
				{!["richtext"].includes(type) && (
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
							Make this field required
						</label>
					</div>
				)}

				{type in extraFields && (
					<ExtraFields
						editing={editing}
						data={storedData}
						register={register}
						fieldId={id}
					/>
				)}
			</div>

			<div className="buttons d-flex flex-row">
				<button type="submit" className="primary first mr-1 mr-sm-2">
					{editing ? "Update" : "Create"}
				</button>
				<button
					className="tertiary"
					onClick={(event) => {
						event.preventDefault();
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
				</button>
				{type in advancedSettings && (
					<>
						<button
							className="settings d-flex flex-row"
							onClick={(event) => {
								event.preventDefault();
								setOptionsModalIsOpen(true);
							}}
						>
							<Icon type="settings" />
							Advanced Settings
						</button>
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
							/>

							<div className="d-flex flex-row mt-5">
								<button
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
									className="primary first mr-1 mr-sm-2"
								>
									Done
								</button>
								<button
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
									className="tertiary"
								>
									Cancel
								</button>
							</div>
						</Modal>
					</>
				)}
			</div>
		</form>
	);
}

export default Form;
