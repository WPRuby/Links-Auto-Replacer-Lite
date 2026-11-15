import { __ } from '@wordpress/i18n';
import { Notice } from '@wordpress/components';

import { useRoute } from './router';
import LinksList from './views/LinksList';
import LinkEditor from './views/LinkEditor';
import Settings from './views/Settings';

const { canSettings } = window.larAdmin;

export default function App() {
	const { view, id } = useRoute();

	switch ( view ) {
		case 'edit':
			return <LinkEditor linkId={ id } />;
		case 'settings':
			return canSettings ? <Settings /> : <Denied />;
		default:
			return <LinksList />;
	}
}

function Denied() {
	return (
		<Notice status="error" isDismissible={ false }>
			{ __(
				'Sorry, you are not allowed to access this page.',
				'links-auto-replacer'
			) }
		</Notice>
	);
}
