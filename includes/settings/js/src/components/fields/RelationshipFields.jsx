/**
 * Additional form fields for the Relationship field type.
 */
import React, { useContext } from "react";
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

	return (
		<>
			<div className="d-flex flex-column d-sm-flex flex-sm-row">
				<div
					className={`${
						errors.reference ? "field has-error" : "field"
					} me-sm-5`}
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
											"One %s can have one %s.",
											"atlas-content-modeler"
										),
										models[modelId].singular,
										selectedReference
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
								defaultChecked={
									data?.numberType === "one-to-many"
								}
								disabled={editing}
							/>
							<label className="radio" htmlFor="one-to-many">
								{__("One To Many", "atlas-content-modeler")}
								<span>
									{sprintf(
										__(
											"One %s can have many %s.",
											"atlas-content-modeler"
										),
										models[modelId].singular,
										selectedReference
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
							"Displayed next to the relationship field on the %s entry form.",
							"atlas-content-modeler"
						),
						models[modelId].singular
					)}
				</p>
				<textarea
					name="description"
					ref={register()}
					id="description"
				/>
			</div>
		</>
	);
};

export default RelationshipFields;
