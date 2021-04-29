import React, {useRef, useState} from "react";

export default function MediaUploader({ modelSlug, field }) {
	const [value, setValue] = useState(field.value);
	const btnRef = useRef();
	const inputRef = useRef();

	/**
	 * Click handler to use wp media uploader
	 * @param e
	 */
	function clickHandler(e) {
		e.preventDefault();

		wp.media({
			title: 'Upload Media',
			// multiple: true if you want to upload multiple files at once
			multiple: false
		}).open()
			.on('select', function(e){
				// This will return the selected image from the Media Uploader, the result is an object
				var uploaded_image = image.state().get('selection').first();
				// We convert uploaded_image to a JSON object to make accessing it easier
				// Let's assign the url value to the input field
				inputRef.current = uploaded_image.toJSON().url;
				console.log(inputRef.current);
				// {`wpe-content-model[${modelSlug}][${field.slug}]`}
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
				   defaultValue={value} />

			<div>
				<input type="button"
				   	ref={btnRef}
					defaultValue="Upload Media"
					onClick={(e) => clickHandler(e)}
				/>
			</div>

			{value && (
				<div>
					<img src={value} alt={field.name} />
				</div>
			)}
		</>
	);
}
