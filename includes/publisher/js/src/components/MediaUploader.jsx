import React, {useRef, useState} from "react";

export default function MediaUploader({ modelSlug, field }) {
	const [value, setValue] = useState(field.value);
	const inputRef = useRef();
	const imageRegex = /\.(gif|jpe?g|tiff?|png|webp|bmp)$/i;

	/**
	 * Delete input value
	 */
	function deleteImage(e) {
		e.preventDefault();
		inputRef.current = '';
		setValue('');
	}

	/**
	 * Get file extension
	 * @param file
	 * @returns {any}
	 */
	function getFileExtension(file) {
		return file.split('.').pop();
	}

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
					<>
					<div style={{marginBottom: '10px'}}>
						{imageRegex.test(value) ? (
							<img onClick={(e) => clickHandler(e)} style={{cursor: 'pointer', maxWidth: '500px', maxHeight: '400px'}} src={value} alt={field.name} />
						) : (
							<a style={{fontSize: '14px'}} href={value}>[{getFileExtension(value).toUpperCase()}] {value}</a>
						)}
					</div>
					</>
				)}

				<input type="button"
				   	className="button button-primary button-large"
					defaultValue={value ? 'Change Media' : 'Upload Media'}
					onClick={(e) => clickHandler(e)}
				/>

				{value && (
					<input type="button"
					   style={{marginLeft: '10px'}}
					   className="button button-secondary btn-delete button-large"
					   defaultValue="Remove"
					   onClick={(e) => deleteImage(e)}
					/>
				)}
			</div>
		</>
	);
}
