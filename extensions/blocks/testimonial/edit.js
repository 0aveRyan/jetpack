/**
 * External dependencies
 */
import { Component, Fragment } from '@wordpress/element';
import { RichText, MediaPlaceholder, BlockControls, AlignmentToolbar } from '@wordpress/editor';
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import { __ } from '../../utils/i18n';

export default class TestimonialEdit extends Component {
	onChangeName = value => void this.props.setAttributes( { name: value } );
	onChangeTitle = value => void this.props.setAttributes( { title: value } );
	onChangeContent = value => void this.props.setAttributes( { content: value } );
	onChangeAlign = value => void this.props.setAttributes( { align: value } );
	onChangeMedia = media =>
		void this.props.setAttributes( { mediaUrl: media.url, mediaId: media.id } );

	render() {
		const { attributes, className, isSelected } = this.props;
		const hasMedia = attributes.mediaUrl && attributes.mediaId;

		const alignmentClass = attributes.align && `is-aligned-${ attributes.align }`;

		return (
			<Fragment>
				<BlockControls>
					<AlignmentToolbar value={ attributes.align } onChange={ this.onChangeAlign } />
				</BlockControls>
				<div className={ classnames( className, alignmentClass ) }>
					<RichText
						tagName="div"
						value={ attributes.content }
						onChange={ this.onChangeContent }
						placeholder={
							// translators: placeholder text used for the quote
							__( 'Write testimonial…' )
						}
						wrapperClassName="wp-block-jetpack-testimonial__content"
					/>
					<div className="wp-block-jetpack-testimonial__author">
						{ hasMedia ? (
							<img
								src={ attributes.mediaUrl }
								width={ 50 }
								height={ 50 }
								alt={ attributes.name || '' }
								className="wp-block-jetpack-testimonial__media"
							/>
						) : (
							isSelected && (
								<MediaPlaceholder
									onSelect={ this.onChangeMedia }
									allowedTypes={ [ 'image' ] }
									labels={ { title: 'The Image' } }
								/>
							)
						) }
						{ ( isSelected || attributes.name || attributes.title ) && (
							<div className="wp-block-jetpack-testimonial__meta">
								{ ( isSelected || attributes.name ) && (
									<RichText
										tagName="div"
										value={ attributes.name }
										onChange={ this.onChangeName }
										placeholder={ 'Write name…' }
										wrapperClassName="wp-block-jetpack-testimonial__name"
									/>
								) }
								{ ( isSelected || attributes.title ) && (
									<RichText
										tagName="div"
										value={ attributes.title }
										onChange={ this.onChangeTitle }
										placeholder={ 'Write title…' }
										wrapperClassName="wp-block-jetpack-testimonial__title"
									/>
								) }
							</div>
						) }
					</div>
				</div>
			</Fragment>
		);
	}
}
