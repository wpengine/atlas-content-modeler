import React, { useContext, useState } from "react";
import { Link } from "react-router-dom";
import Modal from "react-modal";
import { ModelsContext } from "../ModelsContext";

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
				<svg
					className="options"
					width="16"
					height="4"
					viewBox="0 0 16 4"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
				>
					<path
						d="M3.79995 1.99995C3.79995 2.99406 2.99406 3.79995 1.99995 3.79995C1.00584 3.79995 0.199951 2.99406 0.199951 1.99995C0.199951 1.00584 1.00584 0.199951 1.99995 0.199951C2.99406 0.199951 3.79995 1.00584 3.79995 1.99995Z"
						fill="#002838"
					/>
					<path
						d="M9.79995 1.99995C9.79995 2.99406 8.99406 3.79995 7.99995 3.79995C7.00584 3.79995 6.19995 2.99406 6.19995 1.99995C6.19995 1.00584 7.00584 0.199951 7.99995 0.199951C8.99406 0.199951 9.79995 1.00584 9.79995 1.99995Z"
						fill="#002838"
					/>
					<path
						d="M14 3.79995C14.9941 3.79995 15.8 2.99406 15.8 1.99995C15.8 1.00584 14.9941 0.199951 14 0.199951C13.0058 0.199951 12.2 1.00584 12.2 1.99995C12.2 2.99406 13.0058 3.79995 14 3.79995Z"
						fill="#002838"
					/>
				</svg>
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
