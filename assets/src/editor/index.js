/**
 * The panel of the block editor: disable the auto links of a post.
 */
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import {
	PluginDocumentSettingPanel,
	store as editorStore,
} from '@wordpress/editor';
import { ToggleControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';

const META = 'lar_disabled';

function DisablePanel() {
	const disabled = useSelect(
		( select ) =>
			( select( editorStore ).getEditedPostAttribute( 'meta' ) || {} )[
				META
			] === 'on',
		[]
	);
	const { editPost } = useDispatch( editorStore );

	return (
		<PluginDocumentSettingPanel
			name="lar-disable"
			title={ __( 'Keywords to Links Converter', 'links-auto-replacer' ) }
		>
			<ToggleControl
				__nextHasNoMarginBottom
				label={ __(
					'Disable auto links in this post',
					'links-auto-replacer'
				) }
				checked={ disabled }
				onChange={ ( value ) =>
					editPost( { meta: { [ META ]: value ? 'on' : 'off' } } )
				}
			/>
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'lar-disable', { render: DisablePanel } );
