//
// The files manager for tenant info
//
function ciniki_info_contentfiles() {
    this.init = function() {
        //
        // The panel to display the add form
        //
        this.add = new M.panel('Add File',
            'ciniki_info_contentfiles', 'add',
            'mc', 'medium', 'sectioned', 'ciniki.info.contentfiles.edit');
        this.add.default_data = {'type':'20'};
        this.add.data = {}; 
        this.add.sections = {
            '_file':{'label':'File', 'fields':{
                'uploadfile':{'label':'', 'type':'file', 'hidelabel':'yes'},
            }},
            'info':{'label':'Information', 'type':'simpleform', 'fields':{
                'name':{'label':'Title', 'type':'text'},
            }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_info_contentfiles.addFile();'},
            }},
        };
        this.add.fieldValue = function(s, i, d) { 
            if( this.data[i] != null ) {
                return this.data[i]; 
            } 
            return ''; 
        };
        this.add.addButton('save', 'Save', 'M.ciniki_info_contentfiles.addFile();');
        this.add.addClose('Cancel');

        //
        // The panel to display the edit form
        //
        this.edit = new M.panel('File',
            'ciniki_info_contentfiles', 'edit',
            'mc', 'medium', 'sectioned', 'ciniki.info.contentfiles.edit');
        this.edit.file_id = 0;
        this.edit.data = null;
        this.edit.sections = {
            'info':{'label':'Details', 'type':'simpleform', 'fields':{
                'name':{'label':'Title', 'type':'text'},
            }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_info_contentfiles.saveFile();'},
                'download':{'label':'Download', 'fn':'M.ciniki_info_contentfiles.downloadFile(M.ciniki_info_contentfiles.edit.file_id);'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_info_contentfiles.deleteFile();'},
            }},
        };
        this.edit.fieldValue = function(s, i, d) { 
            return this.data[i]; 
        }
        this.edit.sectionData = function(s) {
            return this.data[s];
        };
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.info.contentFileHistory', 'args':{'tnid':M.curTenantID, 
                'file_id':this.file_id, 'field':i}};
        };
        this.edit.addButton('save', 'Save', 'M.ciniki_info_contentfiles.saveFile();');
        this.edit.addClose('Cancel');
    }

    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create container
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_info_contentfiles', 'yes');
        if( appContainer == null ) {
            alert('App Error');
            return false;
        }

        if( args.file_id != null && args.file_id > 0 ) {
            this.showEditFile(cb, args.file_id);
        } else if( args.content_id != null && args.content_id > 0 ) {
            this.showAddFile(cb, args.content_id);
        } else {
            alert('Invalid request');
        }
    }

    this.showMenu = function(cb) {
        this.menu.refresh();
        this.menu.show(cb);
    };

    this.showAddFile = function(cb, eid) {
        this.add.reset();
        this.add.data = {'name':''};
        this.add.file_id = 0;
        this.add.content_id = eid;
        this.add.refresh();
        this.add.show(cb);
    };

    this.addFile = function() {
        var c = this.add.serializeFormData('yes');

        if( c != '' ) {
            var rsp = M.api.postJSONFormData('ciniki.info.contentFileAdd', 
                {'tnid':M.curTenantID, 'content_id':M.ciniki_info_contentfiles.add.content_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } else {
                            M.ciniki_info_contentfiles.add.file_id = rsp.id;
                            M.ciniki_info_contentfiles.add.close();
                        }
                    });
        } else {
            M.ciniki_info_contentfiles.add.close();
        }
    };

    this.showEditFile = function(cb, fid) {
        if( fid != null ) {
            this.edit.file_id = fid;
        }
        var rsp = M.api.getJSONCb('ciniki.info.contentFileGet', {'tnid':M.curTenantID, 
            'file_id':this.edit.file_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_info_contentfiles.edit.data = rsp.file;
                M.ciniki_info_contentfiles.edit.refresh();
                M.ciniki_info_contentfiles.edit.show(cb);
            });
    };

    this.saveFile = function() {
        var c = this.edit.serializeFormData('no');

        if( c != '' ) {
            var rsp = M.api.postJSONFormData('ciniki.info.contentFileUpdate', 
                {'tnid':M.curTenantID, 'file_id':this.edit.file_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } else {
                            M.ciniki_info_contentfiles.edit.close();
                        }
                    });
        }
    };

    this.deleteFile = function() {
        if( confirm('Are you sure you want to delete \'' + this.edit.data.name + '\'?  All information about it will be removed and unrecoverable.') ) {
            var rsp = M.api.getJSONCb('ciniki.info.contentFileDelete', {'tnid':M.curTenantID, 
                'file_id':M.ciniki_info_contentfiles.edit.file_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_info_contentfiles.edit.close();
                });
        }
    };

    this.downloadFile = function(fid) {
        M.api.openFile('ciniki.info.contentFileDownload', {'tnid':M.curTenantID, 'file_id':fid});
    };
}
