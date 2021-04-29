import React from "react";

export default function MediaUploader({ modelSlug, field }) {
	return (
		<>
			<label
				htmlFor={`wpe-content-model-${modelSlug}-${field.slug}`}
			>
				{field.name}
			</label>
			<input type="text"
				   className="hidden"
				   name={`wpe-content-model-${modelSlug}-${field.slug}`}
				   id={`wpe-content-model-${modelSlug}-${field.slug}`}
				   defaultValue={field.value} />
			<div>
				<input type="button"
					   className="button button-primary button-large wpe-upload-btn"
					   data-field={`wpe-content-model-${modelSlug}-${field.slug}`}
					   name={`upload_btn_${field.slug}`}
					   id={`upload_btn_${field.slug}`}
					   defaultValue="Upload Media" />
				{field.value && (
					<img src={field.value} alt={field.name} />
				)}
			</div>
		</>
	);
}
