/** @jsx jsx */
import { jsx, css } from "@emotion/react";
import React from "react";
import Icon from "../../../../../components/icons";
import { __ } from "@wordpress/i18n";

const emailDomainTextArea = css`
	.atlas-content-modeler-admin-page & {
		max-width: none;
		min-height: 55px;
	}
`;

const emailConstraintInput = css`
	.atlas-content-modeler-admin-page & {
		max-width: 150px;
	}
`;

const EmailSettings = ({
	errors,
	storedData,
	setValue,
	getValues,
	trigger,
	watch,
}) => {
	watch(["minRepeatable", "maxRepeatable", "exactRepeatable"]);
	const isRepeatable = getValues("isRepeatableEmail");
	const isMinMaxDisabled = !!getValues("exactRepeatable");
	const isExactDisabled =
		getValues("maxRepeatable") || !!getValues("minRepeatable");

	return (
		<>
			<div className="d-flex flex-column d-sm-flex flex-sm-row">
				<div className="w-100">
					<div
						className={`${
							errors.allowedDomains ? "field has-error" : "field"
						} w-100`}
					>
						<label htmlFor="allowedDomains">
							{__(
								"Specify Allowed Domains",
								"atlas-content-modeler"
							)}
						</label>
						<p>
							{__(
								`Comma-separated list of allowed domains, wildcards allowed. e.g. "*.edu"`,
								"atlas-content-modeler"
							)}
						</p>
						<textarea
							id="allowedDomains"
							name="allowedDomains"
							css={emailDomainTextArea}
							className="w-100"
							rows="1"
							aria-invalid={
								errors.allowedDomains ? "true" : "false"
							}
							placeholder={__(
								"*.edu, gmail.com, wpengine.com, *.flywheel.com",
								"atlas-content-modeler"
							)}
							onChange={async (e) => {
								setValue("allowedDomains", e.target.value, {
									shouldValidate: true,
								});
							}}
							defaultValue={String(
								getValues("allowedDomains") ??
									storedData?.allowedDomains
							)}
						/>
						<p className="field-messages">
							{errors.allowedDomains &&
								errors.allowedDomains.type ===
									"formattedCorrectly" && (
									<span className="error">
										<Icon type="error" />
										<span
											role="alert"
											className="text-start"
										>
											{__(
												"Must be a comma-separated list of domains and subdomains.",
												"atlas-content-modeler"
											)}
										</span>
									</span>
								)}
						</p>
					</div>
				</div>
			</div>

			{isRepeatable && (
				<div className="d-flex flex-column d-sm-flex flex-sm-row justify-content-between">
					<div
						className={
							errors.maxRepeatable ? "field has-error" : "field"
						}
					>
						<label htmlFor="minRepeatable">
							{__("Minimum", "atlas-content-modeler")}
						</label>
						<br />
						<input
							id="minRepeatable"
							name="minRepeatable"
							type="number"
							css={emailConstraintInput}
							aria-invalid={
								errors.minRepeatable ? "true" : "false"
							}
							defaultValue={String(
								getValues("minRepeatable") ??
									storedData?.minRepeatable
							)}
							onChange={async (e) => {
								setValue("minRepeatable", e.target.value, {
									shouldValidate: true,
								});
								// Validate maxRepeatable in case minRepeatable is now bigger.
								await trigger("maxRepeatable");
							}}
							disabled={isMinMaxDisabled}
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
							{__("Maximum", "atlas-content-modeler")}
						</label>
						<br />
						<input
							id="maxRepeatable"
							name="maxRepeatable"
							type="number"
							css={emailConstraintInput}
							aria-invalid={
								errors.maxRepeatable ? "true" : "false"
							}
							defaultValue={String(
								getValues("maxRepeatable") ??
									storedData?.maxRepeatable
							)}
							onChange={(e) => {
								setValue("maxRepeatable", e.target.value, {
									shouldValidate: true,
								});
							}}
							disabled={isMinMaxDisabled}
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

					<div
						className={
							errors.exactRepeatable ? "field has-error" : "field"
						}
					>
						<label htmlFor="exactRepeatable">
							{__("Exact", "atlas-content-modeler")}
						</label>
						<br />
						<input
							id="exactRepeatable"
							name="exactRepeatable"
							type="number"
							css={emailConstraintInput}
							aria-invalid={
								errors.exactRepeatable ? "true" : "false"
							}
							defaultValue={String(
								getValues("exactRepeatable") ??
									storedData?.exactRepeatable
							)}
							onChange={(e) => {
								setValue("exactRepeatable", e.target.value, {
									shouldValidate: true,
								});
							}}
							disabled={isExactDisabled}
						/>
						<p className="field-messages">
							{errors.exactRepeatable &&
								errors.exactRepeatable.type === "min" && (
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
						</p>
					</div>
				</div>
			)}
		</>
	);
};

export default EmailSettings;
