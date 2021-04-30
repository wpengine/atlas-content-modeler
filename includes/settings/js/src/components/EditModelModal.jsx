import React, { useContext, useState } from 'react';
import { useForm } from 'react-hook-form';
import Modal from 'react-modal';
import { ModelsContext } from '../ModelsContext';
import Icon from './icons';

const { apiFetch } = wp;

/**
 * Updates a model via the REST API.
 *
 * @param slug Model slug.
 * @param data Model data.
 */
function updateModel(slug = '', data = {}) {
	if (!slug.length || Object.keys(data).length === 0) {
		return;
	}

	const updated = apiFetch({
		path: `/wpe/content-model/${slug}`,
		method: 'PATCH',
		_wpnonce: wpApiSettings.nonce,
		data,
	}).then((res) => {
		return res;
	});

	return updated;
}

/**
 * The modal component for editing a content model.
 *
 * @param {Object} model The model to edit.
 * @param {Boolean} isOpen Whether or not the model is open.
 * @param {Function} setIsOpen - Callback for opening and closing modal.
 * @returns {JSX.Element} Modal
 */
export function EditModelModal({ model, isOpen, setIsOpen }) {
	const [singularCount, setSingularCount] = useState(
		model.singular_name.length
	);
	const [pluralCount, setPluralCount] = useState(model.name.length);
	const [descriptionCount, setDescriptionCount] = useState(
		model.description.length
	);
	const { dispatch } = useContext(ModelsContext);
	const { register, handleSubmit, errors } = useForm();

	const customStyles = {
		overlay: {
			backgroundColor: 'rgba(0, 40, 56, 0.7)',
		},
		content: {
			top: '50%',
			left: '50%',
			right: 'auto',
			bottom: 'auto',
			transform: 'translate(-50%, -50%)',
			border: 'none',
			padding: '40px',
		},
	};

	return (
		<Modal
			isOpen={isOpen}
			contentLabel={`Editing the ${model.name} content model`}
			parentSelector={() => {
				return document.getElementById('root');
			}}
			portalClassName="wpe-content-model-edit-model-modal-container"
			onRequestClose={() => {
				setIsOpen(false);
			}}
			model={model}
			style={customStyles}
		>
			<h2>Edit {model.name}</h2>
			<form
				onSubmit={handleSubmit(async (data) => {
					const mergedData = { ...model, ...data };
					await updateModel(data.postTypeSlug, mergedData);
					dispatch({ type: 'updateModel', data: mergedData });
					setIsOpen(false);
				})}
			>
				<div className={errors.singular ? 'field has-error' : 'field'}>
					<label htmlFor="singular">Singular Name</label>
					<p className="help">
						Singular display name for your content model, e.g.
						"Rabbit".
					</p>
					<input
						id="singular"
						name="singular"
						placeholder="Rabbit"
						defaultValue={model.singular_name}
						ref={register({ required: true, maxLength: 50 })}
						onChange={(e) =>
							setSingularCount(e.target.value.length)
						}
					/>
					<p className="field-messages">
						{errors.singular &&
							errors.singular.type === 'required' && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										This field is required
									</span>
								</span>
							)}
						{errors.singular &&
							errors.singular.type === 'maxLength' && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										Exceeds max length.
									</span>
								</span>
							)}
						<span>&nbsp;</span>
						<span className="count">{singularCount}/50</span>
					</p>
				</div>

				<div className={errors.plural ? 'field has-error' : 'field'}>
					<label htmlFor="plural">Plural Name</label>
					<p className="help">
						Plural display name for your content model, e.g.
						"Rabbits".
					</p>
					<input
						id="plural"
						name="plural"
						defaultValue={model.name}
						placeholder="Rabbits"
						ref={register({ required: true, maxLength: 50 })}
						onChange={(event) => {
							setPluralCount(event.target.value.length);
						}}
					/>
					<p className="field-messages">
						{errors.plural && errors.plural.type === 'required' && (
							<span className="error">
								<Icon type="error" />
								<span role="alert">This field is required</span>
							</span>
						)}
						{errors.plural && errors.plural.type === 'maxLength' && (
							<span className="error">
								<Icon type="error" />
								<span role="alert">Exceeds max length.</span>
							</span>
						)}
						<span>&nbsp;</span>
						<span className="count">{pluralCount}/50</span>
					</p>
				</div>

				<div className="field">
					<label htmlFor="postTypeSlug">API Identifier</label>
					<p className="help">
						Auto-generated and used for API requests.
					</p>
					<input
						id="postTypeSlug"
						name="postTypeSlug"
						ref={register({ required: true, maxLength: 20 })}
						defaultValue={model.slug}
						readOnly="readOnly"
					/>
					<p className="field-messages">
						<span>&nbsp;</span>
					</p>
				</div>

				<div
					className={
						errors.description
							? 'field field-description has-error'
							: 'field field-description'
					}
				>
					<label htmlFor="description">Description</label>
					<p className="help">
						A hint for content editors and API users.
					</p>
					<textarea
						id="description"
						name="description"
						ref={register({ maxLength: 250 })}
						defaultValue={model.description}
						onChange={(e) =>
							setDescriptionCount(e.target.value.length)
						}
					/>
					<p className="field-messages">
						{errors.description &&
							errors.description.type === 'maxLength' && (
								<span className="error">
									<Icon type="error" />
									<span role="alert">
										Exceeds max length.
									</span>
								</span>
							)}
						<span>&nbsp;</span>
						<span className="count">{descriptionCount}/250</span>
					</p>
				</div>

				<button type="submit" className="primary first">
					Save
				</button>
				<button
					href="#"
					className="tertiary"
					onClick={(event) => {
						event.preventDefault();
						setIsOpen(false);
					}}
				>
					Cancel
				</button>
			</form>
		</Modal>
	);
}
