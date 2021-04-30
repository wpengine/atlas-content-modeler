import { generateSidebarMenuItem } from '../../includes/settings/js/src/utils';

describe('generateSidebarMenuItem tests', () => {
	const mock = {
		slug: 'cows',
		labels: { name: 'Cows' },
	};

	it('Renders a matching snapshot', () => {
		const markup = generateSidebarMenuItem(mock);
		expect(markup).toMatchSnapshot();
	});
});
