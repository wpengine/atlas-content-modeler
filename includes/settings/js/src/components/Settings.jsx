import React from "react";
import { sprintf, __ } from "@wordpress/i18n";
const { wp } = window;
const { apiFetch } = wp;

export default function Settings() {
	function onChangeValue(event) {
		if (event.target.checked) {
			alert(event.target.value);
		}
	}

	return (
		<div className="app-card settings-view">
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
												htmlFor="optin"
											>
												<input
													type="radio"
													id="optin"
													name="optin"
													value="false"
													defaultChecked
													onChange={onChangeValue}
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
												id="optin"
												name="optin"
												value="true"
												onChange={onChangeValue}
											></input>
											<label
												className="radio"
												htmlFor="optin"
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
		</div>
	);
}
