/**
 * External dependencies
 */
import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'i18n-calypso';
import Button from 'components/button';
import SimpleNotice from 'components/notice';
import NoticeAction from 'components/notice/notice-action';

/**
 * Internal dependencies
 */
import { getSiteRawUrl, getSiteAdminUrl } from 'state/initial-state';
import QuerySitePlugins from 'components/data/query-site-plugins';
import QueryVaultPressData from 'components/data/query-vaultpress-data';
import QueryAkismetData from 'components/data/query-akismet-data';
import { isDevMode } from 'state/connection';
import {
	isFetchingPluginsData,
	isPluginActive,
	isPluginInstalled
} from 'state/site/plugins';
import {
	getVaultPressScanThreatCount as _getVaultPressScanThreatCount,
	getVaultPressData as _getVaultPressData,
	getAkismetData as _getAkismetData
} from 'state/at-a-glance';
import {
	getSitePlan,
	isFetchingSiteData
} from 'state/site';

const ProStatus = React.createClass( {
	propTypes: {
		isCompact: React.PropTypes.bool,
		proFeature: React.PropTypes.string
	},

	getDefaultProps: function() {
		return {
			isCompact: true,
			proFeature: ''
		};
	},

	getProActions( type ) {
		let status = '',
			message = false,
			action = false,
			actionUrl = '';
		switch ( type ) {
			case 'threats':
				status = 'is-error';
				if ( this.props.isCompact ) {
					action = __( 'FIX THREATS', { context: 'A caption for a small button to fix security issues.' } );
				} else {
					message = __( 'Threats found!', { context: 'Short warning message about new threats found.' } );
					action = __( 'FIX', { context: 'A caption for a small button to fix security issues.' } );
				}
				actionUrl = 'https://dashboard.vaultpress.com/';
				break;
			case 'free':
			case 'personal':
				status = 'is-warning';
				if ( ! this.props.isCompact ) {
					message = __( 'No scanning', { context: 'Short warning message about site having no security scan.' } );
				}
				action = __( 'Upgrade', { context: 'Caption for a button to purchase a paid feature.' } );
				actionUrl = 'https://jetpack.com/redirect/?source=upgrade&site=' + this.props.siteRawUrl;
				break;
			case 'secure':
				status = 'is-success';
				message = __( 'Secure', { context: 'Short message informing user that the site is secure.' } );
				break;
			case 'invalid_key':
				status = 'is-warning';
				action = __( 'Invalid key', { context: 'Short warning message about an invalid key being used for Akismet.' } );
				actionUrl = this.props.siteAdminUrl + 'admin.php?page=akismet-key-config';
				break;
			case 'active':
				return <span className="jp-dash-item__active-label">{ __( 'ACTIVE' ) }</span>;
		}
		return (
			<SimpleNotice
				showDismiss={ false }
				status={ status }
				isCompact={ true }
			>
				{
					message
				}
				{
					action && <NoticeAction href={ actionUrl }>{ action }</NoticeAction>
				}
			</SimpleNotice>
		);
	},

	render() {
		const sitePlan = this.props.sitePlan(),
			vpData = this.props.getVaultPressData(),
			pluginSlug = 'scan' === this.props.proFeature || 'backups' === this.props.proFeature || 'vaultpress' === this.props.proFeature
				? 'vaultpress/vaultpress.php'
				: 'akismet/akismet.php';

		const hasPersonal = /jetpack_personal*/.test( sitePlan.product_slug ),
			hasFree = /jetpack_free*/.test( sitePlan.product_slug ),
			hasBackups = (
				'undefined' !== typeof vpData.data &&
				'undefined' !== typeof vpData.data.features &&
				'undefined' !== typeof vpData.data.features.backups &&
				vpData.data.features.backups
			),
			hasScan = (
				'undefined' !== typeof vpData.data &&
				'undefined' !== typeof vpData.data.features &&
				'undefined' !== typeof vpData.data.features.security &&
				vpData.data.features.security
			);

		const getStatus = ( feature, active, installed ) => {
			if ( this.props.isDevMode ) {
				return '';
			}

			if ( 'backups' === feature ) {
				if ( hasFree && ! hasBackups ) {
					if ( this.props.isCompact ) {
						return this.getProActions( 'free' );
					}
				}
			}

			if ( 'scan' === feature ) {
				if ( ( hasFree || hasPersonal ) && ! hasScan ) {
					if ( this.props.isCompact ) {
						return this.getProActions( 'free' );
					}
					return '';
				}
				if ( 'N/A' !== vpData ) {
					const threatsCount = this.props.getScanThreats();
					if ( 0 !== threatsCount ) {
						return this.getProActions( 'threats' );
					}
					if ( 0 === threatsCount ) {
						return this.getProActions( 'secure' );
					}
				}
			}

			if ( 'akismet' === feature ) {
				if ( hasFree ) {
					if ( this.props.isCompact ) {
						return this.getProActions( 'free' );
					}
					return '';
				}

				const akismetData = this.props.getAkismetData();
				if ( 'invalid_key' === akismetData ) {
					return this.getProActions( 'invalid_key' );
				}
			}

			if ( sitePlan.product_slug ) {
				if ( ! hasFree ) {
					if ( active && installed ) {
						return this.getProActions( 'active' );
					}

					return (
						<Button
							compact={ true }
							primary={ true }
							href={ `https://wordpress.com/plugins/setup/${ this.props.siteRawUrl }?only=${ feature }` }
						>
							{ __( 'Set up', { context: 'Caption for a button to set up a feature.' } ) }
						</Button>
					);
				}
			}

			return '';
		};

		return (
			<div>
				<QuerySitePlugins />
				<QueryAkismetData />
				<QueryVaultPressData />
				{ getStatus(
					this.props.proFeature,
					this.props.pluginActive( pluginSlug ),
					this.props.pluginInstalled( pluginSlug )
				) }
			</div>
		);
	}
} );

export default connect(
	( state ) => {
		return {
			siteRawUrl: getSiteRawUrl( state ),
			siteAdminUrl: getSiteAdminUrl( state ),
			getScanThreats: () => _getVaultPressScanThreatCount( state ),
			getVaultPressData: () => _getVaultPressData( state ),
			getAkismetData: () => _getAkismetData( state ),
			sitePlan: () => getSitePlan( state ),
			fetchingPluginsData: isFetchingPluginsData( state ),
			pluginActive: ( plugin_slug ) => isPluginActive( state, plugin_slug ),
			pluginInstalled: ( plugin_slug ) => isPluginInstalled( state, plugin_slug ),
			isDevMode: isDevMode( state ),
			fetchingSiteData: isFetchingSiteData( state )
		};
	}
)( ProStatus );
