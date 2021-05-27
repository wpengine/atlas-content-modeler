import React, { useState, useContext, useRef } from "react";
import { useForm } from "react-hook-form";
import { useLocationSearch } from "../../utils";
import Icon from "../../../../../components/icons";
import TextFields from "./TextFields";
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

function Form({ id, position, type, editing, storedData }) {
	const {
		register,
		handleSubmit,
		errors,
		setValue,
		clearErrors,
		setError,
	} = useForm();
	const [nameCount, setNameCount] = useState(storedData?.name?.length || 0);
	const { models, dispatch } = useContext(ModelsContext);
	const query = useLocationSearch();
	const model = query.get("id");
	const ExtraFields = extraFields[type] ?? null;
	const { setApiIdGeneratorInput, apiIdFieldAttributes } = useApiIdGenerator({
		setValue,
		editing,
		storedData,
	});
	const originalState = useRef(cloneDeep(models[model]["fields"] || {}));

	function apiAddField(data) {
		apiFetch({
			path: `/wpe/content-model-field`,
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
			</div>
		</form>
	);
}

export default Form;
