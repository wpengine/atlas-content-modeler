import React, {useContext} from 'react';
import Icon from "../icons"
import Field from "./Field";
import { ModelsContext } from "../../ModelsContext";
import {
	getFieldOrder,
	getPositionAfter,
	getPreviousFieldId,
	getNextFieldId,
} from "../../queries";

const Repeater = ({fields={}, model, parent}) => {
	const {dispatch } = useContext(ModelsContext);
	const hasFields = Object.keys(fields)?.length > 0;
	const fieldOrder = getFieldOrder(fields);

	return (
		<>
			<div className="break">&nbsp;</div>
			<div className="repeater-fields">
				{hasFields ? (
					<ul className="subfield-list">
						{fieldOrder.map((id) => {
							const { type, position, open = false, editing = false } = fields[id];

							return (
								<Field
									key={id}
									id={id}
									model={model}
									type={type}
									open={open}
									editing={editing}
									data={fields[id]}
									// TODO: handle swapAction and setInfoTag when fixing repeater field reordering.
									// swapAction={swapFieldPositions}
									// setInfoTag={setInfoTag}
									previousFieldId={getPreviousFieldId(id, fields)}
									nextFieldId={getNextFieldId(id, fields)}
									position={position}
									positionAfter={getPositionAfter(id, fields)}
									parent={parent}
								/>
							);
						})}
					</ul>
				) : (
					<>
						<ul className="subfield-list">
							<li className="empty">
								<span>&nbsp;</span>
								<span>&nbsp;</span>
								<span>&nbsp;</span>
								<span>&nbsp;</span>
							</li>
							<li className="add-item">
								<button
									onClick={() =>
										dispatch({ type: "addField", position: 0, model, parent })
									}
								>
									<Icon type="add" size="small" />
								</button>
							</li>
						</ul>
					</>
				)}
			</div>
		</>
	);
};

export default Repeater;
