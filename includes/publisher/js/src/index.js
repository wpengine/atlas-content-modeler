import { registerContentModelBlock } from "./blocks/register";

const postType = wpeContentModel.postType;
const fields = wpeContentModel.models[postType]?.fields;

registerContentModelBlock(fields, postType);
