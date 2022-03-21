import { v4 as uuidv4 } from "uuid";

/**
 * Generates a unique ID for field initialization and keying.
 *
 * Exported here to allow mocking with Jest.
 *
 * @returns {string}.
 */
export function uuid() {
	return "field-" + uuidv4();
}
