var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Tabs View.
		FusionPageBuilder.fusion_tabs = FusionPageBuilder.ParentElementView.extend( {

			/**
			 * Runs during render() call.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {
				var $this = this;

				jQuery( window ).on( 'load', function() {
					$this._refreshJs();
				} );
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				var self     = this,
					children = window.FusionPageBuilderViewManager.getChildViews( this.model.get( 'cid' ) );

				this.appendChildren( '.nav-tabs' );

				_.each( children, function( child ) {
					self.appendContents( child );
				} );

				this._refreshJs();
			},

			refreshJs: function() {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-element-render-fusion_tabs', this.model.attributes.cid );

				this.checkActiveTab();
			},

			/**
			 * Find the active tab.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			getActiveTab: function() {
				var self     = this,
					children = window.FusionPageBuilderViewManager.getChildViews( this.model.get( 'cid' ) );

				_.each( children, function( child ) {
					if ( child.$el.hasClass( 'active' ) ) {
						self.model.set( 'activeTab', child.model.get( 'cid' ) );
					}
				} );
			},

			/**
			 * Set tab as active.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			checkActiveTab: function() {
				var self = this,
					children = window.FusionPageBuilderViewManager.getChildViews( this.model.get( 'cid' ) );

				if ( 'undefined' !== typeof this.model.get( 'activeTab' ) ) {
					_.each( children, function( child ) {
						child.checkActive();
					} );
					self.$el.find( '.fusion-extra-' + this.model.get( 'activeTab' ) ).addClass( 'active in' );
				} else {
					_.each( children, function( child ) {
						if ( child.isFirstChild() ) {
							self.$el.find( '.fusion-extra-' + child.model.get( 'cid' ) ).addClass( 'active in' );
						}
					} );
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object} - Returns the attributes.
			 */
			filterTemplateAtts: function( atts ) {

				// Create attribute objects.
				atts.tabsShortcode   = this.buildTabsShortcodeAttrs( atts.values );
				atts.styleTag        = this.buildStyleTag( atts.values );
				atts.justifiedClass  = this.setJustifiedClass( atts.values );

				this.model.set( 'first', true );

				atts.cid             = this.model.get( 'cid' );
				return atts;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object} - Returns the shortcode object.
			 */
			buildTabsShortcodeAttrs: function( values ) {

				// TabsShortcode  Attributes.
				var tabsShortcode = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-tabs fusion-tabs-cid' + this.model.get( 'cid' ) + ' ' + values.design
				} );

				if ( 'yes' !== values.justified && 'vertical' !== values.layout ) {
					tabsShortcode[ 'class' ] += ' nav-not-justified';
				}

				if ( '' !== values.icon_position ) {
					tabsShortcode[ 'class' ] += ' icon-position-' + values.icon_position;
				}

				if ( '' !== values[ 'class' ] ) {
					tabsShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				tabsShortcode[ 'class' ] += ( 'vertical' === values.layout ) ? ' vertical-tabs' : ' horizontal-tabs';

				if ( 'no' == values.show_tab_titles ) {
					tabsShortcode[ 'class' ] += ' woo-tabs-hide-headings';
				}

				if ( '' !== values.id ) {
					tabsShortcode.id = values.id;
				}

				return tabsShortcode;
			},

			/**
			 * Builds styles.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {string} - Returns styles as a string.
			 */
			buildStyleTag: function( values ) {
				var cid    = this.model.get( 'cid' ),
					styles = '.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li a.tab-link{border-top-color:' + values.inactivecolor + ';background-color:' + values.inactivecolor + ';}';

				if ( 'clean' !== values.design ) {
					styles += '.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs{background-color:' + values.backgroundcolor + ';}';
					styles += '.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li.active a.tab-link,.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li.active a.tab-link:hover,.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li.active a.tab-link:focus{border-right-color:' + values.backgroundcolor + ';}';
				} else {
					styles = '#wrapper .fusion-tabs.fusion-tabs-cid' + cid + '.clean .nav-tabs li a.tab-link{border-color:' + values.bordercolor + ';}.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li a.tab-link{background-color:' + values.inactivecolor + ';}';
				}
				styles += '.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li.active a.tab-link,.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li.active a.tab-link:hover,.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li.active a.tab-link:focus{background-color:' + values.backgroundcolor + ';}';
				styles += '.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li a.tab-link:hover{background-color:' + values.backgroundcolor + ';border-top-color:' + values.backgroundcolor + ';}';
				styles += '.fusion-tabs.fusion-tabs-cid' + cid + ' .tab-pane{background-color:' + values.backgroundcolor + ';}';
				styles += '.fusion-tabs.fusion-tabs-cid' + cid + ' .nav,.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs,.fusion-tabs.fusion-tabs-cid' + cid + ' .tab-content .tab-pane{border-color:' + values.bordercolor + ';}';
				styles = '<style type="text/css">' + styles + '</style>';

				return styles;
			},

			/**
			 * Set class.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {string} - Returns a string containing the CSS classes.
			 */
			setJustifiedClass: function( values ) {
				var justifiedClass = '';

				if ( 'yes' === values.justified && 'vertical' !== values.layout ) {
					justifiedClass = ' nav-justified';
				}

				return justifiedClass;
			}
		} );
	} );
}( jQuery ) );
