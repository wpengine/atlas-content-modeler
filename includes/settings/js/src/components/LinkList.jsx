import React from "react";

/**
 *
 * @param {string} classes - Classes for the list ul.
 * @param {array} links - Links for the list { index, classNames, styles, title, url, target, icon, hideTitle }.
 * @returns html
 */
export default function LinkList({ classes, linkOptions }) {
	const { links } = linkOptions;
	let { options } = linkOptions;
	const defaultOptions = {
		liClasses: "",
		aClasses: "",
		liStyles: {},
		aStyles: {},
	};

	options = { ...defaultOptions, ...options };

	function getLinks() {
		return links.map((link, index) => (
			<li
				key={link.index || index}
				style={link.liStyle || options.liStyles}
				className={link.liClassNames || options.liClasses}
			>
				<a
					style={link.aStyle || options.aStyles}
					className={link.aClassNames || options.aClasses}
					title={link.title}
					href={link.url}
					target={link.target || "_blank"}
					rel="noreferrer"
				>
					{link.icon && <span className={link.icon}></span>}
					{link.title && !link.hideTitle && <span>{link.title}</span>}
				</a>
			</li>
		));
	}

	return <ul className={classes}>{getLinks()}</ul>;
}
