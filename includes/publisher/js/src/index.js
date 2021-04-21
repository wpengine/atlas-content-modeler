import { registerContentModelBlocks } from "./blocks/register";

const postType = wpeContentModel.postType;
const fields = wpeContentModel.models[postType]?.fields;

registerContentModelBlocks(fields, postType);
