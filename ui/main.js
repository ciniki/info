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
				'25':{'label':'Extended Bio', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.extendedbio\',null,\'M.ciniki_info_main.showMenu();\');'},
				'3':{'label':'CV', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.cv\',null,\'M.ciniki_info_main.showMenu();\');'},
				'4':{'label':'Awards', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.awards\',null,\'M.ciniki_info_main.showMenu();\');'},
				'5':{'label':'History', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.history\',null,\'M.ciniki_info_main.showMenu();\');'},
				'6':{'label':'Donations', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.donations\',null,\'M.ciniki_info_main.showMenu();\');'},
				'9':{'label':'Facilities', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.facilities\',null,\'M.ciniki_info_main.showMenu();\');'},
				'8':{'label':'Board of Directors', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.boardofdirectors\',null,\'M.ciniki_info_main.showMenu();\');'},
				'7':{'label':'Membership', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.membership\',null,\'M.ciniki_info_main.showMenu();\');'},
				'10':{'label':'Exhibition Application', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.exhibitionapplication\',null,\'M.ciniki_info_main.showMenu();\');'},
				'11':{'label':'Warranty', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.warranty\',null,\'M.ciniki_info_main.showMenu();\');'},
				'12':{'label':'Testimonials', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.testimonials\',null,\'M.ciniki_info_main.showMenu();\');'},
				'13':{'label':'Reviews', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.reviews\',null,\'M.ciniki_info_main.showMenu();\');'},
				'14':{'label':'Green Policy', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.greenpolicy\',null,\'M.ciniki_info_main.showMenu();\');'},
				'15':{'label':'Why Us', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.whyus\',null,\'M.ciniki_info_main.showMenu();\');'},
				'16':{'label':'Privacy Policy', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.privacypolicy\',null,\'M.ciniki_info_main.showMenu();\');'},
				'17':{'label':'Volunteer', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.volunteer\',null,\'M.ciniki_info_main.showMenu();\');'},
				'18':{'label':'Rental', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.rental\',null,\'M.ciniki_info_main.showMenu();\');'},
				'19':{'label':'Financial Assistance', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.financialassistance\',null,\'M.ciniki_info_main.showMenu();\');'},
				'20':{'label':'Artists', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.artists\',null,\'M.ciniki_info_main.showMenu();\');'},
				'21':{'label':'Employment', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.employment\',null,\'M.ciniki_info_main.showMenu();\');'},
				'22':{'label':'Staff', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.staff\',null,\'M.ciniki_info_main.showMenu();\');'},
				'23':{'label':'Sponsorship', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.sponsorship\',null,\'M.ciniki_info_main.showMenu();\');'},
				'24':{'label':'Jobs', 'visible':'no', 'fn':'M.startApp(\'ciniki.info.jobs\',null,\'M.ciniki_info_main.showMenu();\');'},
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
		M.api.getJSONCb('ciniki.info.contentList', {'business_id':M.curBusinessID}, function(rsp) {
			if( rsp.stat != 'ok' ) {
				M.api.err(rsp);
				return false;
			}
			var p = M.ciniki_info_main.menu;
			if( rsp.content != null ) {
				for(i in rsp.content) {
					p.sections.content.list[rsp.content[i].content.id].label = rsp.content[i].content.title;
				}
			}
			p.refresh();
			p.show(cb);
		});
//		this.menu.refresh();
//		this.menu.show(cb);
	};
};
