/** @jsx jsx */
import { jsx, css } from "@emotion/react";
import React from "react";
import Icon from "../../../../../components/icons";
import { __ } from "@wordpress/i18n";

// TODO-abotz: figure out why we need important here and remove
const emailDomainTextArea = css({
	maxWidth: "none !important",
	minHeight: "55px !important",
});

const EmailSettings = ({
	errors,
	field,
	storedData,
	setValue,
	getValues,
	trigger,
}) => {
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
								`Comma seperated list of allowed domains, wild cards allowed. Eg "*.edu"`,
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
		</>
	);
};

export default EmailSettings;
