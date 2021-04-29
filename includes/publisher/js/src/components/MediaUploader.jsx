import React, {useRef, useState} from "react";

export default function MediaUploader({ modelSlug, field }) {
	const [value, setValue] = useState(field.value);
	const inputRef = useRef();

	/**
	 * Click handler to use wp media uploader
	 * @param e - event
	 */
	function clickHandler(e) {
		e.preventDefault();

		const image = wp.media({
			title: value ? 'Change Media' : 'Upload Media',
			multiple: false
		}).open()
			.on('select', function(e){
				// This will return the selected image from the Media Uploader, the result is an object
				var uploaded_image = image.state().get('selection').first();
				// We convert uploaded_image to a JSON object to make accessing it easier
				// Let's assign the url value to the input field
				const url = uploaded_image.toJSON().url;
				setValue(url);
				inputRef.current = url;
				console.log('url', url);
				console.log('inputRef current', inputRef.current);
			});
	}

	return (
		<>
			<label
				htmlFor={`wpe-content-model[${modelSlug}][${field.slug}]`}
			>
				{field.name}
			</label>

			<input type="text"
				   name={`wpe-content-model[${modelSlug}][${field.slug}]`}
				   id={`wpe-content-model[${modelSlug}][${field.slug}]`}
				   ref={inputRef}
				   className="hidden"
				   readOnly={true}
				   value={value} />

			<div>
				{value && (
					<div style={{marginBottom: '10px'}}>
						<img style={{maxWidth: '200px', maxHeight: '100px'}} src={value} alt={field.name} />
					</div>
				)}

				<input type="button"
				   	className="button button-primary button-large"
					defaultValue={value ? 'Change Media' : 'Upload Media'}
					onClick={(e) => clickHandler(e)}
				/>
			</div>
		</>
	);
}
