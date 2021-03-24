import AddIcon from './AddIcon';
import BooleanIcon from './BooleanIcon';
import DateIcon from './DateIcon';
import DownArrow from './DownArrow';
import ErrorIcon from './ErrorIcon';
import MediaIcon from './MediaIcon';
import NumberIcon from './NumberIcon';
import OptionsIcon from './OptionsIcon';
import RepeaterIcon from './RepeaterIcon';
import ReorderIcon from './ReorderIcon';
import RichTextIcon from './RichTextIcon';
import TextIcon from './TextIcon';
import UpArrow from "./UpArrow";

export default function Icon({type}) {
	switch(type) {
		case 'add':
			return <AddIcon/>;
		case 'boolean':
			return <BooleanIcon/>;
		case 'date':
			return <DateIcon/>;
		case 'downarrow':
			return <DownArrow/>;
		case 'error':
			return <ErrorIcon/>;
		case 'media':
			return <MediaIcon/>;
		case 'number':
			return <NumberIcon/>;
		case 'options':
			return <OptionsIcon/>;
		case 'repeater':
			return <RepeaterIcon/>;
		case 'reorder':
			return <ReorderIcon/>;
		case 'richtext':
			return <RichTextIcon/>;
		case 'text':
			return <TextIcon/>;
		case 'uparrow':
			return <UpArrow/>;
		default:
			return '';
	}
}
