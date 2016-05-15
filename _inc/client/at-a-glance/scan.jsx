/**
 * External dependencies
 */
import React from 'react';
import { connect } from 'react-redux';
import DashItem from 'components/dash-item';
import DashSectionHeader from 'components/dash-section-header';

/**
 * Internal dependencies
 */
import QueryVaultPressData from 'components/data/query-vaultpress-data';
import {
	isModuleActivated as _isModuleActivated,
	activateModule,
	isActivatingModule,
	isFetchingModulesList as _isFetchingModulesList
} from 'state/modules';
import {
	getVaultPressScanThreatCount as _getVaultPressScanThreatCount,
	getVaultPressData as _getVaultPressData
} from 'state/at-a-glance';

const DashScan = React.createClass( {
	getContent: function() {
		if ( this.props.isModuleActivated( 'vaultpress' )  ) {
			let vpData = this.props.getVaultPressData();

			if ( vpData === 'N/A' ) {
				return(
					<DashItem label="Security Scan">
						Loading...
					</DashItem>
				);
			}

			// Check for threats
			const threats = this.props.getScanThreats();
			if ( threats !== 0 ) {
				return(
					<DashItem label="Security Scan" status="is-error">
						Uh oh, { threats } found! <a href="#">Do something.</a>
					</DashItem>
				);
			}

			// All good
			if ( vpData.code === 'success' ) {
				return(
					<DashItem label="Security Scan" status="is-working">
						Security Scan is working & all is good.
					</DashItem>
				);
			}
		}

		return(
			<DashItem label="Scan">
				Scan is not currently configured. <a href="#">Do something.</a>
			</DashItem>
		);
	},

	render: function() {
		return(
			<div>
				<QueryVaultPressData />
				{ this.getContent() }
			</div>
		);
	}
} );

export default connect(
	( state ) => {
		return {
			isModuleActivated: ( module_name ) => _isModuleActivated( state, module_name ),
			isFetchingModulesList: () => _isFetchingModulesList( state ),
			getVaultPressData: () => _getVaultPressData( state ),
			getScanThreats: () => _getVaultPressScanThreatCount( state )
		};
	},
	( dispatch ) => {
		return {
			activateModule: ( slug ) => {
				return dispatch( activateModule( slug ) );
			}
		};
	}
)( DashScan );