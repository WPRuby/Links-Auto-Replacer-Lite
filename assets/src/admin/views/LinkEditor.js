import { __, _n, sprintf } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import {
	Button,
	Card,
	CardBody,
	CardHeader,
	ExternalLink,
	FormTokenField,
	Notice,
	SelectControl,
	Spinner,
	TextControl,
	ToggleControl,
} from '@wordpress/components';

import { request } from '../api';
import { navigate, ViewLink } from '../router';
import Header from '../components/Header';

const { defaults, defaultShortLink, shortLinkUrl, proUrl } = window.larAdmin;

export default function LinkEditor( { linkId } ) {
	const [ link, setLink ] = useState( linkId ? null : { ...defaults } );
	const [ shortLink, setShortLink ] = useState(
		linkId ? false : defaultShortLink
	);
	const [ errors, setErrors ] = useState( {} );
	const [ message, setMessage ] = useState( null );
	const [ saving, setSaving ] = useState( false );

	useEffect( () => {
		if ( ! linkId ) {
			return;
		}
		request( `/links/${ linkId }` )
			.then( ( data ) => {
				setLink( data );
				setShortLink( data.slug !== '' );
			} )
			.catch( ( error ) =>
				setMessage( { status: 'error', text: error.message } )
			);
	}, [ linkId ] );

	if ( ! link ) {
		return message ? (
			<Notice status="error" isDismissible={ false }>
				{ message.text }
			</Notice>
		) : (
			<Spinner />
		);
	}

	if ( link.pro_type ) {
		return (
			<Notice status="warning" isDismissible={ false }>
				{ __(
					'This link was made with Affiliate Butler Pro. Edit it with Affiliate Butler Pro.',
					'links-auto-replacer'
				) }
			</Notice>
		);
	}

	const set = ( changes ) => setLink( { ...link, ...changes } );

	const save = async ( status ) => {
		setSaving( true );
		setErrors( {} );
		setMessage( null );
		try {
			const saved = await request(
				linkId ? `/links/${ linkId }` : '/links',
				{
					method: linkId ? 'PUT' : 'POST',
					data: {
						...link,
						status,
						slug: shortLink ? link.slug : '',
						short_link_enabled: shortLink,
					},
				}
			);
			setLink( saved );
			setShortLink( saved.slug !== '' );
			setMessage( {
				status: 'success',
				text:
					status === 'publish'
						? __( 'Link saved.', 'links-auto-replacer' )
						: __( 'Draft saved.', 'links-auto-replacer' ),
			} );
			if ( ! linkId ) {
				navigate( 'edit', saved.id, true );
			}
		} catch ( error ) {
			setErrors( error.fields );
			setMessage( { status: 'error', text: error.message } );
		}
		setSaving( false );
	};

	return (
		<>
			<Header
				title={
					linkId
						? __( 'Edit Auto Link', 'links-auto-replacer' )
						: __( 'Add New Auto Link', 'links-auto-replacer' )
				}
				back={
					<ViewLink view="links">
						{ __( '← All Auto Links', 'links-auto-replacer' ) }
					</ViewLink>
				}
			/>

			{ message && (
				<Notice
					status={ message.status }
					onRemove={ () => setMessage( null ) }
				>
					{ message.text }
					{ Object.values( errors ).length > 0 && (
						<ul>
							{ Object.values( errors ).map( ( text ) => (
								<li key={ text }>{ text }</li>
							) ) }
						</ul>
					) }
				</Notice>
			) }

			<div className="lar-editor">
				<div className="lar-editor__main">
					<Card>
						<CardHeader>
							<h2>{ __( 'Keywords', 'links-auto-replacer' ) }</h2>
						</CardHeader>
						<CardBody>
							<FormTokenField
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								label={ __(
									'Keywords to link',
									'links-auto-replacer'
								) }
								value={ link.keywords }
								onChange={ ( keywords ) =>
									set( {
										keywords: keywords.map( ( keyword ) =>
											typeof keyword === 'string'
												? keyword
												: keyword.value
										),
									} )
								}
								tokenizeOnSpace={ false }
							/>
							<FieldHelp
								error={ errors.keywords }
								help={ __(
									'Every keyword is linked in your posts.',
									'links-auto-replacer'
								) }
							/>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __(
									'Case sensitive',
									'links-auto-replacer'
								) }
								help={ __(
									'Link the keywords only when they are written with the same letter case.',
									'links-auto-replacer'
								) }
								checked={ link.case_sensitive }
								onChange={ ( value ) =>
									set( { case_sensitive: value } )
								}
							/>
						</CardBody>
					</Card>

					<Card>
						<CardHeader>
							<h2>{ __( 'Link', 'links-auto-replacer' ) }</h2>
						</CardHeader>
						<CardBody>
							<TextControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								type="url"
								label={ __( 'URL', 'links-auto-replacer' ) }
								placeholder="https://"
								value={ link.target_url }
								onChange={ ( url ) =>
									set( { target_url: url } )
								}
							/>
							<FieldHelp error={ errors.target_url } />
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __(
									'Short link',
									'links-auto-replacer'
								) }
								help={ __(
									'Use a short link on your site that redirects to the URL, hides it, and counts its clicks.',
									'links-auto-replacer'
								) }
								checked={ shortLink }
								onChange={ setShortLink }
							/>
							{ shortLink && (
								<>
									<TextControl
										__next40pxDefaultSize
										__nextHasNoMarginBottom
										label={ __(
											'Slug',
											'links-auto-replacer'
										) }
										value={ link.slug }
										onChange={ ( slug ) => set( { slug } ) }
										help={
											link.slug
												? shortLinkUrl + link.slug
												: __(
														'Leave it empty to get one.',
														'links-auto-replacer'
													)
										}
									/>
									<FieldHelp error={ errors.slug } />
								</>
							) }
						</CardBody>
					</Card>
				</div>

				<div className="lar-editor__side">
					<Card>
						<CardHeader>
							<h2>{ __( 'Save', 'links-auto-replacer' ) }</h2>
						</CardHeader>
						<CardBody>
							<p className="lar-muted">
								{ link.status === 'publish'
									? __(
											'Published: the keywords are linked.',
											'links-auto-replacer'
										)
									: __(
											'Draft: the keywords are not linked yet.',
											'links-auto-replacer'
										) }
							</p>
							<div className="lar-save-actions">
								<Button
									variant="secondary"
									onClick={ () => save( 'draft' ) }
									disabled={ saving }
								>
									{ __(
										'Save draft',
										'links-auto-replacer'
									) }
								</Button>
								<Button
									variant="primary"
									onClick={ () => save( 'publish' ) }
									isBusy={ saving }
									disabled={ saving }
								>
									{ link.status === 'publish' && linkId
										? __( 'Update', 'links-auto-replacer' )
										: __(
												'Publish',
												'links-auto-replacer'
											) }
								</Button>
							</div>
						</CardBody>
					</Card>

					{ linkId > 0 && link.short_link && (
						<Card>
							<CardHeader>
								<h2>
									{ __( 'Clicks', 'links-auto-replacer' ) }
								</h2>
							</CardHeader>
							<CardBody>
								<p className="lar-big-number">
									{ link.clicks }
								</p>
								<p className="lar-muted">
									{ sprintf(
										/* translators: %d: number of clicks. */
										_n(
											'%d visit of the short link, bots not counted.',
											'%d visits of the short link, bots not counted.',
											link.clicks,
											'links-auto-replacer'
										),
										link.clicks
									) }{ ' ' }
									<ExternalLink href={ proUrl }>
										{ __(
											'Clicks per day, country and post with Affiliate Butler Pro',
											'links-auto-replacer'
										) }
									</ExternalLink>
								</p>
							</CardBody>
						</Card>
					) }

					<Card>
						<CardHeader>
							<h2>{ __( 'Options', 'links-auto-replacer' ) }</h2>
						</CardHeader>
						<CardBody>
							<SelectControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								label={ __( 'Open in', 'links-auto-replacer' ) }
								value={ link.open_in }
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
								onChange={ ( openIn ) =>
									set( { open_in: openIn } )
								}
							/>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __(
									'Nofollow',
									'links-auto-replacer'
								) }
								help={ __(
									'Ask search engines not to follow the link, recommended for affiliate links.',
									'links-auto-replacer'
								) }
								checked={ link.nofollow }
								onChange={ ( value ) =>
									set( { nofollow: value } )
								}
							/>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __(
									'Sponsored',
									'links-auto-replacer'
								) }
								help={ __(
									'Mark it as a paid or affiliate link (rel="sponsored"), as Google asks.',
									'links-auto-replacer'
								) }
								checked={ link.sponsored }
								onChange={ ( value ) =>
									set( { sponsored: value } )
								}
							/>
						</CardBody>
					</Card>

					<Card className="lar-pro-card">
						<CardHeader>
							<h2>
								{ __(
									'Upgrade to Pro',
									'links-auto-replacer'
								) }
							</h2>
						</CardHeader>
						<CardBody>
							<p>
								{ __(
									'Affiliate Butler Pro does more with the same links:',
									'links-auto-replacer'
								) }
							</p>
							<ul>
								<li>
									{ __(
										'Link keywords to your own posts and pages',
										'links-auto-replacer'
									) }
								</li>
								<li>
									{ __(
										'Popups, images, videos and maps',
										'links-auto-replacer'
									) }
								</li>
								<li>
									{ __(
										'Clicks per day, country and post',
										'links-auto-replacer'
									) }
								</li>
								<li>
									{ __(
										'URL rotation and split tests',
										'links-auto-replacer'
									) }
								</li>
								<li>
									{ __(
										'QR codes, import and export',
										'links-auto-replacer'
									) }
								</li>
							</ul>
							<Button
								variant="secondary"
								href={ proUrl }
								target="_blank"
								rel="noreferrer"
							>
								{ __(
									'Get Affiliate Butler Pro',
									'links-auto-replacer'
								) }
							</Button>
						</CardBody>
					</Card>
				</div>
			</div>
		</>
	);
}

function FieldHelp( { error, help } ) {
	if ( error ) {
		return <p className="lar-field-error">{ error }</p>;
	}
	return help ? <p className="lar-field-help">{ help }</p> : null;
}
