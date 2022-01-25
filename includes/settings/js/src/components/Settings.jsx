/* global atlasContentModeler */
import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import { dispatch } from "@wordpress/data";
import { Card } from "../../../../shared-assets/js/components/card";

export default function Settings() {
	const [usageTracking, setUsageTracking] = useState(
		atlasContentModeler.usageTrackingEnabled
	);

	function saveUsageTrackingSetting(event) {
		// @todo catch save errors
		dispatch("core").saveSite({
			atlas_content_modeler_usage_tracking: event.target.value,
		});
		setUsageTracking(event.target.value);
	}

	return (
		<Card className="settings-view">
			<section className="heading flex-wrap d-flex flex-column d-sm-flex flex-sm-row">
				<h2>{__("Settings", "atlas-content-modeler")}</h2>
			</section>
			<section className="card-content">
				<form>
					<div className="row">
						<div className="col-xs-10 col-lg-4 order-1 order-lg-0">
							<div className="row">
								<div className="col-xs-12">
									<h4>
										{__(
											"Analytics",
											"atlas-content-modeler"
										)}
									</h4>
									<p className="help">
										{__(
											"Opt into anonymous usage tracking to help us make Atlas Content Modeler better.",
											"atlas-content-modeler"
										)}
									</p>
									<div className="row">
										<div className="col-xs-12">
											<label
												className="radio"
												htmlFor="atlas-content-modeler-settings[usageTrackingDisabled]"
											>
												<input
													type="radio"
													id="atlas-content-modeler-settings[usageTrackingDisabled]"
													name="atlas-content-modeler-settings[usageTracking]"
													value="0"
													checked={
														usageTracking === "0" ||
														!usageTracking
													}
													onChange={
														saveUsageTrackingSetting
													}
												></input>
												{__(
													"Disabled",
													"atlas-content-modeler"
												)}
											</label>
										</div>
										<div className="col-xs-12">
											<input
												type="radio"
												id="atlas-content-modeler-settings[usageTrackingEnabled]"
												name="atlas-content-modeler-settings[usageTracking]"
												value="1"
												checked={usageTracking === "1"}
												onChange={
													saveUsageTrackingSetting
												}
											></input>
											<label
												className="radio"
												htmlFor="atlas-content-modeler-settings[usageTrackingEnabled]"
											>
												{__(
													"Enabled",
													"atlas-content-modeler"
												)}
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
			</section>
		</Card>
	);
}
