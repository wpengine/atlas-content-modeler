/**
 * Additional form fields for the Relationship field type.
 */
import React, { useContext, useState } from "react";
import { sprintf, __ } from "@wordpress/i18n";
import { ModelsContext } from "../../ModelsContext";
import { useLocationSearch } from "../../utils";
import Icon from "acm-icons";

const RelationshipFields = ({ register, data, editing, watch, errors }) => {
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
						{__("Relation Cardinality", "atlas-content-modeler")}
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
					<span className="count">{`${descriptionCount}/${descriptionMaxLength}`}</span>
				</p>
			</div>
		</>
	);
};

export default RelationshipFields;
