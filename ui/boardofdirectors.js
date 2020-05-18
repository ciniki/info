//
// The info app to manage boardofdirectors information
//
function ciniki_info_boardofdirectors() {
    this.content_type = 8;
    this.init = function() {
        //
        // The panel to display the add form
        //
        this.edit = new M.panel('Board of Directors',
            'ciniki_info_boardofdirectors', 'edit',
            'mc', 'medium mediumaside', 'sectioned', 'ciniki.info.boardofdirectors.edit');
        this.edit.data = {};    
        this.edit.content_id = 0;
        this.edit.sections = {
            '_image':{'label':'', 'aside':'yes', 'type':'imageform', 'fields':{
                'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 
                    'controls':'all', 'history':'no'},
            }},
            '_image_caption':{'label':'', 'aside':'yes', 'fields':{
                'primary_image_caption':{'label':'Caption', 'type':'text'},
//              'primary_image_url':{'label':'URL', 'type':'text'},
            }},
            '_content':{'label':'Introduction', 'fields':{
                'content':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
            }},
            'children':{'label':'Directors', 'type':'simplegrid', 'num_cols':1, 
                'addTxt':'Add Director',
                'addFn':'M.ciniki_info_boardofdirectors.showChildEdit(\'M.ciniki_info_boardofdirectors.updateChildren();\',M.ciniki_info_boardofdirectors.edit.content_id,0);',
                },
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_info_boardofdirectors.saveContent();'},
            }},
        };
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.info.contentHistory', 'args':{'tnid':M.curTenantID,
                'content_id':this.content_id, 'field':i}};
        };
        this.edit.addDropImage = function(iid) {
            M.ciniki_info_boardofdirectors.edit.setFieldValue('primary_image_id', iid, null, null);
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
            if( j == 0 ) { return d.child.title; }
        };
        this.edit.rowFn = function(s, i, d) {
            if( s == 'children' ) {
                return 'M.ciniki_info_boardofdirectors.showChildEdit(\'M.ciniki_info_boardofdirectors.updateChildren();\',M.ciniki_info_boardofdirectors.edit.content_id,\'' + d.child.id + '\');';
            }
        };
        this.edit.addButton('save', 'Save', 'M.ciniki_info_boardofdirectors.saveContent();');
        this.edit.addClose('Cancel');

        //
        // The child edit panel 
        //
        this.childedit = new M.panel('Directors',
            'ciniki_info_boardofdirectors', 'childedit',
            'mc', 'medium mediumaside', 'sectioned', 'ciniki.info.boardofdirectors.childedit');
        this.childedit.data = {};   
        this.childedit.parent_id = 0;
        this.childedit.content_id = 0;
        this.childedit.sections = {
            '_image':{'label':'', 'aside':'yes', 'type':'imageform', 'fields':{
                'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 
                    'controls':'all', 'history':'no'},
            }},
            '_image_caption':{'label':'', 'aside':'yes', 'fields':{
                'primary_image_caption':{'label':'Caption', 'type':'text'},
//              'primary_image_url':{'label':'URL', 'type':'text'},
            }},
            '_title':{'label':'', 'fields':{
                'title':{'label':'Name', 'type':'text'},
            }},
            '_content':{'label':'Bio', 'fields':{
                'content':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
            }},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_info_boardofdirectors.saveChildContent();'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_info_boardofdirectors.deleteChild();'},
            }},
        };
        this.childedit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.info.contentHistory', 'args':{'tnid':M.curTenantID,
                'content_id':this.content_id, 'field':i}};
        };
        this.childedit.addDropImage = function(iid) {
            M.ciniki_info_boardofdirectors.childedit.setFieldValue('primary_image_id', iid, null, null);
            return true;
        };
        this.childedit.deleteImage = function(fid) {
            this.setFieldValue(fid, 0, null, null);
            return true;
        };
        this.childedit.sectionData = function(s) { 
            return this.data[s];
        };
        this.childedit.fieldValue = function(s, i, j, d) {
            return this.data[i];
        };
        this.childedit.addButton('save', 'Save', 'M.ciniki_info_boardofdirectors.saveChildContent();');
        this.childedit.addClose('Cancel');
    }

    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create container
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_info_boardofdirectors', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        }

        this.showEdit(cb);
    }

    this.showEdit = function(cb) {
        M.api.getJSONCb('ciniki.info.contentGet', {'tnid':M.curTenantID,
            'content_type':this.content_type, 'children':'list'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_info_boardofdirectors.edit;
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
                    M.ciniki_info_boardofdirectors.edit.close();
                });
        } else {
            this.edit.close();
        }
    };

    this.updateChildren = function() {
        M.api.getJSONCb('ciniki.info.contentGet', {'tnid':M.curTenantID,
            'content_id':M.ciniki_info_boardofdirectors.edit.content_id, 'children':'list'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_info_boardofdirectors.edit;
                p.data.children = rsp.content.children;
                p.refreshSection('children');
                p.show();
            });
    };

    this.showChildEdit = function(cb, pid, cid) {
        if( pid != null ) { this.childedit.parent_id = pid; }
        if( cid != null ) { this.childedit.content_id = cid; }
        if( this.childedit.content_id > 0 ) {
            M.api.getJSONCb('ciniki.info.contentGet', {'tnid':M.curTenantID,
                'content_id':this.childedit.content_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_info_boardofdirectors.childedit;
                    p.data = rsp.content;
                    p.refresh();
                    p.show(cb);
                });
        } else {
            this.childedit.reset();
            this.childedit.data = {};
            this.childedit.refresh();
            this.childedit.show(cb);
        }
    };

    this.saveChildContent = function() {
        if( this.childedit.content_id > 0 ) {
            var c = this.childedit.serializeFormData('no');
            if( c != null ) {
                M.api.postJSONFormData('ciniki.info.contentUpdate', 
                    {'tnid':M.curTenantID, 'content_id':this.childedit.content_id}, c, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        M.ciniki_info_boardofdirectors.childedit.close();
                    });
            } else {
                this.childedit.close();
            }
        } else {
            var c = this.childedit.serializeFormData('yes');
            M.api.postJSONFormData('ciniki.info.contentAdd', 
                {'tnid':M.curTenantID, 'content_type':this.content_type,
                'parent_id':this.childedit.parent_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_info_boardofdirectors.childedit.close();
                });
        }
    };

    this.deleteChild = function() {
        M.confirm('Are you sure you want to delete this director?',null,function() {
            var rsp = M.api.getJSONCb('ciniki.info.contentDelete', {'tnid':M.curTenantID, 
                'content_id':M.ciniki_info_boardofdirectors.childedit.content_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_info_boardofdirectors.childedit.close();
                });
        });
    };
}
