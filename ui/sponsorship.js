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
            '_image':{'label':'', 'aside':'yes', 'type':'imageform', 'fields':{
                'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'sponsorship':'no', 'history':'no'},
            }},
            '_image_caption':{'label':'', 'aside':'yes', 'fields':{
                'primary_image_caption':{'label':'Caption', 'type':'text'},
//              'primary_image_url':{'label':'URL', 'type':'text'},
            }},
            '_content':{'label':'Sponsorship', 'fields':{
                'content':{'label':'', 'type':'textarea', 'size':'large', 'hidelabel':'yes'},
            }},
            'files':{'label':'Files',
                'type':'simplegrid', 'num_cols':1,
                'headerValues':null,
                'cellClasses':[''],
                'addTxt':'Add File',
                'addFn':'M.ciniki_info_sponsorship.showFileEdit(\'M.ciniki_info_sponsorship.updateFiles();\',M.ciniki_info_sponsorship.edit.content_id,0);',
                },
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_info_sponsorship.saveContent();'},
            }},
        };
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.info.contentHistory', 'args':{'tnid':M.curTenantID,
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

        //
        // The panel to display the add form
        //
        this.addfile = new M.panel('Add File',
            'ciniki_info_sponsorship', 'addfile',
            'mc', 'medium', 'sectioned', 'ciniki.info.sponsorship.editfile');
        this.addfile.default_data = {};
        this.addfile.data = {}; 
        this.addfile.sections = {
            '_file':{'label':'File', 'fields':{
                'uploadfile':{'label':'', 'type':'file', 'hidelabel':'yes'},
            }},
            'info':{'label':'Information', 'type':'simpleform', 'fields':{
                'name':{'label':'Title', 'type':'text'},
            }},
//          '_description':{'label':'Description', 'type':'simpleform', 'fields':{
//              'description':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
//          }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_info_sponsorship.addFile();'},
            }},
        };
        this.addfile.fieldValue = function(s, i, d) { 
            if( this.data[i] != null ) {
                return this.data[i]; 
            } 
            return ''; 
        };
        this.addfile.addButton('save', 'Save', 'M.ciniki_info_sponsorship.addFile();');
        this.addfile.addClose('Cancel');

        //
        // The panel to display the edit form
        //
        this.editfile = new M.panel('File',
            'ciniki_info_sponsorship', 'editfile',
            'mc', 'medium', 'sectioned', 'ciniki.info.sponsorship.editfile');
        this.editfile.file_id = 0;
        this.editfile.data = null;
        this.editfile.sections = {
            'info':{'label':'Details', 'type':'simpleform', 'fields':{
                'name':{'label':'Title', 'type':'text'},
            }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_info_sponsorship.saveFile();'},
                'download':{'label':'Download', 'fn':'M.ciniki_info_sponsorship.downloadFile(M.ciniki_info_sponsorship.editfile.file_id);'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_info_sponsorship.deleteFile();'},
            }},
        };
        this.editfile.fieldValue = function(s, i, d) { 
            return this.data[i]; 
        }
        this.editfile.sectionData = function(s) {
            return this.data[s];
        };
        this.editfile.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.info.contentFileHistory', 'args':{'tnid':M.curTenantID, 
                'file_id':this.file_id, 'field':i}};
        };
        this.editfile.addButton('save', 'Save', 'M.ciniki_info_sponsorship.saveFile();');
        this.editfile.addClose('Cancel');
    }

    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create container
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_info_sponsorship', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        }

        this.showEdit(cb);
    }

    this.showEdit = function(cb) {
        M.api.getJSONCb('ciniki.info.contentGet', {'tnid':M.curTenantID,
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
                {'tnid':M.curTenantID, 'content_id':this.edit.content_id}, c, function(rsp) {
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

    this.showFileEdit = function(cb,cid,fid) {
        if( cid != null ) { this.editfile.content_id = cid; this.addfile.content_id = cid; }
        if( fid != null && fid > 0 ) {
            this.editfile.file_id = fid;
            var rsp = M.api.getJSONCb('ciniki.info.contentFileGet', 
                {'tnid':M.curTenantID, 'file_id':this.editfile.file_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_info_sponsorship.editfile;
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
        M.api.getJSONCb('ciniki.info.contentGet', {'tnid':M.curTenantID,
            'content_id':M.ciniki_info_sponsorship.editfile.content_id, 'files':'yes'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_info_sponsorship.edit;
                p.data.files = rsp.content.files;
                p.refreshSection('files');
                p.show();
            });
    };

    this.addFile = function() {
        var c = this.addfile.serializeFormData('yes');

        M.api.postJSONFormData('ciniki.info.contentFileAdd', 
            {'tnid':M.curTenantID, 
            'content_id':M.ciniki_info_sponsorship.addfile.content_id}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                } 
                M.ciniki_info_sponsorship.addfile.close();
            });
    };

    this.saveFile = function() {
        var c = this.editfile.serializeFormData('no');

        if( c != '' ) {
            M.api.postJSONFormData('ciniki.info.contentFileUpdate', 
                {'tnid':M.curTenantID, 'file_id':this.editfile.file_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                        M.ciniki_info_sponsorship.editfile.close();
                    });
        } else {
            this.editfile.close();
        }
    };

    this.deleteFile = function() {
        M.confirm('Are you sure you want to delete \'' + this.editfile.data.name + '\'?  All information about the file will be removed and unrecoverable.',null,function() {
            M.api.getJSONCb('ciniki.info.contentFileDelete', {'tnid':M.curTenantID, 
                'file_id':M.ciniki_info_sponsorship.editfile.file_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_info_sponsorship.editfile.close();
                });
        });
    };

    this.downloadFile = function(fid) {
        M.api.openFile('ciniki.info.contentFileDownload', {'tnid':M.curTenantID, 'file_id':fid});
    };
}
