/**
 * External dependencies
 */
import React from 'react';
import { connect } from 'react-redux';
import Button from 'components/button';
import Spinner from 'components/spinner';

/**
 * Internal dependencies
 */
import {
	disconnectSite,
	unlinkUser,
	isCurrentUserLinked as _isCurrentUserLinked,
	isUnlinkingUser as _isUnlinkingUser,
	isDisconnectingSite as _isDisconnectingSite,
	isFetchingConnectUrl as _isFetchingConnectUrl,
	getConnectUrl as _getConnectUrl
} from 'state/connection';
import { getConnectUrl } from 'state/initial-state';
import QueryUserConnectionData from 'components/data/query-user-connection';
import QueryConnectUrl  from 'components/data/query-connect-url';

const ConnectionSettings = React.createClass( {
	renderContent: function() {
		const userData = window.Initial_State.userData;
		const maybeShowDisconnectBtn = userData.currentUser.canDisconnect
			? <Button onClick={ this.props.disconnectSite } >Disconnect site from WordPress.com</Button>
			: null;

		const maybeShowUnlinkBtn = ! userData.currentUser.isMaster
			? <Button onClick={ this.props.unlinkUser } >Unlink user from WordPress.com</Button>
			: null;

		// If current user is not linked.
		if ( ! this.props.isLinked( this.props ) ) {
			return(
				<div>
					You, { userData.currentUser.username }, are not linked to WordPress.com <br/>
					<Button href={ this.props.connectUrl( this.props ) }>Link to WordPress.com</Button>
					{ maybeShowDisconnectBtn }{ this.props.isDisconnecting() ? <Spinner /> : null }
				</div>
			);
		}

		return(
			<div>
				You are linked to WordPress.com account <strong>{ userData.currentUser.wpcomUser.login } / { userData.currentUser.wpcomUser.email }</strong><br/>
				{ maybeShowDisconnectBtn }{ this.props.isDisconnecting() ? <Spinner /> : null }<br/>
				{ maybeShowUnlinkBtn }{ this.props.isUnlinking() ? <Spinner /> : null }
			</div>
		)
	},

	render() {
		return(
			<div>
				{ this.renderContent() }
				<QueryUserConnectionData />
				<QueryConnectUrl />
			</div>
		)
	}
} );

export default connect(
	( state ) => {
		return {
			isLinked: () => _isCurrentUserLinked( state ),
			isUnlinking: () => _isUnlinkingUser( state ),
			isDisconnecting: () => _isDisconnectingSite( state ),
			fetchingConnectUrl: () => _isFetchingConnectUrl( state ),
			connectUrl: () => _getConnectUrl( state )
		}
	},
	( dispatch ) => {
		return {
			disconnectSite: () => {
				return dispatch( disconnectSite() );
			},
			unlinkUser: () => {
				return dispatch( unlinkUser() );
			}
		}
	}
)( ConnectionSettings );

