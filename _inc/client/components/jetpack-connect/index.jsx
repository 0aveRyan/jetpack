/**
 * External dependencies
 */
import React from 'react';
import { connect } from 'react-redux';
import Button from 'components/button';
import Card from 'components/card';
import SectionHeader from 'components/section-header';

/**
 * Internal dependencies
 */
import { getConnectUrl } from 'state/initial-state';
import { imagePath } from 'constants';

const JetpackConnect = React.createClass( {
	render: function() {
		return (
			<div className="jp-connection__container">
				<h1 className="jp-connection__container-title" title="Please connect Jetpack to WordPress.com">Please Connect Jetpack</h1>

				<Card className="jp-connection__cta">
					<p className="jp-connection__description">Please connect to or create a WordPress.com account to enable Jetpack, including powerful security, traffic, and customization services.</p>
					<Button className="is-primary jp-connection__button" onClick={ getConnectUrl( this.props ) } >Connect Jetpack</Button>
					<p><a href="#" className="jp-connection__link">No WordPress.com account? Create one for free.</a></p>
				</Card>

				<Card className="jp-connection__feature jp-connection__traffic">

					<header className="jp-connection__header">
						<h2 className="jp-connection__container-subtitle" title="Drive more traffic to your site with Jetpack">Drive more traffic to your site</h2>
						<p className="jp-connection__description">Jetpack has many traffic and engagement tools to help you get more viewers 
to your site and keep them there.</p>

						<div className="jp-connection__header-img-container">
							<img src={ imagePath + "long-clouds.svg" } width="1160" height="63" alt="Decoration: Jetpack clouds" className="jp-connection__header-img" /> {/* defining width and height for IE here */}
							<img src={ imagePath + "stat-bars.svg" } width="400" alt="Decoration: Jetpack bar graph" className="jp-connection__header-img" />
						</div>
					</header>

					<div className="jp-connection__interior-container">
						<div className="jp-connection__feature-list">
							<div className="jp-connection__feature-list-column">
								<h3 title="Jetpack's Publicize feature" className="dops-section-header__label">Publicize</h3>
								<div className="jp-connection__feature-content">
									<h4 className="jp-connection__feature-content-title" title="Automated social marketing">Automated social marketing.</h4>
									<p>Use Publicize to automatically share your posts with friends, followers, and the world.</p>
								</div>
							</div>
							<div className="jp-connection__feature-list-column">
								<h3 title="Jetpack's Sharing and Like features" className="dops-section-header__label">Sharing &amp; Like Buttons</h3>
								<div className="jp-connection__feature-content">
									<h4 className="jp-connection__feature-content-title" title="Build a community">Build a community</h4>
									<p>Give visitors the tools to share and subscribe to your content.</p>
								</div>
							</div>
							<div className="jp-connection__feature-list-column">
								<h3 title="Jetpack's Related Posts feature" className="dops-section-header__label">Related Posts</h3>
								<div className="jp-connection__feature-content">
									<h4 className="jp-connection__feature-content-title" title="Increase page views">Increase page views</h4>
									<p>Keep visitors engaged by giving them more to share and read with Related Posts.</p>
								</div>
							</div>
						</div>

						<h2 className="jp-connection__container-subtitle" title="Track your growth">Track your growth</h2>
						<p className="jp-connection__description">Jetpack harnesses the power of WordPress.com to show you detailed insights about your visitors, what they’re reading, and where they’re coming from.</p>

						<img src={ imagePath + "stats-example-med.png" } 
							srcSet={ `${imagePath}stats-example-sm.png 500w, ${imagePath}stats-example-med.png 600w, ${imagePath}stats-example-lrg.png 900w` }
							className="jp-connection__feature-image"  alt="Jetpack statistics and traffic insights graph" />
					</div>
				</Card>
				<Card className="jp-connection__feature">

					<header className="jp-connection__header">
						<h2 className="jp-connection__container-subtitle" title="Site security and peace of mind with Jetpack">Site security and peace of mind</h2>
						<p className="jp-connection__description">Jetpack blocks malicious log in attempts, lets you know if your site goes down, and can automatically update your plugins, so you don’t have to worry.</p>
					</header>

					<div className="jp-connection__interior-container">
						<div className="jp-connection__feature-list">
							<div className="jp-connection__feature-list-column">
								<h3 title="Jetpack's Protect feature" className="dops-section-header__label">Protect</h3>
								<div className="jp-connection__feature-content">
									<h4 className="jp-connection__feature-content-title" title="Block site attacks">Block site attacks.</h4>
									<p>Gain peace of mind with Protect, the tool that has blocked billions of login attacks across millions of sites.</p>
								</div>
							</div>
							<div className="jp-connection__feature-list-column">
								<h3 title="Jetpack's Monitor features" className="dops-section-header__label">Monitor</h3>
								<div className="jp-connection__feature-content">
									<h4 className="jp-connection__feature-content-title" title="Live site monitoring">Live site monitoring</h4>
									<p>Stress less. Monitor will send you real-time alerts if your site ever goes down.</p>
								</div>
							</div>
							<div className="jp-connection__feature-list-column">
								<h3 title="Jetpack's Manage feature" className="dops-section-header__label">Manage</h3>
								<div className="jp-connection__feature-content">
									<h4 className="jp-connection__feature-content-title" title="Automatic site updates">Automatic site updates</h4>
									<p>Never fall behind on a security release or waste time updating multiple sites.</p>
								</div>
							</div>
						</div>
					</div>
				</Card>
				<Card className="jp-connection__feature">
					<header className="jp-connection__header">
						<h2 className="jp-connection__container-subtitle" title="lightning fast optimized images with Jetpack Photon">Lightning fast, optimized images</h2>
						<p className="jp-connection__description">Jetpack utilizes the state-of-the-art WordPress.com content delivery network to load your gorgeous imagery super fast. Optimized for any device, and its completely free.</p>
					</header>

					<div className="jp-connection__interior-container">
						<img src={ imagePath + "feature-photon-med.jpg" } 
							srcSet={ `${imagePath}feature-photon-sm.jpg 500w, ${imagePath}feature-photon-med.jpg 600w, ${imagePath}feature-photon-lrg.jpg 900w` }
							className="jp-connection__feature-image"  alt="Jetpacks photon serves up lightning fast, optimized images" />
					</div>
				</Card>
				<Card className="jp-connection__feature">
					<header className="jp-connection__header">
						<h2 className="jp-connection__container-subtitle" title="Jetpack offers free, professional support">Did we mention free, professional support?</h2>
						<p className="jp-connection__description">Jetpack is supported by some of the most technical and passionate people in the community. They're located around the globe and ready to help you.</p>
					</header>

					<div className="jp-connection__interior-container">
						<img src={ imagePath + "aurora-med.jpg" } 
							srcSet={ `${imagePath}aurora-sm.jpg 500w, ${imagePath}aurora-med.jpg 600w, ${imagePath}aurora-lrg.jpg 900w` }
							className="jp-connection__feature-image"  alt="Jetpack's free support team" />
					</div>
				</Card>
				<Card>
					<p className="jp-connection__description">Join the millions of users who rely on Jetpack to enhance and secure their sites. We’re passionate about WordPress and here to make your life easier.</p>
					<Button className="is-primary jp-connection__button" onClick={ getConnectUrl( this.props ) } >Connect Jetpack</Button>
				</Card>
			</div>
		);
	}
} );

export default connect( ( state ) => {
	return state;
} )( JetpackConnect );
