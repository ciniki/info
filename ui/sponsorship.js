//
// The info app to manage sponsorship information
//
function ciniki_info_sponsorship() {
	this.content_type = 23;
	this.init = function() {
		//
		// The panel to display the add form
		//
		this.edit = new M.panel('Sponsorship',
			'ciniki_info_sponsorship', 'edit',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.info.sponsorship.edit');
		this.edit.data = {};	
		this.edit.content_id = 0;
		this.edit.sections = {
			'_image':{'label':'', 'aside':'yes', 'fields':{
				'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 
					'controls':'all', 'sponsorship':'no'},
			}},
			'_image_caption':{'label':'', 'aside':'yes', 'fields':{
				'primary_image_caption':{'label':'Caption', 'type':'text'},
//				'primary_image_url':{'label':'URL', 'type':'text'},
			}},
			'_content':{'label':'Sponsorship', 'fields':{
				'content':{'label':'', 'type':'textarea', 'size':'large', 'hidelabel':'yes'},
			}},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_info_sponsorship.saveContent();'},
			}},
		};
		this.edit.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.info.contentHistory', 'args':{'business_id':M.curBusinessID,
				'content_id':this.content_id, 'field':i}};
		};
		this.edit.addDropImage = function(iid) {
			M.ciniki_info_sponsorship.edit.setFieldValue('primary_image_id', iid, null, null);
			return true;
		};
		this.edit.deleteImage = function(fid) {
			this.setFieldValue(fid, 0, null, null);
			return true;
		};
		this.edit.sectionData = function(s) { 
			return this.data[s];
		};
		this.edit.fieldValue = function(s, i, j, d) {
			return this.data[i];
		};
		this.edit.cellValue = function(s, i, j, d) {
			if( j == 0 ) { return d.file.name; }
		};
		this.edit.rowFn = function(s, i, d) {
			return 'M.ciniki_info_sponsorship.showFileEdit(\'M.ciniki_info_sponsorship.updateFiles();\',M.ciniki_info_sponsorship.edit.content_id,\'' + d.file.id + '\');';
		};
		this.edit.addButton('save', 'Save', 'M.ciniki_info_sponsorship.saveContent();');
		this.edit.addClose('Cancel');
	}

	this.start = function(cb, appPrefix, aG) {
		args = {};
		if( aG != null ) { args = eval(aG); }

		//
		// Create container
		//
		var appContainer = M.createContainer(appPrefix, 'ciniki_info_sponsorship', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		}

		this.showEdit(cb);
	}

	this.showEdit = function(cb) {
		M.api.getJSONCb('ciniki.info.contentGet', {'business_id':M.curBusinessID,
			'content_type':this.content_type}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_info_sponsorship.edit;
				p.data = rsp.content;
				p.content_id = rsp.content.id;
				p.refresh();
				p.show(cb);
			});
	};

	this.saveContent = function() {
		var c = this.edit.serializeFormData('no');
		if( c != null ) {
			M.api.postJSONFormData('ciniki.info.contentUpdate', 
				{'business_id':M.curBusinessID, 'content_id':this.edit.content_id}, c, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_info_sponsorship.edit.close();
				});
		} else {
			this.edit.close();
		}
	};
}
