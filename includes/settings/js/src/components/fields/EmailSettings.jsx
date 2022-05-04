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

const EmailSettings = ({ errors, storedData, setValue, getValues }) => {
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
			<div className="d-flex flex-column justify-content-between d-sm-flex flex-sm-row">
				<div>
					<label htmlFor="minEntry">
						{__("Minimum", "atlas-content-modeler")}
					</label>
					<input
						css={emailConstraintInput}
						style={{ width: "100px !important" }}
						id="minEntry"
						name="minEntry"
						type="number"
					/>
				</div>
				<div>
					<label htmlFor="minEntry">
						{__("Maximum", "atlas-content-modeler")}
					</label>
					<input
						css={emailConstraintInput}
						id="maxEntry"
						name="maxEntry"
						type="number"
					/>
				</div>
				<div>
					<label htmlFor="exactEntry">
						{__("Exact", "atlas-content-modeler")}
					</label>
					<input
						css={emailConstraintInput}
						id="exactEntry"
						name="exactEntry"
						type="number"
					/>
				</div>
			</div>
		</>
	);
};

export default EmailSettings;
