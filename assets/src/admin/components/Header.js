/**
 * The title of a screen, with its actions.
 *
 * @param {Object}  props          The props.
 * @param {string}  props.title    The title.
 * @param {Element} props.back     A link back, shown before the title.
 * @param {Element} props.children The actions.
 * @return {Element} The header.
 */
export default function Header( { title, back, children } ) {
	return (
		<div className="lar-header">
			{ back && <div className="lar-header__back">{ back }</div> }
			<h1 className="wp-heading-inline">{ title }</h1>
			{ children && (
				<div className="lar-header__actions">{ children }</div>
			) }
			<hr className="wp-header-end" />
		</div>
	);
}
