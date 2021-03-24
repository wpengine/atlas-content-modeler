/**
 * Queries against existing global models or fields data.
 * These are pure functions that make no HTTP requests.
 */

/**
 * Gives an array of field IDs in the order they should appear based on
 * their position property.
 *
 * @param {Object} fields Fields with id, position (and other) properties.
 * @return {Array} Field ids in position order, lowest position first.
 * @example
 * ```js
 * let ordered = getFieldOrder({123: {id: 123, position: 10000}, 456: {id: 456, position: 0}});
 * ordered === [ 456, 123 ];
 * ```
 */
export function getFieldOrder(fields) {
	if (typeof fields !== 'object') {
		return [];
	}

	return Object
		.keys(fields)
		.map((key) => {
			return {
				position: fields[key]['position'],
				id: fields[key]['id'],
			}
		})
		.sort((field1, field2) => field1.position - field2.position)
		.map(field => field.id);
}

/**
 * Gets the field after `id` without wrapping from last to first.
 *
 * @param {Number} id Id of current field
 * @param {Object} fields Fields with id, position (and other) properties.
 * @return {Number} Id of next field. -1 means no next item.
 * @example
 * ```js
 * let next = getNextFieldId(456, {123: {id: 123, position: 10000}, 456: {id: 456, position: 0}});
 * next === 123;
 * let next2 = getNextFieldId(123, {123: {id: 123, position: 10000}, 456: {id: 456, position: 0}});
 * next2 === -1; // 123 is the last field and has no next field.
 * ```
 */
export function getNextFieldId(id, fields) {
	const fieldOrder = getFieldOrder(fields);
	const myIndex = fieldOrder?.indexOf(id);
	if (myIndex < 0) return -1; // No such id found.
	if (myIndex === fieldOrder.length - 1) return -1; // No item after last.
	return fieldOrder[myIndex + 1];
}

/**
 * Gets the field before `id` without wrapping from first to last.
 *
 * @param {Number} id Id of current field
 * @param {Object} fields Fields with id, position (and other) properties.
 * @return {Number} Id of next field. -1 means no previous item.
 * @example
 * ```js
 * let previous = getPreviousFieldId(123, {123: {id: 123, position: 10000}, 456: {id: 456, position: 0}});
 * previous === 456;
 * let previous2 = getPreviousFieldId(456, {123: {id: 123, position: 10000}, 456: {id: 456, position: 0}});
 * previous2 === -1; //456 is the first field and has no previous field.
 * ```
 */
export function getPreviousFieldId(id, fields) {
	const fieldOrder = getFieldOrder(fields);
	const myIndex = fieldOrder?.indexOf(id);
	if (myIndex < 0) return -1; // No such id found.
	if (myIndex === 0) return -1; // No item before first.
	return fieldOrder[myIndex - 1];
}

/**
 * Gets the position a new field would need to have to be placed after the
 * field with `id`. So that new fields can be added between two fields.
 *
 * Instead of incrementing field positions by 1, increment with a gap.
 * This allows new fields to be inserted between others without
 * affecting the position values of surrounding fields.
 *
 * @param {Number} id Id of current field
 * @param {Object} fields Fields with id, position (and other) properties.
 * @returns {Number}
 * @example
 * ```js
 * let example1 = getPositionAfter(123, {123: {id: 123, position: 10}});
 * example1 === 10010;
 * let example2 = getPositionAfter(123, {123: {id: 123, position: 10}, 456: {id: 456, position: 20}});
 * example2 === 15;
 * ```
 */
export function getPositionAfter(id, fields) {
	const POSITION_GAP = 10000;
	const fieldOrder = getFieldOrder(fields);

	const myOrder = fieldOrder.indexOf(id);
	const myPosition = parseFloat(fields[id]?.position);

	// Last field. Just add the gap.
	if (myOrder + 1 === Object.keys(fieldOrder)?.length) {
		return myPosition + POSITION_GAP;
	}

	// Otherwise add half the difference between my position and the next field's position.
	const nextFieldId = fieldOrder[myOrder+1];
	const nextFieldPosition = parseFloat(fields[nextFieldId]?.position);

	if (nextFieldPosition) {
		return (myPosition + nextFieldPosition) / 2;
	}

	return 0;
}

/**
 * Takes a flat list of parent and child fields and returns root fields (fields
 * with no 'parent' property) with child fields moved to a 'subfields' property.
 *
 * Used to remove repeater subfields from the main fields object. A repeater
 * field is a parent field and its subfields are children.
 *
 * @param {Object} fields Fields with optional 'parent' properties.
 */
export function getRootFields(fields) {
	if (typeof fields !== 'object') {
		return {};
	}

	const createFieldTree = fields => {
		const hashTable = Object.create(null);
		fields.forEach(field => hashTable[field.id] = {...field, subfields: {}});
		const result = {};
		fields.forEach(field => {
			if(field.parent) {
				hashTable[field.parent].subfields[field.id] = hashTable[field.id]
			} else {
				result[field.id] = hashTable[field.id];
			}
		});
		return result;
	};

	return createFieldTree(Object.values(fields));
}
