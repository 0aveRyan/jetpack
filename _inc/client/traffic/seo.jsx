/**
 * External dependencies
 */
import React from 'react';
import { translate as __ } from 'i18n-calypso';
import Card from 'components/card';
import analytics from 'lib/analytics';

/**
 * Internal dependencies
 */
import { FEATURE_SEO_TOOLS_JETPACK } from 'lib/plans/constants';
import { withModuleSettingsFormHelpers } from 'components/module-settings/with-module-settings-form-helpers';
import SettingsCard from 'components/settings-card';
import SettingsGroup from 'components/settings-group';

class SeoComponent extends React.Component {
	trackConfigureClick = () => {
		analytics.tracks.recordJetpackClick( 'configure-seo' );
	};

	render() {
		return (
			<SettingsCard
				{ ...this.props }
				header={ __( 'Search engine optimization', { context: 'Settings header' } ) }
				feature={ FEATURE_SEO_TOOLS_JETPACK }
				hideButton
			>
				<SettingsGroup
					disableInDevMode
					module={ { module: 'seo-tools' } }
					support={ {
						text: __(
							'Allows you to optimize your site and its content for better results in search engines.'
						),
						link: 'https://jetpack.com/support/seo-tools/',
					} }
				>
					<span>
						{ __(
							"Take control of the way search engines represent your site. With Jetpack's SEO tools you can preview how your content will look on popular search engines and change items like your site name and tagline without having to touch code.",
							{
								components: {
									a: <a href="https://jetpack.com/support/seo-tools/" />,
								},
							}
						) }
					</span>
				</SettingsGroup>
				{ ! this.props.isUnavailableInDevMode( 'seo-tools' ) && (
					<Card
						compact
						className="jp-settings-card__configure-link"
						onClick={ this.trackConfigureClick }
						href={ this.props.configureUrl }
					>
						{ __( 'Configure your SEO settings' ) }
					</Card>
				) }
			</SettingsCard>
		);
	}
}

export const SEO = withModuleSettingsFormHelpers( SeoComponent );
