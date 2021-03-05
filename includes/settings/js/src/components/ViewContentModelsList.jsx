import React, {useEffect, useState} from "react";
import { Link } from "react-router-dom";
const { apiFetch } = wp;

function getAllModels() {
	const allModels = apiFetch( {
		path: '/wpe/content-models',
		method: 'GET',
		_wpnonce: wpApiSettings.nonce,
	} ).then( res => {
		return res;
	} );
	return allModels;
}

function HeaderWithAddNewButton() {
	return <section className="heading">
		<h2>Content Models</h2>
		<Link to="/wp-admin/admin.php?page=wpe-content-model&view=create-model">
			<button>Add New</button>
		</Link>
	</section>;
}

export default function ViewContentModelsList() {

	const [loading, setLoading] = useState(true);
	const [models, setModels] = useState({});

	useEffect(() => {
		async function getModels() {
			const allModels = await getAllModels();
			return allModels;
		}

		getModels().then( (result) => {
			if ( Object.keys(result).length === 0 ) {
				setModels('none');
			} else {
				setModels(result);
			}
			setLoading(false);
		});
	}, [] );

	if ( loading ) {
		return (
			{/* Content Models List */},
				<div className="app-card">
					<HeaderWithAddNewButton/>
					<p>Loading...</p>
				</div>
		);
	}

	if ( models === 'none' ) {
		return <ViewNoContentModelsExist/>;
	}

	return (
		{/* Content Models List */},
		<div className="app-card">
			<HeaderWithAddNewButton/>
			<ViewContentModelUnorderedList models={models}/>
		</div>
	);
}

function ViewNoContentModelsExist() {
	return (
		{/* Content Models Empty List */},
		<div className="app-card">
			<HeaderWithAddNewButton/>
			<section className="card-content">
				<p>You have no Content Models. It might be a good idea to create one now.</p>
				<ul aria-hidden="true">
					<li className="empty"><span>&nbsp;</span><span>&nbsp;</span><span>&nbsp;</span><span>&nbsp;</span></li>
				</ul>
			</section>
		</div>
	);
}

function ListItems({models}) {
	return Object.keys(models).map(slug => {
		const { name, description } = models[slug];
		return (
			<li key={slug}>
				<Link to={`/wp-admin/admin.php?page=wpe-content-model&view=edit-model&id=${slug}`} aria-label={`Edit ${name} content model`}>
				<span className="wide">
					<p className="label">Name</p>
					<p className="value"><strong>{ name }</strong></p>
				</span>
					<span className="widest">
					<p className="label">Description</p>
					<p className="value">{description}</p>
				</span>
					<span>
					<p className="label">Fields</p>
					<p className="value">5</p>
				</span>
					<span>
					<p className="label">Created</p>
					<p className="value">Jan 24, 2021</p>
				</span>
				</Link>
				<span>
			<button className="options" aria-label={`Options for ${name} content model`}>
				<svg className="options" width="16" height="4" viewBox="0 0 16 4" fill="none"
					 xmlns="http://www.w3.org/2000/svg">
					<path
						d="M3.79995 1.99995C3.79995 2.99406 2.99406 3.79995 1.99995 3.79995C1.00584 3.79995 0.199951 2.99406 0.199951 1.99995C0.199951 1.00584 1.00584 0.199951 1.99995 0.199951C2.99406 0.199951 3.79995 1.00584 3.79995 1.99995Z"
						fill="#002838"/>
					<path
						d="M9.79995 1.99995C9.79995 2.99406 8.99406 3.79995 7.99995 3.79995C7.00584 3.79995 6.19995 2.99406 6.19995 1.99995C6.19995 1.00584 7.00584 0.199951 7.99995 0.199951C8.99406 0.199951 9.79995 1.00584 9.79995 1.99995Z"
						fill="#002838"/>
					<path
						d="M14 3.79995C14.9941 3.79995 15.8 2.99406 15.8 1.99995C15.8 1.00584 14.9941 0.199951 14 0.199951C13.0058 0.199951 12.2 1.00584 12.2 1.99995C12.2 2.99406 13.0058 3.79995 14 3.79995Z"
						fill="#002838"/>
				</svg>
			</button>
		</span>
			</li>
		);
	} );
}

function ViewContentModelUnorderedList({models}) {
	return (
		<section className="card-content">
			<ul className="model-list">
				<ListItems models={models}/>
			</ul>
		</section>
	);
}
