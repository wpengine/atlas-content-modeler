/**
 * Additional form fields for the Number field type.
 */
import React from "react";
import { sprintf, __ } from "@wordpress/i18n";

const NumberFields = ({ register, data, editing }) => {
	return (
		<div className={editing ? "field read-only editing" : "field"}>
			<legend>Number Type</legend>
			<fieldset>
				<div className="radio-row">
					<input
						type="radio"
						id="integer"
						name="numberType"
						value="integer"
						ref={register}
						defaultChecked={
							data?.numberType === "integer" ||
							typeof data?.numberType === "undefined"
						}
						disabled={editing}
					/>
					<label className="radio" htmlFor="integer">
						{__("Integer", "atlas-content-modeler")}
						<span>1, 2, 3, 5, 8, 13…</span>
					</label>
				</div>
				<div className="radio-row">
					<input
						type="radio"
						id="decimal"
						name="numberType"
						value="decimal"
						ref={register}
						defaultChecked={data?.numberType === "decimal"}
						disabled={editing}
					/>
					<label className="radio" htmlFor="decimal">
						{__("Decimal", "atlas-content-modeler")}
						<span>3.14159265389</span>
					</label>
				</div>
			</fieldset>
		</div>
	);
};

export default NumberFields;
