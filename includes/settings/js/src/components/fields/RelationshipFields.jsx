/**
 * Additional form fields for the Relationship field type.
 */
import React, { useContext, useState } from "react";
import { sprintf, __ } from "@wordpress/i18n";
import { ModelsContext } from "../../ModelsContext";
import { useLocationSearch } from "../../utils";
import Icon from "acm-icons";
import { useInputGenerator } from "../../hooks";
import { toValidApiId } from "../../formats";

const RelationshipFields = ({
	register,
	data,
	editing,
	watch,
	errors,
	setValue,
	model,
	clearErrors,
}) => {
	const { models } = useContext(ModelsContext);
	const modelsAlphabetical = Object.values(models).sort((a, b) =>
		a.plural.toLowerCase() < b.plural.toLowerCase() ? -1 : 1
	);
	const modelId = useLocationSearch().get("id");
	const selectedReference = watch("reference");
	const [descriptionCount, setDescriptionCount] = useState(
		data?.description?.length || 0
	);
	const descriptionMaxLength = 250;
	const [reverseNameCount, setNameCount] = useState(
		data?.reverseName?.length || 0
	);
	const [showReferenceFields, setShowReferenceFields] = useState(
		data?.enableReverse === true
	);
	const {
		setInputGeneratorSourceValue,
		onChangeGeneratedValue,
	} = useInputGenerator({
		linked: !editing,
		setGeneratedValue: (value) => setValue("reverseSlug", value),
		setDefaultInputValue: (value) => {
			setNameCount(value?.length);
			setValue("reverseName", value);
		},
		defaultInputValue: data?.reverseName
			? data.reverseName
			: models[model].plural,
		format: toValidApiId,
	});

	return (
		<>
			<div className="d-flex flex-column d-sm-flex flex-sm-row">
				<div
					className={`field me-sm-5
						${errors.reference && " has-error"}
						${editing && "read-only editing"}
						`}
				>
					<legend>
						{__("Model to Reference", "atlas-content-modeler")}
					</legend>
					<p className="help">
						{__(
							"The related model to show entries from.",
							"atlas-content-modeler"
						)}
					</p>
					<select
						name="reference"
						ref={register({ required: true })}
						id="reference"
						disabled={editing}
					>
						<option value="">
							— {__("Choose a model", "atlas-content-modeler")} —
						</option>
						{modelsAlphabetical.map((model) => {
							return (
								<option key={model.slug} value={model.slug}>
									{model.plural}
								</option>
							);
						})}
					</select>
					{editing && (
						<input
							type="hidden"
							ref={register()}
							name="reference"
							value={data?.reference}
						/>
					)}
					<p className="field-messages">
						{errors.reference &&
							errors.reference.type === "required" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										{__(
											"Please choose a related model",
											"atlas-content-modeler"
										)}
									</span>
								</span>
							)}
						{errors.reference &&
							errors.reference.type === "invalidRelatedModel" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										{__(
											"This model no longer exists.",
											"atlas-content-modeler"
										)}
									</span>
								</span>
							)}
					</p>
				</div>
				<div className={editing ? "field read-only editing" : "field"}>
					<legend>
						{__("Connections", "atlas-content-modeler")}
					</legend>
					<fieldset>
						<div className="radio-row">
							<input
								type="radio"
								id="one-to-one"
								name="cardinality"
								value="one-to-one"
								ref={register}
								defaultChecked={
									data?.cardinality === "one-to-one" ||
									typeof data?.cardinality === "undefined"
								}
								disabled={editing}
							/>
							<label className="radio" htmlFor="one-to-one">
								{__("One to One", "atlas-content-modeler")}
								<span>
									{sprintf(
										__(
											/*
											 * translators:
											 * 1: Singular name of the current model (e.g. “Country”)
											 * 2: Singular name of the related model (e.g. “Capital City”) or “related item” if no related model is selected.
											 */
											"One %1$s can have one %2$s.",
											"atlas-content-modeler"
										),
										models[modelId].singular,
										models[selectedReference]?.singular
											? models[selectedReference].singular
											: __(
													"related item",
													"atlas-content-modeler"
											  )
									)}
								</span>
							</label>
						</div>
						<div className="radio-row">
							<input
								type="radio"
								id="one-to-many"
								name="cardinality"
								value="one-to-many"
								ref={register}
								disabled={editing}
							/>
							<label className="radio" htmlFor="one-to-many">
								{__("One To Many", "atlas-content-modeler")}
								<span>
									{sprintf(
										__(
											/*
											 * translators:
											 * 1: Singular name of the current content model (e.g. “Company”).
											 * 2: Plural name of the related model (e.g. “Employees”) or “related items” if no related model is selected.
											 */
											"One %1$s can have many %2$s.",
											"atlas-content-modeler"
										),
										models[modelId].singular,
										models[selectedReference]?.plural
											? models[selectedReference].plural
											: __(
													"related items",
													"atlas-content-modeler"
											  )
									)}
								</span>
							</label>
						</div>
					</fieldset>
				</div>
			</div>
			<div className="field">
				<label htmlFor="description">
					{__("Description", "atlas-content-modeler")}{" "}
					<span>({__("Optional", "atlas-content-modeler")})</span>
				</label>
				<p className="help">
					{sprintf(
						__(
							/* translators: %s: Singular name of the current content model (e.g. “Employee”). */
							"Displayed next to the relationship field on the %s entry form.",
							"atlas-content-modeler"
						),
						models[modelId].singular
					)}
				</p>
				<textarea
					name="description"
					ref={register({ maxLength: descriptionMaxLength })}
					id="description"
					className="two-columns"
					onChange={(e) => setDescriptionCount(e.target.value.length)}
				/>
				<p className="field-messages two-columns">
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
					<span className="count description-count">{`${descriptionCount}/${descriptionMaxLength}`}</span>
				</p>
			</div>
			<div className="d-flex flex-column d-sm-flex flex-sm-row">
				<div className="field">
					<legend>
						{__("Reverse Reference", "atlas-content-modeler")}
					</legend>
					<input
						name="enableReverse"
						type="checkbox"
						id={`enable-reverse`}
						ref={register}
						defaultChecked={showReferenceFields}
						onChange={() =>
							setShowReferenceFields(!showReferenceFields)
						}
					/>
					<label
						htmlFor={`enable-reverse`}
						className="checkbox enable-reverse"
					>
						{__(
							"Configure Reverse Reference",
							"atlas-content-modeler"
						)}
					</label>
				</div>
			</div>
			<div
				className={
					showReferenceFields
						? "d-flex flex-column d-sm-flex flex-sm-row"
						: "d-none"
				}
			>
				<div
					className={`${
						errors.reverseName ? "field has-error" : "field"
					} me-sm-5`}
				>
					<label htmlFor="reverseName">
						{__("Reverse Display Name", "atlas-content-modeler")}
					</label>
					<br />
					<p className="help">
						{__(
							"Display name for your reverse connection.",
							"atlas-content-modeler"
						)}
					</p>
					<input
						aria-invalid={errors.reverseName ? "true" : "false"}
						id="reverseName"
						name="reverseName"
						ref={register({
							maxLength: 50,
							validate: {
								// Require field if “configure references” is open.
								required: (value) => {
									return showReferenceFields ? !!value : true;
								},
							},
						})}
						placeholder="Reverse Display Name"
						type="text"
						onChange={(e) => {
							setInputGeneratorSourceValue(e.target.value);
							setNameCount(e.target.value.length);
							clearErrors("reverseSlug");
						}}
					/>
					<p className="field-messages">
						{errors.reverseName &&
							errors.reverseName.type === "required" && (
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
						{errors.reverseName &&
							errors.reverseName.type === "maxLength" && (
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
						<span className="count reverse-name-count">
							{reverseNameCount}/50
						</span>
					</p>
				</div>
				<div
					className={errors.reverseSlug ? "field has-error" : "field"}
				>
					<label htmlFor="slug">
						{__("Reverse API Identifier", "atlas-content-modeler")}
					</label>
					<br />
					<p className="help">
						{__(
							"Auto-generated and used for API requests.",
							"atlas-content-modeler"
						)}
					</p>
					<input
						id="reverseSlug"
						name="reverseSlug"
						type="text"
						ref={register({
							maxLength: 50,
							validate: {
								// Require field if “configure references” is open.
								required: (value) => {
									return showReferenceFields ? !!value : true;
								},
							},
						})}
						onChange={(e) => onChangeGeneratedValue(e.target.value)}
					/>
					<p className="field-messages">
						{errors.reverseSlug &&
							errors.reverseSlug.type === "required" && (
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
						{errors.reverseSlug &&
							errors.reverseSlug.type === "maxLength" && (
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
						{errors.reverseSlug &&
							errors.reverseSlug.type === "idExists" && (
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
					</p>
				</div>
			</div>
		</>
	);
};

export default RelationshipFields;
