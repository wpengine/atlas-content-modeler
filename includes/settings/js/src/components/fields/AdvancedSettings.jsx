/**
 * Additional field options that appear in the Advanced Settings modal.
 */
import React from "react";
import Icon from "../../../../../components/icons";

const NumberSettings = ({
	errors,
	storedData,
	setValue,
	getValues,
	trigger,
}) => {
	return (
		<>
			<h3>Specific Number Range</h3>
			<p className="mb-4">
				Define your range here. Only numberic characters can be used.
			</p>

			<div className="d-flex flex-column d-sm-flex flex-sm-row">
				<div>
					<div
						className={`${
							errors.minValue ? "field has-error" : "field"
						} me-sm-5`}
					>
						<label htmlFor="minValue">
							Minimum Character Limit
						</label>
						<br />
						<input
							aria-invalid={errors.minValue ? "true" : "false"}
							type="number"
							id="minValue"
							name="minValue"
							onChange={async (e) => {
								setValue("minValue", e.target.value, {
									shouldValidate: true,
								});
								// Validate maxValue in case minValue is now bigger.
								await trigger("maxValue");
							}}
							defaultValue={String(
								getValues("minValue") ?? storedData?.minValue
							)}
						/>
						<p className="field-messages">
							{errors.minValue && errors.minValue.type === "min" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										The minimum value is 0.
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
							Maximum Character Limit
						</label>
						<br />
						<input
							aria-invalid={errors.maxValue ? "true" : "false"}
							type="number"
							id="maxValue"
							name="maxValue"
							onChange={(e) => {
								setValue("maxValue", e.target.value, {
									shouldValidate: true,
								});
							}}
							defaultValue={String(
								getValues("maxValue") ?? storedData?.maxValue
							)}
						/>
						<p className="field-messages">
							{errors.maxValue && errors.maxValue.type === "min" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										The minimum value is 1.
									</span>
								</span>
							)}
							{errors.maxValue &&
								errors.maxValue.type === "maxBelowMin" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											Max must be more than min.
										</span>
									</span>
								)}
						</p>
					</div>
				</div>

				<div className={errors.step ? "field has-error" : "field"}>
					<label htmlFor="step">Step</label>
					<br />
					<input
						aria-invalid={errors.step ? "true" : "false"}
						type="number"
						id="step"
						name="step"
						onChange={(e) => {
							setValue("step", e.target.value, {
								shouldValidate: true,
							});
						}}
						defaultValue={String(
							getValues("step") ?? storedData?.step
						)}
					/>
					<p className="field-messages">
						{errors.step && errors.step.type === "min" && (
							<span className="error">
								<Icon type="error" />
								<span role="alert">
									The minimum value is 0.
								</span>
							</span>
						)}
					</p>
				</div>
			</div>
		</>
	);
};

const TextSettings = ({ errors, storedData, setValue, getValues, trigger }) => {
	return (
		<>
			<h3>Character Limit</h3>
			<p className="mb-4">
				Set a minimum and/or maximum character count for this text
				field.
			</p>

			<div className="d-flex flex-column d-sm-flex flex-sm-row">
				<div
					className={`${
						errors.minChars ? "field has-error" : "field"
					} me-sm-5`}
				>
					<label htmlFor="minChars">Minimum Character Limit</label>
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
									The minimum value is 0.
								</span>
							</span>
						)}
					</p>
				</div>

				<div className={errors.maxChars ? "field has-error" : "field"}>
					<label htmlFor="maxChars">Maximum Character Limit</label>
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
									The minimum value is 1.
								</span>
							</span>
						)}
						{errors.maxChars &&
							errors.maxChars.type === "maxBelowMin" && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										Max must be more than min.
									</span>
								</span>
							)}
					</p>
				</div>
			</div>
		</>
	);
};

export { TextSettings, NumberSettings };
