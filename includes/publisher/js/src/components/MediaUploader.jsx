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
					<>
					<div style={{marginBottom: '10px'}}>
						<img onClick={(e) => clickHandler(e)} style={{cursor: 'pointer', maxWidth: '500px', maxHeight: '400px'}} src={value} alt={field.name} />
					</div>

						{/*<video width="320" height="240" autoPlay muted>*/}
						{/*	<source src="movie.mp4" type="video/mp4"/>*/}
						{/*	<source src="movie.ogg" type="video/ogg"/>*/}
						{/*	Your browser does not support the video tag.*/}
						{/*</video>*/}
					</>
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
