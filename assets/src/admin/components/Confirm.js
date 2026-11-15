import { __ } from '@wordpress/i18n';
import { Button, Modal } from '@wordpress/components';

/**
 * Ask to confirm a destructive action.
 *
 * @param {Object}     props              The props.
 * @param {string}     props.title        The title.
 * @param {string}     props.confirmLabel The label of the confirm button.
 * @param {() => void} props.onConfirm    Called when confirmed.
 * @param {() => void} props.onCancel     Called when canceled.
 * @param {Element}    props.children     The message.
 * @return {Element} The modal.
 */
export default function Confirm( {
	title,
	confirmLabel,
	onConfirm,
	onCancel,
	children,
} ) {
	return (
		<Modal title={ title } onRequestClose={ onCancel } size="small">
			<p>{ children }</p>
			<div className="lar-modal-actions">
				<Button variant="tertiary" onClick={ onCancel }>
					{ __( 'Cancel', 'links-auto-replacer' ) }
				</Button>
				<Button variant="primary" isDestructive onClick={ onConfirm }>
					{ confirmLabel }
				</Button>
			</div>
		</Modal>
	);
}
