/**
 * External dependencies
 */
import React from 'react';
import forEach from 'lodash/forEach';
import Card from 'components/card';
import Chart from 'components/chart';
import { connect } from 'react-redux';
import DashSectionHeader from 'components/dash-section-header';
import Button from 'components/button';
import { translate as __ } from 'lib/mixins/i18n';

/**
 * Internal dependencies
 */
import { getSiteConnectionStatus } from 'state/connection';
import { demoStatsData, demoStatsBottom } from 'devmode';
import {
	statsSwitchTab,
	getActiveStatsTab as _getActiveStatsTab
} from 'state/at-a-glance';
import {
	isModuleActivated as _isModuleActivated,
	activateModule,
	isFetchingModulesList as _isFetchingModulesList
} from 'state/modules';

const DashStats = React.createClass( {
	statsChart: function( unit ) {
		let s = [];
		forEach( window.Initial_State.statsData[unit].data, function( v ) {
			let label = v[0];
			let views = v[1];

			if ( 'day' === unit ) {
				label = i18n.moment( v[0] ).format( 'MMM D' );
			} else if ( 'week' === unit ) {
				label = label.replace( /W/g, '-' );
				label = i18n.moment( label ).format( 'MMM D' );
			} else if ( 'month' ) {
				label = i18n.moment( label ).format( 'MMMM' );
			}

			s.push( {
				label: label,
				value: views,
				nestedValue: null,
				className: 'statsChartbar',
				data: {},
				tooltipData: [ {
					label: label,
					value: 'Views: ' + views,
					link: null,
					icon: '',
					className: 'tooltip class'
				} ]
			} );
		} );
		return ( getSiteConnectionStatus( this.props ) === 'dev' ) ? demoStatsData : s;
	},

	renderStatsArea: function() {
		if ( this.props.isModuleActivated( 'stats' ) ) {
			const activeTab = this.props.activeTab();
			return (
				<div className="jp-at-a-glance__stats-container">
					<div className="jp-at-a-glance__stats-chart">
						<Chart data={ this.statsChart( activeTab ) } />
					</div>
					<div id="stats-bottom" className="jp-at-a-glance__stats-bottom">
						<DashStatsBottom { ...this.props } />
					</div>
				</div>
			);
		} else {
			return (
				<div>
					{
						__( '{{a}}Activate Site Statistics{{/a}} to see detailed stats, likes, followers, subscribers, and more!', {
							components: {
								a: <a href="javascript:void(0)" onClick={ this.props.activateStats } />
							}
						} )
					}
				</div>
			);
		}
	},

	maybeShowStatsTabs: function() {
		if ( this.props.isModuleActivated( 'stats' ) ) {
			return(
				<ul className="jp-at-a-glance__stats-views">
					<li tabIndex="0" className="jp-at-a-glance__stats-view">
						<a href="javascript:void(0)" onClick={ this.handleSwitchStatsView.bind( this, 'day' ) }
							className={ this.getClass( 'day' ) }
						>{ __( 'Days' ) }</a>
					</li>
					<li tabIndex="0" className="jp-at-a-glance__stats-view">
						<a href="javascript:void(0)" onClick={ this.handleSwitchStatsView.bind( this, 'week' ) }
							className={ this.getClass( 'week' ) }
						>{ __( 'Weeks' ) }</a>
					</li>
					<li tabIndex="0" className="jp-at-a-glance__stats-view">
						<a href="javascript:void(0)" onClick={ this.handleSwitchStatsView.bind( this, 'month' ) }
							className={ this.getClass( 'month' ) }
						>{ __( 'Months' ) }</a>
					</li>
				</ul>
			);
		}
	},

	handleSwitchStatsView: function( view ) {
		this.props.switchView( view );
	},

	getClass: function( view ) {
		const activeTab = this.props.activeTab();
		return activeTab === view ?
			'jp-at-a-glance__stats-view-link is-current' :
			'jp-at-a-glance__stats-view-link';
	},

	render: function() {
		return(
			<div>
				<DashSectionHeader
					label="Site Statistics"
					settingsPath="#engagement"
				>
					{ this.maybeShowStatsTabs() }
				</DashSectionHeader>
				<Card>
					{ this.renderStatsArea() }
				</Card>
			</div>
		)
	}
} );

const DashStatsBottom = React.createClass( {
	statsBottom: function() {
		const generalStats = ( getSiteConnectionStatus( this.props ) === 'dev' ) ? demoStatsBottom : window.Initial_State.statsData.general.stats;
		return [
			{
				viewsToday: generalStats.views_today,
				bestDay: {
					day: generalStats.views_best_day,
					count: generalStats.views_best_day_total
				},
				allTime: {
					views: generalStats.views,
					comments: generalStats.comments
				}
			}
		];
	},

	render: function() {
		const s = this.statsBottom()[0];
		return (
		<div>
			<div className="jp-at-a-glance__stats-summary">
				<div className="jp-at-a-glance__stats-summary-today">
					<p className="jp-at-a-glance__stat-details">{ __( 'Views today', { comment: 'Referring to a number of page views' } ) }</p>
					<h3 className="jp-at-a-glance__stat-number">{ s.viewsToday }</h3>
				</div>
				<div className="jp-at-a-glance__stats-summary-bestday">
					<p className="jp-at-a-glance__stat-details">{ __( 'Best overall day', { comment: 'Referring to a number of page views' } ) }</p>
					<h3 className="jp-at-a-glance__stat-number">
						{
							__( '%(number)s View', '%(number)s Views',
								{
									count: number,
									args: {
										number: s.bestDay.count
									}
								}
							)
						}
					</h3>
					<p className="jp-at-a-glance__stat-details">{ s.bestDay.day }</p>
				</div>
				<div className="jp-at-a-glance__stats-summary-alltime">
					<div className="jp-at-a-glance__stats-alltime-views">
						<p className="jp-at-a-glance__stat-details">{ __( 'All-time views', { comment: 'Referring to a number of page views' } ) }</p>
						<h3 className="jp-at-a-glance__stat-number">{ s.allTime.views }</h3>
					</div>
					<div className="jp-at-a-glance__stats-alltime-comments">
						<p className="jp-at-a-glance__stat-details">{ __( 'All-time comments', { comment: 'Referring to a number of comments' } ) }</p>
						<h3 className="jp-at-a-glance__stat-number">{ s.allTime.comments }</h3>
					</div>
				</div>
			</div>
			<div className="jp-at-a-glance__stats-cta">
				<div className="jp-at-a-glance__stats-cta-description">
					<p>{ __( 'Need to see more stats, likes, followers, subscribers, and more?' ) }</p>
				</div>
				<div className="jp-at-a-glance__stats-cta-buttons">
					{ __( '{{button}}View old stats{{/button}}', { components: { button: <Button href="?page=stats" /> } } ) }
					{ __( '{{button}}View enhanced stats on WordPress.com{{/button}}', {
						components: { button: <Button className="is-primary" href={ 'https://wordpress.com/stats/insights/' + window.Initial_State.rawUrl } /> }
					} ) }
				</div>
			</div>
		</div>
		);
	}
} );

export default connect(
	( state ) => {
		return {
			isModuleActivated: ( module_name ) => _isModuleActivated( state, module_name ),
			getModule: ( module_name ) => _getModule( state, module_name ),
			isFetchingModules: () => _isFetchingModulesList( state ),
			activeTab: () => _getActiveStatsTab( state )
		};
	},
	( dispatch ) => {
		return {
			activateStats: () => {
				return dispatch( activateModule( 'stats' ) );
			},
			switchView: ( tab ) => {
				return dispatch( statsSwitchTab( tab ) );
			}
		};
	}
)( DashStats );
