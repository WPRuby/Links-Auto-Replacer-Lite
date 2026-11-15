import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import {
	Button,
	Card,
	CardBody,
	CardHeader,
	ExternalLink,
	Notice,
	SelectControl,
	Spinner,
	TextControl,
	ToggleControl,
} from '@wordpress/components';

import { request } from '../api';
import Header from '../components/Header';

const { proUrl } = window.larAdmin;

export default function Settings() {
	const [ values, setValues ] = useState( null );
	const [ message, setMessage ] = useState( null );
	const [ saving, setSaving ] = useState( false );

	useEffect( () => {
		request( '/settings' )
			.then( setValues )
			.catch( ( error ) =>
				setMessage( { status: 'error', text: error.message } )
			);
	}, [] );

	if ( ! values ) {
		return message ? (
			<Notice status="error" isDismissible={ false }>
				{ message.text }
			</Notice>
		) : (
			<Spinner />
		);
	}

	const set = ( changes ) => setValues( { ...values, ...changes } );

	const save = async () => {
		setSaving( true );
		setMessage( null );
		try {
			setValues(
				await request( '/settings', { method: 'POST', data: values } )
			);
			setMessage( {
				status: 'success',
				text: __( 'Settings saved.', 'links-auto-replacer' ),
			} );
		} catch ( error ) {
			setMessage( { status: 'error', text: error.message } );
		}
		setSaving( false );
	};

	return (
		<>
			<Header title={ __( 'Settings', 'links-auto-replacer' ) } />

			{ message && (
				<Notice
					status={ message.status }
					onRemove={ () => setMessage( null ) }
				>
					{ message.text }
				</Notice>
			) }

			<div className="lar-settings">
				<div className="lar-settings__column">
					<Card>
						<CardHeader>
							<h2>{ __( 'Linking', 'links-auto-replacer' ) }</h2>
						</CardHeader>
						<CardBody>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __(
									'Link the keywords',
									'links-auto-replacer'
								) }
								help={ __(
									'Turn it off to stop linking keywords everywhere. Your links are kept.',
									'links-auto-replacer'
								) }
								checked={ values.enable }
								onChange={ ( enable ) => set( { enable } ) }
							/>
							<TextControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								type="number"
								min={ 0 }
								label={ __(
									'Links per keyword in a post',
									'links-auto-replacer'
								) }
								help={ __(
									'0 for no limit.',
									'links-auto-replacer'
								) }
								value={ String( values.per_post_limit ) }
								onChange={ ( value ) =>
									set( {
										per_post_limit: Math.max(
											0,
											parseInt( value, 10 ) || 0
										),
									} )
								}
							/>
							<TextControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								label={ __(
									'Excluded HTML tags',
									'links-auto-replacer'
								) }
								help={ __(
									'Keywords inside these tags are not linked, separated by commas, e.g. h1,h2,strong',
									'links-auto-replacer'
								) }
								value={ values.excluded_html_tags }
								onChange={ ( value ) =>
									set( { excluded_html_tags: value } )
								}
							/>
						</CardBody>
					</Card>

					<Card className="lar-pro-card">
						<CardHeader>
							<h2>
								{ __(
									'Affiliate Butler Pro',
									'links-auto-replacer'
								) }
							</h2>
						</CardHeader>
						<CardBody>
							<p>
								{ __(
									'Link keywords to your own posts, popups, images, videos and maps, see clicks per day, country and post, rotate short links between offers, and import or export your links.',
									'links-auto-replacer'
								) }
							</p>
							<ExternalLink href={ proUrl }>
								{ __(
									'About Affiliate Butler Pro',
									'links-auto-replacer'
								) }
							</ExternalLink>
						</CardBody>
					</Card>
				</div>

				<div className="lar-settings__column">
					<Card>
						<CardHeader>
							<h2>
								{ __(
									'New link defaults',
									'links-auto-replacer'
								) }
							</h2>
						</CardHeader>
						<CardBody>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __(
									'Nofollow',
									'links-auto-replacer'
								) }
								checked={ ! values.do_follow }
								onChange={ ( value ) =>
									set( { do_follow: ! value } )
								}
							/>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __(
									'Sponsored',
									'links-auto-replacer'
								) }
								checked={ values.sponsored }
								onChange={ ( sponsored ) =>
									set( { sponsored } )
								}
							/>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __(
									'Short link',
									'links-auto-replacer'
								) }
								checked={ values.shrink }
								onChange={ ( shrink ) => set( { shrink } ) }
							/>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __(
									'Case sensitive',
									'links-auto-replacer'
								) }
								checked={ values.is_sensitive }
								onChange={ ( value ) =>
									set( { is_sensitive: value } )
								}
							/>
							<SelectControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								label={ __( 'Open in', 'links-auto-replacer' ) }
								value={ values.open_in }
								options={ [
									{
										value: '_self',
										label: __(
											'Same window',
											'links-auto-replacer'
										),
									},
									{
										value: '_blank',
										label: __(
											'New window',
											'links-auto-replacer'
										),
									},
								] }
								onChange={ ( value ) =>
									set( { open_in: value } )
								}
							/>
						</CardBody>
					</Card>
				</div>
			</div>

			<Button variant="primary" onClick={ save } isBusy={ saving }>
				{ __( 'Save settings', 'links-auto-replacer' ) }
			</Button>
		</>
	);
}
