import React from 'react';
import Icon from "../icons"

const Repeater = ({fields}) => {
	const hasFields = Object.keys(fields)?.length > 0;
	return (
		<>
			<div className="break">&nbsp;</div>
			<div className="repeater-fields">
				{
					hasFields
						? <p>Fields to appear here</p>
						:
						<>
							<ul className="subfield-list">
								<li className="empty"><span>&nbsp;</span><span>&nbsp;</span><span>&nbsp;</span><span>&nbsp;</span></li>
								<li className="add-item">

									<button

									>
										<Icon type="add" size="small" />
									</button>
								</li>
							</ul>
						</>
				}
			</div>
		</>
	);
};

export default Repeater;
