Ext.application({
	name: 'LogMon',
	launch: function() {
		var tabs = Ext.widget('tabpanel', {
			renderTo: document.body,
			activeTab: 0,
			plain: true,
			defaults: {
				autoScroll: true,
				bodyPadding: 10
			},
			items: [{
				title: 'Events',
				loader: {
					url: 'events.php',
					contentType: 'html',
					loadMask: true
				},
				listeners: {
					activate: function(tab) {
						tab.loader.load();
					}
				}
            },{
				title: 'Alerts',
				loader: {
					url: 'alerts.php',
					contentType: 'html',
					loadMask: true
				},
				listeners: {
					activate: function(tab) {
						tab.loader.load();
					}
				}
			}]
		});

		Ext.create('Ext.container.Viewport', {
			layout: 'fit',
			items: [
					tabs
			]});
		}
	});
