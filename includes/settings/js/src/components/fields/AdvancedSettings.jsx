/**
 * Additional field options that appear in the Advanced Settings modal.
 */
import React from "react";
import Icon from "../../../../../components/icons";
import { __ } from "@wordpress/i18n";
import EmailSettings from "./EmailSettings";

const MediaSettings = ({
	errors,
	storedData,
	setValue,
	getValues,
	trigger,
}) => {
	return (
		<>
			<h3>{__("Allowed File Types", "atlas-content-modeler")}</h3>
			<p>
				{__(
					"Define what file types are allowed to be uploaded.",
					"atlas-content-modeler"
				)}
			</p>

			<p className="mb-4">
				<a
					href="https://wpengine.com/support/mime-types-wordpress/"
					target="_blank"
					rel="noreferrer"
				>
					{__("WordPress Allowed Types", "atlas-content-modeler")}
				</a>
			</p>

			<div className="d-flex flex-column d-sm-flex flex-sm-row">
				<div className="w-100">
					<div
						className={`${
							errors.allowedTypes ? "field has-error" : "field"
						} w-100`}
					>
						<label htmlFor="allowedTypes">
							{__("File Extensions", "atlas-content-modeler")}
						</label>
						<br />
						<textarea
							className="w-100"
							aria-invalid={
								errors.allowedTypes ? "true" : "false"
							}
							placeholder={__(
								"jpeg,jpg,png,tiff,tif,pdf",
								"atlas-content-modeler"
							)}
							id="allowedTypes"
							name="allowedTypes"
							onChange={async (e) => {
								setValue("allowedTypes", e.target.value, {
									shouldValidate: true,
								});
							}}
							defaultValue={String(
								getValues("allowedTypes") ??
									storedData?.allowedTypes
							)}
						/>
						<p className="field-messages">
							{errors.allowedTypes &&
								errors.allowedTypes.type ===
									"formattedCorrectly" && (
									<span className="error">
										<Icon type="error" />
										<span
											role="alert"
											className="text-start"
										>
											{__(
												"Must be a comma-separated list of file extensions without periods.",
												"atlas-content-modeler"
											)}
										</span>
									</span>
								)}
						</p>
					</div>
				</div>
			</div>
		</>
	);
};

const NumberSettings = ({
	errors,
	storedData,
	setValue,
	getValues,
	trigger,
}) => {
	function getDefaultNumberValue(value, storedValue) {
		return value ?? storedValue;
	}
	return (
		<>
			<h3>{__("Specific Number Range", "atlas-content-modeler")}</h3>
			<p className="mb-4">
				{__(
					"Define your range here. Only numeric characters can be used.",
					"atlas-content-modeler"
				)}
			</p>

			<div className="d-flex flex-column d-sm-flex flex-sm-row">
				<div>
					<div
						className={`${
							errors.minValue ? "field has-error" : "field"
						} me-sm-5`}
					>
						<label htmlFor="minValue">
							{__("Min Value", "atlas-content-modeler")}
						</label>
						<br />
						<input
							aria-invalid={errors.minValue ? "true" : "false"}
							type="number"
							step="0.1"
							id="minValue"
							name="minValue"
							onChange={async (e) => {
								setValue("minValue", e.target.value, {
									shouldValidate: true,
								});
								// Validate maxValue in case minValue is now bigger.
								await trigger("maxValue");
								await trigger("step");
							}}
							defaultValue={getDefaultNumberValue(
								getValues("minValue"),
								storedData?.minValue
							)}
						/>
						<p className="field-messages">
							{errors.minValue && errors.minValue.type === "min" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										{__(
											"The minimum value is 0.",
											"atlas-content-modeler"
										)}
									</span>
								</span>
							)}
							{errors.minValue &&
								errors.minValue.type ===
									"isAppropriateType" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											{__(
												"The value must be an integer.",
												"atlas-content-modeler"
											)}
										</span>
									</span>
								)}
						</p>
					</div>

					<div
						className={`${
							errors.maxValue ? "field has-error" : "field"
						} me-sm-5`}
					>
						<label htmlFor="maxValue">
							{__("Max Value", "atlas-content-modeler")}
						</label>
						<br />
						<input
							aria-invalid={errors.maxValue ? "true" : "false"}
							type="number"
							step="0.1"
							id="maxValue"
							name="maxValue"
							onChange={async (e) => {
								setValue("maxValue", e.target.value, {
									shouldValidate: true,
								});
								await trigger("step");
							}}
							defaultValue={getDefaultNumberValue(
								getValues("maxValue"),
								storedData?.maxValue
							)}
						/>
						<p className="field-messages">
							{errors.maxValue && errors.maxValue.type === "min" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										{__(
											"The minimum value is 0.",
											"atlas-content-modeler"
										)}
									</span>
								</span>
							)}
							{errors.maxValue &&
								errors.maxValue.type === "maxBelowMin" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											{__(
												"Max must be more than min.",
												"atlas-content-modeler"
											)}
										</span>
									</span>
								)}
							{errors.maxValue &&
								errors.maxValue.type ===
									"isAppropriateType" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											{__(
												"The value must be an integer.",
												"atlas-content-modeler"
											)}
										</span>
									</span>
								)}
						</p>
					</div>
				</div>

				<div className={errors.step ? "field has-error" : "field"}>
					<label htmlFor="step">
						{__("Step", "atlas-content-modeler")}
					</label>
					<br />
					<input
						aria-invalid={errors.step ? "true" : "false"}
						type="number"
						step="0.1"
						id="step"
						name="step"
						onChange={(e) => {
							setValue("step", e.target.value, {
								shouldValidate: true,
							});
						}}
						defaultValue={getDefaultNumberValue(
							getValues("step"),
							storedData?.step
						)}
					/>
					<p className="field-messages">
						{errors.step && errors.step.type === "min" && (
							<span className="error">
								<Icon type="error" />
								<span role="alert">
									{__(
										"The minimum value is 0.",
										"atlas-content-modeler"
									)}
								</span>
							</span>
						)}
						{errors.step && errors.step.type === "maxBelowStep" && (
							<span className="error">
								<Icon type="error" />
								<span role="alert">
									{__(
										"Step must be lower than max.",
										"atlas-content-modeler"
									)}
								</span>
							</span>
						)}
						{errors.step &&
							errors.step.type ===
								"minAndStepEqualOrLessThanMax" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										{__(
											"Min plus Step can't be larger than Max.",
											"atlas-content-modeler"
										)}
									</span>
								</span>
							)}
						{errors.step &&
							errors.step.type === "isAppropriateType" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										{__(
											"The value must be an integer.",
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

const TextSettings = ({
	errors,
	field,
	storedData,
	setValue,
	getValues,
	trigger,
}) => {
	const isRepeatable = getValues("isRepeatable");
	return (
		<>
			<h3>{__("Character Limit", "atlas-content-modeler")}</h3>
			<p className="mb-4">
				{__(
					"Set a minimum and/or maximum character count for this text field.",
					"atlas-content-modeler"
				)}
			</p>

			<div className="d-flex flex-column d-sm-flex flex-sm-row">
				<div
					className={`${
						errors.minChars ? "field has-error" : "field"
					} me-sm-5`}
				>
					<label htmlFor="minChars">
						{__("Minimum Character Limit", "atlas-content-modeler")}
					</label>
					<br />
					<input
						aria-invalid={errors.minChars ? "true" : "false"}
						type="number"
						id="minChars"
						name="minChars"
						onChange={async (e) => {
							setValue("minChars", e.target.value, {
								shouldValidate: true,
							});
							// Validate maxChars in case minChars is now bigger.
							await trigger("maxChars");
						}}
						defaultValue={String(
							getValues("minChars") ?? storedData?.minChars
						)}
					/>
					<p className="field-messages">
						{errors.minChars && errors.minChars.type === "min" && (
							<span className="error">
								<Icon type="error" />
								<span role="alert">
									{__(
										"The minimum value is 0.",
										"atlas-content-modeler"
									)}
								</span>
							</span>
						)}
					</p>
				</div>

				<div className={errors.maxChars ? "field has-error" : "field"}>
					<label htmlFor="maxChars">
						{__("Maximum Character Limit", "atlas-content-modeler")}
					</label>
					<br />
					<input
						aria-invalid={errors.maxChars ? "true" : "false"}
						type="number"
						id="maxChars"
						name="maxChars"
						onChange={(e) => {
							setValue("maxChars", e.target.value, {
								shouldValidate: true,
							});
						}}
						defaultValue={String(
							getValues("maxChars") ?? storedData?.maxChars
						)}
					/>
					<p className="field-messages">
						{errors.maxChars && errors.maxChars.type === "min" && (
							<span className="error">
								<Icon type="error" />
								<span role="alert">
									{__(
										"The minimum value is 1.",
										"atlas-content-modeler"
									)}
								</span>
							</span>
						)}
						{errors.maxChars &&
							errors.maxChars.type === "maxBelowMin" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										{__(
											"Max must be more than min.",
											"atlas-content-modeler"
										)}
									</span>
								</span>
							)}
					</p>
				</div>
			</div>
			{isRepeatable && (
				<div className="d-flex flex-column d-sm-flex flex-sm-row">
					<div
						className={`${
							errors.minRepeatable ? "field has-error" : "field"
						} me-sm-5`}
					>
						<label htmlFor="minRepeatable">
							{__(
								"Minimum Repeatable Limit",
								"atlas-content-modeler"
							)}
						</label>
						<br />
						<input
							aria-invalid={
								errors.minRepeatable ? "true" : "false"
							}
							type="number"
							id="minRepeatable"
							name="minRepeatable"
							onChange={async (e) => {
								setValue("minRepeatable", e.target.value, {
									shouldValidate: true,
								});
								// Validate maxRepeatable in case minRepeatable is now bigger.
								await trigger("maxRepeatable");
							}}
							defaultValue={String(
								getValues("minRepeatable") ??
									storedData?.minRepeatable
							)}
						/>
						<p className="field-messages">
							{errors.minRepeatable &&
								errors.minRepeatable.type === "min" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											{__(
												"The minimum value is 0.",
												"atlas-content-modeler"
											)}
										</span>
									</span>
								)}
						</p>
					</div>

					<div
						className={
							errors.maxRepeatable ? "field has-error" : "field"
						}
					>
						<label htmlFor="maxRepeatable">
							{__(
								"Maximum Repeatable Limit",
								"atlas-content-modeler"
							)}
						</label>
						<br />
						<input
							aria-invalid={
								errors.maxRepeatable ? "true" : "false"
							}
							type="number"
							id="maxRepeatable"
							name="maxRepeatable"
							onChange={(e) => {
								setValue("maxRepeatable", e.target.value, {
									shouldValidate: true,
								});
							}}
							defaultValue={String(
								getValues("maxRepeatable") ??
									storedData?.maxRepeatable
							)}
						/>
						<p className="field-messages">
							{errors.maxRepeatable &&
								errors.maxRepeatable.type === "min" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											{__(
												"The minimum value is 1.",
												"atlas-content-modeler"
											)}
										</span>
									</span>
								)}
							{errors.maxRepeatable &&
								errors.maxRepeatable.type === "maxBelowMin" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											{__(
												"Max must be more than min.",
												"atlas-content-modeler"
											)}
										</span>
									</span>
								)}
						</p>
					</div>
				</div>
			)}
		</>
	);
};

export { MediaSettings, TextSettings, NumberSettings, EmailSettings };
