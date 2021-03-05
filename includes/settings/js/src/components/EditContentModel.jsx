import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { useLocationSearch } from "../utils";
const { apiFetch } = wp;

async function getModel( slug ) {
	const model = apiFetch( {
		path: `/wpe/content-model/${slug}`,
		method: 'GET',
		_wpnonce: wpApiSettings.nonce,
	} ).then( res => {
		return res;
	} );
	return model;
}


export default function EditContentModel() {
	const [loading, setLoading] = useState(true);
	const [model, setModel] = useState(null);

	const query = useLocationSearch();
	const id = query.get('id');

	useEffect(() => {
		getModel(id).then( (result) => {
			setModel(result.data);
			setLoading(false);
		} );
	}, [] );

	if ( loading ) {
		return (
			{/* Content Models List */},
				<div className="app-card">
					<p>Loading...</p>
				</div>
		);
	}

	const { name } = model;

	return (
		{/* Empty Content Model */},
		<div className="app-card">
		<section className="heading">
			<h2><Link to="/wp-admin/admin.php?page=wpe-content-model">Content Models</Link> / {name}</h2>
			<button className="options" aria-label={`Options for ${name} content model`}>
				<svg className="options" width="16" height="4" viewBox="0 0 16 4" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M3.79995 1.99995C3.79995 2.99406 2.99406 3.79995 1.99995 3.79995C1.00584 3.79995 0.199951 2.99406 0.199951 1.99995C0.199951 1.00584 1.00584 0.199951 1.99995 0.199951C2.99406 0.199951 3.79995 1.00584 3.79995 1.99995Z" fill="#002838"/>
					<path d="M9.79995 1.99995C9.79995 2.99406 8.99406 3.79995 7.99995 3.79995C7.00584 3.79995 6.19995 2.99406 6.19995 1.99995C6.19995 1.00584 7.00584 0.199951 7.99995 0.199951C8.99406 0.199951 9.79995 1.00584 9.79995 1.99995Z" fill="#002838"/>
					<path d="M14 3.79995C14.9941 3.79995 15.8 2.99406 15.8 1.99995C15.8 1.00584 14.9941 0.199951 14 0.199951C13.0058 0.199951 12.2 1.00584 12.2 1.99995C12.2 2.99406 13.0058 3.79995 14 3.79995Z" fill="#002838"/>
				</svg>
			</button>
		</section>
		<section className="card-content">
			{<p>Your current model {name} has no fields at the moment. It might be a good idea to add some now.</p>}
			<ul className="model-list">
				<li className="empty"><span>&nbsp;</span><span>&nbsp;</span><span>&nbsp;</span><span>&nbsp;</span></li>
				<li className="add-item">
					<button>
						<svg className="add" width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
							<g filter="url(#filter0_d)">
								<circle cx="32" cy="27" r="12" fill="#7E5CEF"/>
								<path d="M33.3333 33.3333C33.3333 33.5173 33.1841 33.6666 33 33.6666H31C30.8159 33.6666 30.6666 33.5173 30.6666 33.3333V28.3333H25.6666C25.4826 28.3333 25.3333 28.184 25.3333 27.9999V25.9999C25.3333 25.8158 25.4826 25.6666 25.6666 25.6666H30.6666V20.6666C30.6666 20.4825 30.8159 20.3333 31 20.3333H33C33.1841 20.3333 33.3333 20.4825 33.3333 20.6666V25.6666H38.3333C38.5174 25.6666 38.6666 25.8158 38.6666 25.9999V27.9999C38.6666 28.184 38.5174 28.3333 38.3333 28.3333H33.3333V33.3333Z" fill="white"/>
							</g>
							<defs>
								<filter id="filter0_d" x="0" y="0" width="64" height="64" filterUnits="userSpaceOnUse" colorInterpolationFilters="sRGB">
									<feFlood floodOpacity="0" result="BackgroundImageFix"/>
									<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/>
									<feOffset dy="5"/>
									<feGaussianBlur stdDeviation="10"/>
									<feColorMatrix type="matrix" values="0 0 0 0 0.266667 0 0 0 0 0.266667 0 0 0 0 0.266667 0 0 0 0.15 0"/>
									<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/>
									<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow" result="shape"/>
								</filter>
							</defs>
						</svg>
					</button>
				</li>
			</ul>
		</section>
	</div>
	);
}
