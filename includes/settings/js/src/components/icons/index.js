import AddIcon from './AddIcon';
import BooleanIcon from './BooleanIcon';
import DateIcon from './DateIcon';
import ErrorIcon from './ErrorIcon';
import MediaIcon from './MediaIcon';
import NumberIcon from './NumberIcon';
import OptionsIcon from './OptionsIcon';
import ReorderIcon from './ReorderIcon';
import TextIcon from './TextIcon';

export default function Icon({type}) {
	switch(type) {
		case 'add':
			return <AddIcon/>;
		case 'boolean':
			return <BooleanIcon/>;
		case 'date':
			return <DateIcon/>;
		case 'error':
			return <ErrorIcon/>;
		case 'media':
			return <MediaIcon/>;
		case 'number':
			return <NumberIcon/>;
		case 'options':
			return <OptionsIcon/>;
		case 'reorder':
			return <ReorderIcon/>;
		case 'text':
			return <TextIcon/>;
	}
}
