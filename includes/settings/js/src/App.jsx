import React from "react";

export default function App() {
	return (
		<div className="app">

			{/* Content Models Empty List */}
			<div className="app-card">
				<section className="heading">
					<h2>Content Models</h2>
					<button>Add New</button>
				</section>
				<section className="card-content">
					<p>You have no Content Models. It might be a good idea to create one now.</p>
					<ul aria-hidden="true">
						<li className="empty"><span>&nbsp;</span><span>&nbsp;</span><span>&nbsp;</span><span>&nbsp;</span></li>
					</ul>
				</section>
			</div>

			{/* Content Models List */}
			<div className="app-card">
				<section className="heading">
					<h2>Content Models</h2>
					<button>Add New</button>
				</section>
				<section className="card-content">
					<ul className="model-list">
						<li>
							<a href="#" aria-label="Edit 5 fields for My Rabbits content model, created Jan 24, 2021">
								<span className="wide">
									<p className="label">Name</p>
									<p className="value"><strong>My Rabbits</strong></p>
								</span>
								<span className="widest">
									<p className="label">Description</p>
									<p className="value">This is my first content model.</p>
								</span>
								<span>
									<p className="label">Fields</p>
									<p className="value">5</p>
								</span>
								<span>
									<p className="label">Created</p>
									<p className="value">Jan 24, 2021</p>
								</span>
							</a>
							<span>
								<button className="options" aria-label="Options for My Rabbits content model">
									<svg className="options" width="16" height="4" viewBox="0 0 16 4" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M3.79995 1.99995C3.79995 2.99406 2.99406 3.79995 1.99995 3.79995C1.00584 3.79995 0.199951 2.99406 0.199951 1.99995C0.199951 1.00584 1.00584 0.199951 1.99995 0.199951C2.99406 0.199951 3.79995 1.00584 3.79995 1.99995Z" fill="#002838"/>
										<path d="M9.79995 1.99995C9.79995 2.99406 8.99406 3.79995 7.99995 3.79995C7.00584 3.79995 6.19995 2.99406 6.19995 1.99995C6.19995 1.00584 7.00584 0.199951 7.99995 0.199951C8.99406 0.199951 9.79995 1.00584 9.79995 1.99995Z" fill="#002838"/>
										<path d="M14 3.79995C14.9941 3.79995 15.8 2.99406 15.8 1.99995C15.8 1.00584 14.9941 0.199951 14 0.199951C13.0058 0.199951 12.2 1.00584 12.2 1.99995C12.2 2.99406 13.0058 3.79995 14 3.79995Z" fill="#002838"/>
									</svg>
								</button>
							</span>
						</li>
						<li>
							<a href="#" aria-label="Edit 5 fields for My Frogs content model, created Jan 24, 2021">
								<span className="wide">
									<p className="label">Name</p>
									<p className="value"><strong>My Frogs</strong></p>
								</span>
								<span className="widest">&nbsp;</span>
								<span>
									<p className="label">Fields</p>
									<p className="value">22</p>
								</span>
								<span>
									<p className="label">Created</p>
									<p className="value">Jan 25, 2021</p>
								</span>
							</a>
							<span>
								<button className="options" aria-label="Options for My Frogs content model">
									<svg className="options" width="16" height="4" viewBox="0 0 16 4" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M3.79995 1.99995C3.79995 2.99406 2.99406 3.79995 1.99995 3.79995C1.00584 3.79995 0.199951 2.99406 0.199951 1.99995C0.199951 1.00584 1.00584 0.199951 1.99995 0.199951C2.99406 0.199951 3.79995 1.00584 3.79995 1.99995Z" fill="#002838"/>
										<path d="M9.79995 1.99995C9.79995 2.99406 8.99406 3.79995 7.99995 3.79995C7.00584 3.79995 6.19995 2.99406 6.19995 1.99995C6.19995 1.00584 7.00584 0.199951 7.99995 0.199951C8.99406 0.199951 9.79995 1.00584 9.79995 1.99995Z" fill="#002838"/>
										<path d="M14 3.79995C14.9941 3.79995 15.8 2.99406 15.8 1.99995C15.8 1.00584 14.9941 0.199951 14 0.199951C13.0058 0.199951 12.2 1.00584 12.2 1.99995C12.2 2.99406 13.0058 3.79995 14 3.79995Z" fill="#002838"/>
									</svg>
								</button>
							</span>
						</li>
						<li>
							<a href="#" aria-label="Edit 5 fields for My Cats content model, created Jan 24, 2021">
								<span className="wide">
									<p className="label">Name</p>
									<p className="value"><strong>My Cats</strong></p>
								</span>
								<span className="widest">
									<p className="label">Description</p>
									<p className="value">This is my third content model.</p>
								</span>
								<span>
									<p className="label">Fields</p>
									<p className="value">7</p>
								</span>
								<span>
									<p className="label">Created</p>
									<p className="value">Jan 26, 2021</p>
								</span>
							</a>
							<span>
								<button className="options" aria-label="Options for My Frogs content model">
									<svg className="options" width="16" height="4" viewBox="0 0 16 4" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M3.79995 1.99995C3.79995 2.99406 2.99406 3.79995 1.99995 3.79995C1.00584 3.79995 0.199951 2.99406 0.199951 1.99995C0.199951 1.00584 1.00584 0.199951 1.99995 0.199951C2.99406 0.199951 3.79995 1.00584 3.79995 1.99995Z" fill="#002838"/>
										<path d="M9.79995 1.99995C9.79995 2.99406 8.99406 3.79995 7.99995 3.79995C7.00584 3.79995 6.19995 2.99406 6.19995 1.99995C6.19995 1.00584 7.00584 0.199951 7.99995 0.199951C8.99406 0.199951 9.79995 1.00584 9.79995 1.99995Z" fill="#002838"/>
										<path d="M14 3.79995C14.9941 3.79995 15.8 2.99406 15.8 1.99995C15.8 1.00584 14.9941 0.199951 14 0.199951C13.0058 0.199951 12.2 1.00584 12.2 1.99995C12.2 2.99406 13.0058 3.79995 14 3.79995Z" fill="#002838"/>
									</svg>
								</button>
							</span>
						</li>
					</ul>
				</section>
			</div>


			{/* Add Content Model */}
			<div className="app-card">
				<section className="heading">
					<h2>New Content Model</h2>
					<button className="tertiary">View All Models</button>
				</section>
				<section className="card-content">
					<form>
						<div>
							<label htmlFor="name">Name</label><br/>
							<p className="help">Display name for your content model, for example “My Rabbits”.</p>
							<input id="name" type="text" />
							<p className="limit">0/50</p>
						</div>
						<div>
							<label htmlFor="api">API Identifier</label><br/>
							<p className="help">Auto-generated and used for API requests.</p>
							<input id="api" type="text" readOnly={true} />
						</div>
						<div>
							<label htmlFor="description">Description</label><br/>
							<p className="help">A hint for content editors and API users.</p>
							<textarea id="description" />
							<p className="limit">0/250</p>
						</div>

						<button className="primary first">Create</button>
						<button className="tertiary">Cancel</button>
					</form>
				</section>
			</div>

			{/* Empty Content Model */}
			<div className="app-card">
				<section className="heading">
					<h2><a href="#">Content Models</a> / My Rabbits</h2>
					<button className="options" aria-label="Options for My Rabbits content model">
						<svg className="options" width="16" height="4" viewBox="0 0 16 4" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M3.79995 1.99995C3.79995 2.99406 2.99406 3.79995 1.99995 3.79995C1.00584 3.79995 0.199951 2.99406 0.199951 1.99995C0.199951 1.00584 1.00584 0.199951 1.99995 0.199951C2.99406 0.199951 3.79995 1.00584 3.79995 1.99995Z" fill="#002838"/>
							<path d="M9.79995 1.99995C9.79995 2.99406 8.99406 3.79995 7.99995 3.79995C7.00584 3.79995 6.19995 2.99406 6.19995 1.99995C6.19995 1.00584 7.00584 0.199951 7.99995 0.199951C8.99406 0.199951 9.79995 1.00584 9.79995 1.99995Z" fill="#002838"/>
							<path d="M14 3.79995C14.9941 3.79995 15.8 2.99406 15.8 1.99995C15.8 1.00584 14.9941 0.199951 14 0.199951C13.0058 0.199951 12.2 1.00584 12.2 1.99995C12.2 2.99406 13.0058 3.79995 14 3.79995Z" fill="#002838"/>
						</svg>
					</button>
				</section>
				<section className="card-content">
					<p>Your current model “My Rabbits” has no fields at the moment. It might be a good idea to add some now.</p>
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
										<filter id="filter0_d" x="0" y="0" width="64" height="64" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
											<feFlood flood-opacity="0" result="BackgroundImageFix"/>
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

		</div>
	);
}
