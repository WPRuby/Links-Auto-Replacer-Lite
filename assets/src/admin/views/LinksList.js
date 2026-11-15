import { __, _n, sprintf } from '@wordpress/i18n';
import { useCallback, useEffect, useState } from '@wordpress/element';
import { Button, Notice, SearchControl, Spinner } from '@wordpress/components';
import { useDebounce } from '@wordpress/compose';
import { dateI18n, getSettings } from '@wordpress/date';

import { fetchLinks, request } from '../api';
import { navigate, ViewLink } from '../router';
import Confirm from '../components/Confirm';
import Header from '../components/Header';

const { proUrl } = window.larAdmin;

const PER_PAGE = 20;

const STATUSES = [
	{ value: 'any', label: __( 'All', 'links-auto-replacer' ) },
	{ value: 'publish', label: __( 'Published', 'links-auto-replacer' ) },
	{ value: 'draft', label: __( 'Drafts', 'links-auto-replacer' ) },
	{ value: 'trash', label: __( 'Trash', 'links-auto-replacer' ) },
];

export default function LinksList() {
	const [ search, setSearch ] = useState( '' );
	const [ query, setQuery ] = useState( {
		page: 1,
		search: '',
		status: 'any',
	} );
	const [ result, setResult ] = useState( null );
	const [ error, setError ] = useState( null );
	const [ notice, setNotice ] = useState( null );
	const [ deleting, setDeleting ] = useState( null );

	const setSearchQuery = useDebounce(
		( value ) =>
			setQuery( ( current ) => ( {
				...current,
				search: value,
				page: 1,
			} ) ),
		300
	);

	const load = useCallback( () => {
		setError( null );
		fetchLinks( { ...query, per_page: PER_PAGE } )
			.then( setResult )
			.catch( setError );
	}, [ query ] );

	useEffect( load, [ load ] );

	const update = async ( link, action ) => {
		setNotice( null );
		try {
			if ( action === 'trash' ) {
				await request( `/links/${ link.id }`, { method: 'DELETE' } );
				setNotice(
					__( 'Link moved to the Trash.', 'links-auto-replacer' )
				);
			} else if ( action === 'restore' ) {
				await request( `/links/${ link.id }`, {
					method: 'PUT',
					data: { status: 'draft' },
				} );
				setNotice(
					__( 'Link restored as a draft.', 'links-auto-replacer' )
				);
			} else if ( action === 'delete' ) {
				await request( `/links/${ link.id }?force=true`, {
					method: 'DELETE',
				} );
				setNotice(
					__( 'Link deleted permanently.', 'links-auto-replacer' )
				);
			}
			load();
		} catch ( e ) {
			setError( e );
		}
	};

	return (
		<>
			<Header title={ __( 'Auto Links', 'links-auto-replacer' ) }>
				<Button variant="primary" onClick={ () => navigate( 'edit' ) }>
					{ __( 'Add New Auto Link', 'links-auto-replacer' ) }
				</Button>
			</Header>

			<p className="lar-intro">
				{ __(
					'An auto link is one or more keywords and the URL they link to. Once published, the keywords are linked in every post and page where they appear.',
					'links-auto-replacer'
				) }
			</p>

			{ notice && (
				<Notice status="success" onRemove={ () => setNotice( null ) }>
					{ notice }
				</Notice>
			) }
			{ error && (
				<Notice status="error" onRemove={ () => setError( null ) }>
					{ error.message }
				</Notice>
			) }

			<div className="lar-toolbar">
				<div className="lar-filters" role="group">
					{ STATUSES.map( ( status ) => (
						<Button
							key={ status.value }
							variant={
								query.status === status.value
									? 'primary'
									: 'tertiary'
							}
							size="compact"
							onClick={ () =>
								setQuery( {
									...query,
									status: status.value,
									page: 1,
								} )
							}
						>
							{ status.label }
						</Button>
					) ) }
				</div>
				<SearchControl
					__nextHasNoMarginBottom
					label={ __( 'Search links', 'links-auto-replacer' ) }
					value={ search }
					onChange={ ( value ) => {
						setSearch( value );
						setSearchQuery( value );
					} }
				/>
			</div>

			{ ! result && ! error && <Spinner /> }
			{ result && (
				<LinksTable
					links={ result.items }
					trash={ query.status === 'trash' }
					onAction={ ( link, action ) =>
						action === 'delete'
							? setDeleting( link )
							: update( link, action )
					}
				/>
			) }
			{ result && result.totalPages > 1 && (
				<div className="lar-pagination">
					<span>
						{ sprintf(
							/* translators: %d: number of links. */
							_n(
								'%d link',
								'%d links',
								result.total,
								'links-auto-replacer'
							),
							result.total
						) }
					</span>
					<Button
						variant="secondary"
						size="compact"
						disabled={ query.page <= 1 }
						onClick={ () =>
							setQuery( { ...query, page: query.page - 1 } )
						}
					>
						{ __( 'Previous', 'links-auto-replacer' ) }
					</Button>
					<span>
						{ sprintf(
							/* translators: 1: current page, 2: number of pages. */
							__( 'Page %1$d of %2$d', 'links-auto-replacer' ),
							query.page,
							result.totalPages
						) }
					</span>
					<Button
						variant="secondary"
						size="compact"
						disabled={ query.page >= result.totalPages }
						onClick={ () =>
							setQuery( { ...query, page: query.page + 1 } )
						}
					>
						{ __( 'Next', 'links-auto-replacer' ) }
					</Button>
				</div>
			) }

			{ deleting && (
				<Confirm
					title={ __( 'Delete the link?', 'links-auto-replacer' ) }
					confirmLabel={ __(
						'Delete permanently',
						'links-auto-replacer'
					) }
					onConfirm={ () => {
						update( deleting, 'delete' );
						setDeleting( null );
					} }
					onCancel={ () => setDeleting( null ) }
				>
					{ sprintf(
						/* translators: %s: the keywords of the link. */
						__(
							'Delete the link "%s" and its click count? This cannot be undone.',
							'links-auto-replacer'
						),
						deleting.keywords.join( ', ' )
					) }
				</Confirm>
			) }
		</>
	);
}

function LinksTable( { links, trash, onAction } ) {
	if ( ! links.length ) {
		return (
			<p className="lar-empty">
				{ __( 'No links found.', 'links-auto-replacer' ) }
			</p>
		);
	}

	const dateFormat = getSettings().formats.date;

	return (
		<table className="wp-list-table widefat fixed striped lar-links-table">
			<thead>
				<tr>
					<th className="column-primary">
						{ __( 'Keywords', 'links-auto-replacer' ) }
					</th>
					<th>{ __( 'Links to', 'links-auto-replacer' ) }</th>
					<th className="lar-col-number">
						{ __( 'Clicks', 'links-auto-replacer' ) }
					</th>
					<th>{ __( 'Date', 'links-auto-replacer' ) }</th>
				</tr>
			</thead>
			<tbody>
				{ links.map( ( link ) => (
					<tr key={ link.id }>
						<td className="column-primary">
							<strong>
								{ trash || link.pro_type ? (
									link.keywords.join( ' | ' )
								) : (
									<ViewLink
										view="edit"
										id={ link.id }
										className="row-title"
									>
										{ link.keywords.join( ' | ' ) ||
											__(
												'(no keywords)',
												'links-auto-replacer'
											) }
									</ViewLink>
								) }
								{ link.status === 'draft' &&
									! trash &&
									' — ' +
										__( 'Draft', 'links-auto-replacer' ) }
							</strong>
							{ link.pro_type && (
								<span className="lar-badge">
									{ __(
										'Affiliate Butler Pro',
										'links-auto-replacer'
									) }
								</span>
							) }
							<RowActions
								link={ link }
								trash={ trash }
								onAction={ onAction }
							/>
						</td>
						<td className="lar-col-url">
							<Destination link={ link } />
						</td>
						<td className="lar-col-number">
							{ link.short_link ? link.clicks : '—' }
						</td>
						<td>{ dateI18n( dateFormat, link.date ) }</td>
					</tr>
				) ) }
			</tbody>
		</table>
	);
}

/**
 * Where a link goes: its short link and URL.
 *
 * @param {Object} props      The props.
 * @param {Object} props.link The link.
 * @return {Element} The destination.
 */
function Destination( { link } ) {
	if ( link.pro_type ) {
		return (
			<span className="lar-muted">
				{ __(
					'A link type of Affiliate Butler Pro, edit it there.',
					'links-auto-replacer'
				) }
			</span>
		);
	}
	return (
		<>
			<code>{ link.short_link || link.target_url || '—' }</code>
			{ link.short_link && (
				<div className="lar-muted">{ link.target_url }</div>
			) }
		</>
	);
}

function RowActions( { link, trash, onAction } ) {
	if ( trash ) {
		return (
			<div className="lar-row-actions">
				<Button
					variant="link"
					onClick={ () => onAction( link, 'restore' ) }
				>
					{ __( 'Restore', 'links-auto-replacer' ) }
				</Button>
				<Button
					variant="link"
					isDestructive
					onClick={ () => onAction( link, 'delete' ) }
				>
					{ __( 'Delete permanently', 'links-auto-replacer' ) }
				</Button>
			</div>
		);
	}

	return (
		<div className="lar-row-actions">
			{ link.pro_type ? (
				<a href={ proUrl } target="_blank" rel="noreferrer">
					{ __(
						'About Affiliate Butler Pro',
						'links-auto-replacer'
					) }
				</a>
			) : (
				<ViewLink view="edit" id={ link.id }>
					{ __( 'Edit', 'links-auto-replacer' ) }
				</ViewLink>
			) }
			<Button
				variant="link"
				isDestructive
				onClick={ () => onAction( link, 'trash' ) }
			>
				{ __( 'Trash', 'links-auto-replacer' ) }
			</Button>
		</div>
	);
}
