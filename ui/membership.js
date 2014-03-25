//
// The info app to manage membership information
//
function ciniki_info_membership() {
	this.content_type = 7;
	this.init = function() {
		//
		// The panel to display the add form
		//
		this.edit = new M.panel('Membership Details',
			'ciniki_info_membership', 'edit',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.info.membership.edit');
		this.edit.data = {};	
		this.edit.content_id = 0;
		this.edit.sections = {
			'_image':{'label':'', 'aside':'yes', 'fields':{
				'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 
					'controls':'all', 'history':'no'},
			}},
			'_image_caption':{'label':'', 'aside':'yes', 'fields':{
				'primary_image_caption':{'label':'Caption', 'type':'text'},
//				'primary_image_url':{'label':'URL', 'type':'text'},
			}},
			'_content':{'label':'Membership Info', 'fields':{
				'content':{'label':'', 'type':'textarea', 'size':'large', 'hidelabel':'yes'},
			}},
			'files':{'label':'Application Forms',
				'type':'simplegrid', 'num_cols':1,
				'headerValues':null,
				'cellClasses':[''],
				'addTxt':'Add Application',
				'addFn':'M.ciniki_info_membership.showFileEdit(\'M.ciniki_info_membership.updateFiles();\',M.ciniki_info_membership.edit.content_id,0);',
				},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_info_membership.saveContent();'},
			}},
		};
		this.edit.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.info.contentHistory', 'args':{'business_id':M.curBusinessID,
				'content_id':this.content_id, 'field':i}};
		};
		this.edit.addDropImage = function(iid) {
			M.ciniki_info_membership.edit.setFieldValue('primary_image_id', iid, null, null);
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
			return 'M.ciniki_info_membership.showFileEdit(\'M.ciniki_info_membership.updateFiles();\',M.ciniki_info_membership.edit.content_id,\'' + d.file.id + '\');';
		};
		this.edit.addButton('save', 'Save', 'M.ciniki_info_membership.saveContent();');
		this.edit.addClose('Cancel');

		//
		// The panel to display the add form
		//
		this.addfile = new M.panel('Add File',
			'ciniki_info_membership', 'addfile',
			'mc', 'medium', 'sectioned', 'ciniki.info.membership.editfile');
		this.addfile.default_data = {};
		this.addfile.data = {};	
		this.addfile.sections = {
			'_file':{'label':'File', 'fields':{
				'uploadfile':{'label':'', 'type':'file', 'hidelabel':'yes'},
			}},
			'info':{'label':'Information', 'type':'simpleform', 'fields':{
				'name':{'label':'Title', 'type':'text'},
			}},
//			'_description':{'label':'Description', 'type':'simpleform', 'fields':{
//				'description':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
//			}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_info_membership.addFile();'},
			}},
		};
		this.addfile.fieldValue = function(s, i, d) { 
			if( this.data[i] != null ) {
				return this.data[i]; 
			} 
			return ''; 
		};
		this.addfile.addButton('save', 'Save', 'M.ciniki_info_membership.addFile();');
		this.addfile.addClose('Cancel');

		//
		// The panel to display the edit form
		//
		this.editfile = new M.panel('File',
			'ciniki_info_membership', 'editfile',
			'mc', 'medium', 'sectioned', 'ciniki.info.membership.editfile');
		this.editfile.file_id = 0;
		this.editfile.data = null;
		this.editfile.sections = {
			'info':{'label':'Details', 'type':'simpleform', 'fields':{
				'name':{'label':'Title', 'type':'text'},
			}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_info_membership.saveFile();'},
				'download':{'label':'Download', 'fn':'M.ciniki_info_membership.downloadFile(M.ciniki_info_membership.editfile.file_id);'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_info_membership.deleteFile();'},
			}},
		};
		this.editfile.fieldValue = function(s, i, d) { 
			return this.data[i]; 
		}
		this.editfile.sectionData = function(s) {
			return this.data[s];
		};
		this.editfile.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.info.contentFileHistory', 'args':{'business_id':M.curBusinessID, 
				'file_id':this.file_id, 'field':i}};
		};
		this.editfile.addButton('save', 'Save', 'M.ciniki_info_membership.saveFile();');
		this.editfile.addClose('Cancel');
	}

	this.start = function(cb, appPrefix, aG) {
		args = {};
		if( aG != null ) { args = eval(aG); }

		//
		// Create container
		//
		var appContainer = M.createContainer(appPrefix, 'ciniki_info_membership', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		}

		this.showEdit(cb);
	}

	this.showEdit = function(cb) {
		M.api.getJSONCb('ciniki.info.contentGet', {'business_id':M.curBusinessID,
			'content_type':M.ciniki_info_membership.content_type, 'files':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_info_membership.edit;
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
					M.ciniki_info_membership.edit.close();
				});
		} else {
			M.ciniki_info_membership.edit.close();
		}
	};

	this.showFileEdit = function(cb,cid,fid) {
		if( cid != null ) { this.editfile.content_id = cid; this.addfile.content_id = cid; }
		if( fid != null && fid > 0 ) {
			this.editfile.file_id = fid;
			var rsp = M.api.getJSONCb('ciniki.info.contentFileGet', 
				{'business_id':M.curBusinessID, 'file_id':this.editfile.file_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					var p = M.ciniki_info_membership.editfile;
					p.data = rsp.file;
					p.refresh();
					p.show(cb);
				});

		} else {
			this.addfile.reset();
			this.addfile.data = {};
			this.addfile.refresh();
			this.addfile.show(cb);
		}
	};

	this.updateFiles = function() {
		M.api.getJSONCb('ciniki.info.contentGet', {'business_id':M.curBusinessID,
			'content_id':M.ciniki_info_membership.editfile.content_id, 'files':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_info_membership.edit;
				p.data.files = rsp.content.files;
				p.refreshSection('files');
				p.show();
			});
	};

	this.addFile = function() {
		var c = this.addfile.serializeFormData('yes');

		M.api.postJSONFormData('ciniki.info.contentFileAdd', 
			{'business_id':M.curBusinessID, 
			'content_id':M.ciniki_info_membership.addfile.content_id}, c, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				} 
				M.ciniki_info_membership.addfile.close();
			});
	};

	this.saveFile = function() {
		var c = this.editfile.serializeFormData('no');

		if( c != '' ) {
			M.api.postJSONFormData('ciniki.info.contentFileUpdate', 
				{'business_id':M.curBusinessID, 'file_id':this.editfile.file_id}, c,
					function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} 
						M.ciniki_info_membership.editfile.close();
					});
		} else {
			this.editfile.close();
		}
	};

	this.deleteFile = function() {
		if( confirm('Are you sure you want to delete \'' + this.editfile.data.name + '\'?  All information about the file will be removed and unrecoverable.') ) {
			M.api.getJSONCb('ciniki.info.contentFileDelete', {'business_id':M.curBusinessID, 
				'file_id':this.editfile.file_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} 
					M.ciniki_info_membership.editfile.close();
				});
		}
	};

	this.downloadFile = function(fid) {
		window.open(M.api.getUploadURL('ciniki.info.contentFileDownload', {'business_id':M.curBusinessID, 'file_id':fid}));
	};
}
