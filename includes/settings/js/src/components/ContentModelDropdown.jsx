import React, {useContext, useState} from "react";
import {ModelsContext} from "../ModelsContext";
import Icon from "./icons";
import Modal from "react-modal";
import {EditModelModal} from "./EditModelModal";
import {useHistory} from "react-router-dom";
import {maybeCloseDropdown, removeSidebarMenuItem} from "../utils";
import {showError} from "../toasts";

Modal.setAppElement('#root');

const { apiFetch } = wp;

function deleteModel( name = '' ) {
	if ( ! name.length ) {
		return;
	}

	return apiFetch({
		path: `/wpe/content-model/${name}`,
		method: "DELETE",
		_wpnonce: wpApiSettings.nonce,
	});
}

export const ContentModelDropdown = ({model}) => {
	const { name, slug } = model;
	const { dispatch } = useContext(ModelsContext);
	const history = useHistory();
	const [ dropdownOpen, setDropdownOpen ] = useState(false);
	const [ modalIsOpen, setModalIsOpen ] = useState(false);
	const [ editModelModalIsOpen, setEditModelModalIsOpen ] = useState(false);

	const customStyles = {
		overlay: {
			backgroundColor: 'rgba(0, 40, 56, 0.7)'
		},
		content : {
			top: '50%',
			left: '50%',
			right: 'auto',
			bottom: 'auto',
			marginRight: '-50%',
			transform: 'translate(-50%, -50%)',
			border: 'none',
			padding: '40px'
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
				onBlur={() => maybeCloseDropdown(setDropdownOpen)}
			>
				<Icon type="options" />
			</button>
			<div className={`dropdown-content ${dropdownOpen ? '' : 'hidden' }`}>
				<a
					className="edit"
					href="#"
					onBlur={() => maybeCloseDropdown(setDropdownOpen)}
					onClick={ (event) => {
						event.preventDefault();
						setDropdownOpen(false);
						setEditModelModalIsOpen(true);
					} }
				>
					Edit
				</a>
				<a
					className="delete"
					href="#"
					onBlur={() => maybeCloseDropdown(setDropdownOpen)}
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
				<h2>Delete the {name} Content Model?</h2>
				<p>This is an irreversible action. You will have to recreate this model if you delete it.</p>
				<p>This will NOT delete actual data stored in this model. It only deletes the model definition.</p>
				<p>{`Are you sure you want to delete the ${name} content model?`}</p>
				<button
					className="first warning"
					onClick={ async () => {
						// delete model and remove related menu sidebar item
						await deleteModel(slug).then((res) => {
							if(res.success) {
								removeSidebarMenuItem(slug);
							}
						}).catch(() => {
							// @todo capture and show errors.
							showError(`There was an error. The ${slug} model type was not deleted.`);
						});

						setModalIsOpen(false);
						history.push( "/wp-admin/admin.php?page=wpe-content-model" );
						dispatch({type: 'removeModel', slug});
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
			<EditModelModal model={model} isOpen={editModelModalIsOpen} setIsOpen={setEditModelModalIsOpen} />
		</span>
	);
}
