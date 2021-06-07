/**
 * Additional field options that appear in the Advanced Settings modal.
 */
import React from "react";
import Icon from "../../../../../components/icons";

const TextSettings = ({ errors, storedData, setValue, getValues, trigger }) => {
	return (
		<>
			<h3>Character limit</h3>
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

export { TextSettings };
