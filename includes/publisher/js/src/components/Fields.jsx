import React from "react";
import Field from "./Field";
import { getFieldOrder } from "../../../../settings/js/src/queries";

export default function Fields( props ) {
	const { model } = props;
	const { fields } = model;
	const fieldOrder = getFieldOrder(fields);

	return fieldOrder.map( (v, i, a) => {
		const field = fields[v];
		return <Field field={field} modelSlug={model.slug} key={v} />;
	} );
}
