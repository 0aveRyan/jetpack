/**
 * External dependencies
 */
import React from 'react';
import { connect } from 'react-redux';
import FoldableCard from 'components/foldable-card';
import { ModuleToggle } from 'components/module-toggle';
import forEach from 'lodash/forEach';
import Button from 'components/button';
import Gridicon from 'components/gridicon';
import Collection from 'components/search/search-collection.jsx';
import { translate as __ } from 'i18n-calypso';
import SimpleNotice from 'components/notice';

/**
 * Internal dependencies
 */
import QuerySitePlugins from 'components/data/query-site-plugins';
import QuerySite from 'components/data/query-site';
import QueryVaultPressData from 'components/data/query-vaultpress-data';
import QueryAkismetData from 'components/data/query-akismet-data';
import { isUnavailableInDevMode } from 'state/connection';
import { AllModuleSettings } from 'components/module-settings/modules-per-tab-page';
import {
	isModuleActivated as _isModuleActivated,
	activateModule,
	deactivateModule,
	isActivatingModule,
	isDeactivatingModule,
	getModule as _getModule,
	getModules as _getModules
} from 'state/modules';
import { getSearchTerm } from 'state/search';
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

export const Page = ( {
	toggleModule,
	isModuleActivated,
	isTogglingModule,
	getModule,
	getModules,
	searchTerm,
	getVaultPressData,
	getScanThreats,
	getAkismetData,
	sitePlan,
	fetchingPluginsData,
	pluginInstalled,
	pluginActive,
	fetchingSiteData,
	unavailableInDevMode
	} ) => {
	let modules = getModules(),
		moduleList = [
			[
				'scan',
				__( 'Security Scanning' ),
				__( 'Automatically scan your site for common threats and attacks.' ),
				'security scan threat attacks pro' // Extra search terms @todo make translatable
			],
			[ 'akismet',
				'Akismet',
				__( 'Keep those spammers away!' ),
				'https://akismet.com/jetpack/',
				'spam security comments pro'
			],
			[
				'backups',
				__( 'Site Backups' ),
				__( 'Keep your site backed up!' ),
				'https://vaultpress.com/jetpack/',
				'backup restore pro security'
			]
		],
		cards;

	forEach( modules, function( m ) {
		'vaultpress' !== m.module ? moduleList.push( [
			m.module,
			getModule( m.module ).name,
			getModule( m.module ).description,
			getModule( m.module ).long_description,
			getModule( m.module ).learn_more_button,
			getModule( m.module ).search_terms,
			getModule( m.module ).additional_search_queries,
			getModule( m.module ).short_description,
			getModule( m.module ).feature.toString()
		] ) : '';
	} );

	cards = moduleList.map( ( element ) => {
		let isPro = 'scan' === element[0] || 'akismet' === element[0] || 'backups' === element[0],
			proProps = {},
			unavailableDevMode = unavailableInDevMode( element[0] ),
			toggle = unavailableDevMode ? __( 'Unavailable in Dev Mode' ) : (
				<ModuleToggle
					slug={ element[0] }
					activated={ isModuleActivated( element[0] ) }
					toggling={ isTogglingModule( element[0] ) }
					toggleModule={ toggleModule }
				/>
			),
			customClasses = unavailableDevMode ? 'devmode-disabled' : '';

		let getProToggle = ( active, installed ) => {
			let pluginSlug = 'scan' === element[0] || 'backups' === element[0] ?
				'vaultpress' :
				'akismet';

			let vpData = getVaultPressData();

			if ( 'N/A' !== vpData && 'vaultpress' === element[0] && 0 !== getScanThreats() ) {
				return(
					<SimpleNotice
						showDismiss={ false }
						status='is-error'
						isCompact={ true }
					>
						{ __( 'Threats found!' ) }
					</SimpleNotice>
				);
			}

			if ( 'akismet' === element[0] ) {
				const akismetData = getAkismetData();
				return 'invalid_key' === akismetData ?
					(
						<SimpleNotice
							showDismiss={ false }
							status='is-warning'
							isCompact={ true }
						>
							{ __( 'Invalid Key' ) }
						</SimpleNotice>
					) : '';
			}

			if ( false !== sitePlan() ) {
				return active && installed ?
					__( 'ACTIVE' ) :
					<Button
						compact={ true }
						primary={ true }
						href={ 'https://wordpress.com/plugins/' + pluginSlug + '/' + window.Initial_State.rawUrl }
					>
						{ ! installed ? __( 'Install' ) : __( 'Activate' ) }
					</Button>;
			}

			return active && installed ? __( 'ACTIVE' ) : '';
		};

		if ( isPro ) {
			proProps = {
				module: element[0],
				fetchingPluginsData: fetchingPluginsData,
				isProPluginInstalled: 'backups' === element[0] || 'scan' === element[0] ?
					pluginInstalled( 'vaultpress/vaultpress.php' ) :
					pluginInstalled( 'akismet/akismet.php' ),
				isProPluginActive: 'backups' === element[0] || 'scan' === element[0] ?
					pluginActive( 'vaultpress/vaultpress.php' ) :
					pluginActive( 'akismet/akismet.php' ),
				configure_url: 'backups' === element[0] || 'scan' === element[0] ?
					'https://dashboard.vaultpress.com' :
					Initial_State.adminUrl + 'admin.php?page=akismet-key-config'
			};
			toggle = ! fetchingSiteData ? getProToggle( proProps.isProPluginActive, proProps.isProPluginInstalled ) : '';

			// Add a "pro" button next to the header title
			element[1] = <span>
				{ element[1] }
				<Button
					compact={ true }
					href="#professional"
				>
					{ __( 'Pro' ) }
				</Button>
			</span>
		}

		if ( 1 === element.length ) {
			return ( <h1>{ element[0] }</h1> );
		}

		return (
			<FoldableCard
				key={ element[0] }
				className={ customClasses }
				header={ element[1] }
				searchTerms={ element.toString().replace( /<(?:.|\n)*?>/gm, '' ) }
				subheader={ element[2] }
				summary={ toggle }
				expandedSummary={ toggle }
				clickableHeaderText={ true }
			>
				{
					isModuleActivated ?
						<AllModuleSettings module={ isPro ? proProps : getModule( element[0] ) } /> :
						// Render the long_description if module is deactivated
						<div dangerouslySetInnerHTML={ renderLongDescription( getModule( element[0] ) ) } />
				}
				<br/>
				<div className="jp-module-settings__read-more">
					<Button borderless compact href={ element[3] }><Gridicon icon="help-outline" /><span className="screen-reader-text">{ __( 'Learn More' ) }</span></Button>
				</div>
			</FoldableCard>
		);
	} );

	return (
		<div>
			<QuerySite />
			<QuerySitePlugins />
			<QueryVaultPressData />
			<QueryAkismetData />
			<h2>{ __( 'Searching All Modules' ) }</h2>
			<Collection filter={ searchTerm() }>
				{ cards }
			</Collection>
		</div>
	);
};

function renderLongDescription( module ) {
	// Rationale behind returning an object and not just the string
	// https://facebook.github.io/react/tips/dangerously-set-inner-html.html
	return { __html: module.long_description };
}

export default connect(
	( state ) => {
		return {
			isModuleActivated: ( module_name ) => _isModuleActivated( state, module_name ),
			isTogglingModule: ( module_name ) => isActivatingModule( state, module_name ) || isDeactivatingModule( state, module_name ),
			getModule: ( module_name ) => _getModule( state, module_name ),
			getModules: () => _getModules( state ),
			searchTerm: () => getSearchTerm( state ),
			getScanThreats: () => _getVaultPressScanThreatCount( state ),
			getVaultPressData: () => _getVaultPressData( state ),
			getAkismetData: () => _getAkismetData( state ),
			sitePlan: () => getSitePlan( state ),
			fetchingSiteData: isFetchingSiteData( state ),
			fetchingPluginsData: isFetchingPluginsData( state ),
			pluginActive: ( plugin_slug ) => isPluginActive( state, plugin_slug ),
			pluginInstalled: ( plugin_slug ) => isPluginInstalled( state, plugin_slug ),
			unavailableInDevMode: ( module_name ) => isUnavailableInDevMode( state, module_name )
		};
	},
	( dispatch ) => {
		return {
			toggleModule: ( module_name, activated ) => {
				return ( activated )
					? dispatch( deactivateModule( module_name ) )
					: dispatch( activateModule( module_name ) );
			}
		};
	}
)( Page );
