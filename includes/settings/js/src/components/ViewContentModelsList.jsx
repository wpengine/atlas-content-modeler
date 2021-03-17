import React, { useContext, useState } from "react";
import { Link } from "react-router-dom";
import Modal from "react-modal";
import { ModelsContext } from "../ModelsContext";
import Icon from "./icons";

const { apiFetch } = wp;

Modal.setAppElement('#root');

function getAllModels() {
	const allModels = apiFetch({
		path: "/wpe/content-models",
		method: "GET",
		_wpnonce: wpApiSettings.nonce,
	}).then((res) => {
		return res;
	});
	return allModels;
}

function HeaderWithAddNewButton() {
	return (
		<section className="heading">
			<h2>Content Models</h2>
			<Link to="/wp-admin/admin.php?page=wpe-content-model&view=create-model">
				<button>Add New</button>
			</Link>
		</section>
	);
}

export default function ViewContentModelsList() {
	const { models } = useContext(ModelsContext);

	return (
		<div className="app-card">
			<HeaderWithAddNewButton />
			<section className="card-content">
				{models === "none" ? (
					<>
						<p>
							You have no Content Models. It might be a good idea to create one
							now.
						</p>
						<ul aria-hidden="true">
							<li className="empty">
								<span>&nbsp;</span>
								<span>&nbsp;</span>
								<span>&nbsp;</span>
								<span>&nbsp;</span>
							</li>
						</ul>
					</>
				) : (
					<ul className="model-list">
						<ContentModels models={models} />
					</ul>
				)}
			</section>
		</div>
	);
}

function ContentModels({ models }) {
	return Object.keys(models).map((slug) => {
		const { name, description, fields={} } = models[slug];
		return (
			<li key={slug}>
				<Link
					to={`/wp-admin/admin.php?page=wpe-content-model&view=edit-model&id=${slug}`}
					aria-label={`Edit ${name} content model`}
				>
					<span className="wide">
						<p className="label">Name</p>
						<p className="value">
							<strong>{name}</strong>
						</p>
					</span>
					<span className="widest">
						<p className="label">Description</p>
						<p className="value">{description}</p>
					</span>
					<span>
						<p className="label">Fields</p>
						<p className="value">{Object.keys(fields).length}</p>
					</span>
					<span>
						<p className="label">Created</p>
						<p className="value">Jan 24, 2021</p>
					</span>
				</Link>
				<ContentModelDropdown model={models[slug]} />
			</li>
		);
	});
}

const ContentModelDropdown = ({model}) => {
	const { name, slug } = model;
	const { refreshModels } = useContext(ModelsContext);
	const [ dropdownOpen, setDropdownOpen ] = useState(false);
	const [ modalIsOpen, setModalIsOpen ] = useState(false);
	const customStyles = {
		overlay: {
			backgroundColor: 'rgba(0, 40, 56, 0.7)'
		},
		content : {
			top                   : '50%',
			left                  : '50%',
			right                 : 'auto',
			bottom                : 'auto',
			marginRight           : '-50%',
			transform             : 'translate(-50%, -50%)'
		}
	};

	return (
		<span className="dropdown">
			<button
				className="options"
				aria-label={`Options for ${name} content model`}
				onClick={ () => {
					setDropdownOpen(!dropdownOpen);
				} }
			>
				<Icon type="options" />
			</button>
			<div className={`dropdown-content ${dropdownOpen ? '' : 'hidden' }`}>
				<a>Edit</a>
				<a
					className="delete"
					href="#"
					onClick={ (event) => {
						event.preventDefault();
						setDropdownOpen(false);
						setModalIsOpen(true);
					} }
				>
					Delete
				</a>
			</div>
			<Modal
				isOpen={modalIsOpen}
				contentLabel={`Delete the ${name} content model?`}
				portalClassName="wpe-content-model-delete-model-modal-container"
				onRequestClose={() => { setModalIsOpen(false) }}
				style={customStyles}
				model={model}
			>
				<h2>Delete Content Model</h2>
				<p>This is an irreversible action. You will have to recreate this model if you delete it.</p>
				<p>This will NOT delete actual data stored in this model. It only deletes the model definition.</p>
				<p>{`Are you sure you want to delete the ${name} content model?`}</p>
				<button
					className="first warning"
					onClick={ async () => {
						// @todo capture and show errors.
						const deleted = await deleteModel(slug);
						refreshModels();
						setModalIsOpen(false);
					}}
				>
					Delete
				</button>
				<button
					className="tertiary"
					onClick={() => {
						setModalIsOpen(false)
					}}
				>
					Cancel
				</button>
			</Modal>
		</span>
	);
}

function deleteModel( name = '' ) {
	if ( ! name.length ) {
		return;
	}

	const deleted = apiFetch({
		path: `/wpe/content-model/${name}`,
		method: "DELETE",
		_wpnonce: wpApiSettings.nonce,
	}).then((res) => {
		return res;
	});

	return deleted;
}
