//
// This app will handle the listing, additions and deletions of events.  These are associated business.
//
function ciniki_info_main() {
	//
	// Panels
	//
	this.init = function() {
		//
		// events panel
		//
		this.menu = new M.panel('Business Information',
			'ciniki_info_main', 'menu',
			'mc', 'medium', 'sectioned', 'ciniki.info.main.menu');
        this.menu.sections = {
			'content':{'label':'', 'list':{
				'1':{'label':'About', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.about\',null,\'M.ciniki_info_main.showMenu();\');'},
				'2':{'label':'Artist Statement', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.artiststatement\',null,\'M.ciniki_info_main.showMenu();\');'},
				'3':{'label':'CV', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.cv\',null,\'M.ciniki_info_main.showMenu();\');'},
				'4':{'label':'Awards', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.awards\',null,\'M.ciniki_info_main.showMenu();\');'},
				'5':{'label':'History', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.history\',null,\'M.ciniki_info_main.showMenu();\');'},
				'6':{'label':'Donations', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.donations\',null,\'M.ciniki_info_main.showMenu();\');'},
				'9':{'label':'Facilities', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.facilities\',null,\'M.ciniki_info_main.showMenu();\');'},
				'8':{'label':'Board of Directors', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.boardofdirectors\',null,\'M.ciniki_info_main.showMenu();\');'},
				'7':{'label':'Membership', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.membership\',null,\'M.ciniki_info_main.showMenu();\');'},
				'10':{'label':'Exhibition Application', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.exhibitionapplication\',null,\'M.ciniki_info_main.showMenu();\');'},
				}},
			};
		this.menu.listFn = function(s, i, d) { return d.fn; }
		this.menu.listLabel = function(s, i, d) { return ''; }
		this.menu.addClose('Back');
	}

	//
	// Arguments:
	// aG - The arguments to be parsed into args
	//
	this.start = function(cb, appPrefix, aG) {
		args = {};
		if( aG != null ) { args = eval(aG); }

		//
		// Create the app container if it doesn't exist, and clear it out
		// if it does exist.
		//
		var appContainer = M.createContainer(appPrefix, 'ciniki_info_main', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		} 

		for(i in this.menu.sections.content.list) {
			if( (M.curBusiness.modules['ciniki.info'].flags&Math.pow(2, i-1)) > 0 ) {
				this.menu.sections.content.list[i].visible = 'yes';
			} else {
				this.menu.sections.content.list[i].visible = 'no';
			}
		}
	
		this.showMenu(cb);
	}

	this.showMenu = function(cb) {
		this.menu.refresh();
		this.menu.show(cb);
	};
};
