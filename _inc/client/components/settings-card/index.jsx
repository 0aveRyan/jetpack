/**
 * External dependencies
 */
import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'i18n-calypso';
import includes from 'lodash/includes';

/**
 * Internal dependencies
 */
import {
	PLAN_JETPACK_PREMIUM,
	PLAN_JETPACK_BUSINESS,
	FEATURE_SECURITY_SCANNING_JETPACK,
	FEATURE_SEO_TOOLS_JETPACK,
	FEATURE_VIDEO_HOSTING_JETPACK,
	FEATURE_GOOGLE_ANALYTICS_JETPACK,
	getPlanClass
} from 'lib/plans/constants';
import { getSiteRawUrl, userCanManageModules } from 'state/initial-state';
import {
	getSitePlan,
	isFetchingSiteData
} from 'state/site';
import SectionHeader from 'components/section-header';
import Banner from 'components/banner';
import Button from 'components/button';

export const SettingsCard = props => {
	const module = props.module
			? props.getModule( props.module )
			: false;

	// Non admin users only get Publicize, After the Deadline, and Post by Email settings. The UI doesn't have settings for Publicize.
	// composing is not a module slug but it's used so the Composing card is rendered to show AtD.
	if ( ! props.userCanManageModules && ! includes( [ 'composing', 'post-by-email' ], props.module ) ) {
		return <span />;
	}

	const isSaving = props.isSavingAnyOption(),
		feature = props.feature
			? props.feature
			: false,
		siteRawUrl = props.siteRawUrl;
	let header = props.header
			? props.header
			: '';

	if ( '' === header && module ) {
		header = module.name;
	}

	const getBanner = () => {
		const planClass = getPlanClass( props.sitePlan.product_slug );

		switch ( feature ) {
			case FEATURE_VIDEO_HOSTING_JETPACK:
				if (
					'is-premium-plan' === planClass ||
					'is-business-plan' === planClass
				) {
					return '';
				}

				return (
					<Banner
						title={ __( 'Host fast, high-quality, and ad-free video.' ) }
						callToAction={ __( 'Activate', { context: 'A caption for a button to activate a paid feature.' } ) }
						plan={ PLAN_JETPACK_PREMIUM }
						feature={ feature }
						href={ 'https://jetpack.com/redirect/?source=settings-video-premium&site=' + siteRawUrl }
					/>
				);

			case FEATURE_SECURITY_SCANNING_JETPACK:
				if ( 'is-business-plan' === planClass ) {
					return '';
				}

				if (
					'is-premium-plan' === planClass
				) {
					return (
						<Banner
							title={ __( 'Real-time site backups and automated threat resolution.' ) }
							plan={ PLAN_JETPACK_BUSINESS }
							callToAction={ __( 'Upgrade', { context: 'A caption for a button to upgrade an existing paid feature to a higher tier.' } ) }
							feature={ feature }
							href={ 'https://jetpack.com/redirect/?source=settings-security-pro&site=' + siteRawUrl }
						/>
					);
				}

				return (
					<Banner
						callToAction={ __( 'Activate', { context: 'A caption for a button to activate a paid feature.' } ) }
						title={ __( 'Protect against data loss, malware, and hacks.' ) }
						plan={ PLAN_JETPACK_PREMIUM }
						feature={ feature }
						href={ 'https://jetpack.com/redirect/?source=settings-security-premium&site=' + siteRawUrl }
					/>
				);

			case FEATURE_GOOGLE_ANALYTICS_JETPACK:
				if ( 'is-business-plan' === planClass ) {
					return '';
				}
				return (
					<Banner
						callToAction={ __( 'Activate', { context: 'A caption for a button to activate a paid feature.' } ) }
						title={ __( 'Hassle-free Google Analytics installation.' ) }
						plan={ PLAN_JETPACK_BUSINESS }
						feature={ feature }
						href={ 'https://jetpack.com/redirect/?source=settings-ga&site=' + siteRawUrl }
					/>
				);
			case FEATURE_SEO_TOOLS_JETPACK:
				if ( 'is-business-plan' === planClass ) {
					return '';
				}

				return (
					<Banner
						callToAction={ __( 'Activate', { context: 'A caption for a button to activate a paid feature.' } ) }
						title={ __( 'SEO tools help your content get found and shared.' ) }
						plan={ PLAN_JETPACK_BUSINESS }
						feature={ feature }
						href={ 'https://jetpack.com/redirect/?source=settings-seo&site=' + siteRawUrl }
					/>
				);

			default:
				return '';
		}
	};

	return (
		<form className="jp-form-settings-card">
			<SectionHeader label={ header }>
				{
					! props.hideButton && (
						<Button
							primary
							compact
							isSubmitting={ isSaving }
							onClick={ isSaving ? () => {} : props.onSubmit }
							disabled={ isSaving || ! props.isDirty() }>
							{
								isSaving
								? __( 'Saving…', { context: 'Button caption' } )
								: __( 'Save settings', { context: 'Button caption' } )
							}
						</Button>
					)
				}
			</SectionHeader>
			{ props.children }
			{ ! props.fetchingSiteData && getBanner( feature ) }
		</form>
	);
};

export default connect(
	( state ) => {
		return {
			sitePlan: getSitePlan( state ),
			fetchingSiteData: isFetchingSiteData( state ),
			siteRawUrl: getSiteRawUrl( state ),
			userCanManageModules: userCanManageModules( state )
		};
	}
)( SettingsCard );
