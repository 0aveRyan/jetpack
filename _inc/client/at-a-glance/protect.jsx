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
import {
	isModuleActivated as _isModuleActivated,
	activateModule,
	isActivatingModule,
	isFetchingModulesList as _isFetchingModulesList
} from 'state/modules';
import {
	fetchProtectCount,
	getProtectCount as _getProtectCount
} from 'state/at-a-glance';

const DashProtect = React.createClass( {
	getContent: function() {
		if ( this.props.isFetchingModulesList( this.props ) ) {
			return(
				<DashItem label="Protect">
					Loading Data...
				</DashItem>
			);
		}

		const protectCount = this.props.getProtectCount();
		if ( this.props.isModuleActivated( 'protect' )  ) {
			if ( false === protectCount || "0" === protectCount ) {
				return(
					<DashItem label="Protect" status="is-working">
						Sit back and relax. Protect is on and actively blocking malicious login attempts. Data will display here soon.
					</DashItem>
				);
			}
			return(
				<DashItem label="Protect" status="is-working">
					<h1>{ protectCount }</h1> Blocked attacks!
				</DashItem>
			);
		}

		return(
			<DashItem label="Protect">
				Protect is not on. <a onClick={ this.props.activateProtect }>activate it</a>
			</DashItem>
		);
	},

	render: function() {
		return this.getContent();
	}
} );

export default connect(
	( state ) => {
		return {
			isModuleActivated: ( module_name ) => _isModuleActivated( state, module_name ),
			getProtectCount: () => _getProtectCount( state ),
			isFetchingModulesList: () => _isFetchingModulesList( state ),
			getModule: ( module_name ) => _getModule( state, module_name )
		};
	},
	( dispatch ) => {
		return {
			activateProtect: () => {
				return dispatch( activateModule( 'protect' ) );
			},
			fetchProtectCount: () => {
				return dispatch( fetchProtectCount() );
			}
		};
	}
)( DashProtect );