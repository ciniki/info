//
// The info app to manage rental information
//
function ciniki_info_rental() {
    this.content_type = 18;
    this.init = function() {
        //
        // The panel to display the add form
        //
        this.edit = new M.panel('Rental',
            'ciniki_info_rental', 'edit',
            'mc', 'medium mediumaside', 'sectioned', 'ciniki.info.rental.edit');
        this.edit.data = {};    
        this.edit.content_id = 0;
        this.edit.sections = {
            '_image':{'label':'', 'aside':'yes', 'type':'imageform', 'fields':{
                'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 
                    'controls':'all', 'history':'no', 
                    'addDropImage':function(iid) {
                        M.ciniki_info_rental.edit.setFieldValue('primary_image_id', iid, null, null);
                        return true;
                        },
                    'addDropImageRefresh':'',
                    'deleteImage':'M.ciniki_info_rental.edit.deletePrimaryImage',
                    },
            }},
            '_image_caption':{'label':'', 'aside':'yes', 'fields':{
                'primary_image_caption':{'label':'Caption', 'type':'text'},
//              'primary_image_url':{'label':'URL', 'type':'text'},
            }},
            '_title':{'label':'', 'fields':{
                'title':{'label':'Title', 'type':'text', 'hint':'Rental'},
            }},
            '_content':{'label':'Rental', 'fields':{
                'content':{'label':'', 'type':'textarea', 'size':'large', 'hidelabel':'yes'},
            }},
            'images':{'label':'Gallery', 'type':'simplethumbs'},
            '_images':{'label':'', 'type':'simplegrid', 'num_cols':1,
                'addTxt':'Add Additional Image',
                'addFn':'M.startApp(\'ciniki.info.images\',null,\'M.ciniki_info_rental.edit.addDropImageRefresh();\',\'mc\',{\'content_id\':M.ciniki_info_rental.edit.content_id,\'add\':\'yes\'});',
            },
            'files':{'label':'Files',
                'type':'simplegrid', 'num_cols':1,
                'headerValues':null,
                'cellClasses':[''],
                'addTxt':'Add File',
                'addFn':'M.startApp(\'ciniki.info.contentfiles\',null,\'M.ciniki_info_rental.updateFiles();\',\'mc\',{\'content_id\':M.ciniki_info_rental.edit.content_id});',
//              'addFn':'M.ciniki_info_rental.showFileEdit(\'M.ciniki_info_rental.updateFiles();\',M.ciniki_info_rental.edit.content_id,0);',
            },
            '_child_title':{'label':'Suppliers', 'fields':{
                'child_title':{'label':'Title', 'type':'text', 'hint':'Suppliers'},
            }},
            'children':{'label':'', 'type':'simplegrid', 'num_cols':1, 
                'addTxt':'Add Supplier',
                'addFn':'M.ciniki_info_rental.showChildEdit(\'M.ciniki_info_rental.updateChildren();\',M.ciniki_info_rental.edit.content_id,0);',
                },
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_info_rental.saveContent();'},
            }},
        };
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.info.contentHistory', 'args':{'tnid':M.curTenantID,
                'content_id':this.content_id, 'field':i}};
        };
        this.edit.deletePrimaryImage = function(fid) {
            this.setFieldValue(fid, 0, null, null);
            return true;
        };
        this.edit.addDropImage = function(iid) {
            var rsp = M.api.getJSON('ciniki.info.contentImageAdd', 
                {'tnid':M.curTenantID, 'image_id':iid, 
                'content_id':M.ciniki_info_rental.edit.content_id});
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            return true;
        };
        this.edit.addDropImageRefresh = function() {
            if( M.ciniki_info_rental.edit.content_id > 0 ) {
                var rsp = M.api.getJSONCb('ciniki.info.contentGet', {'tnid':M.curTenantID, 
                    'content_id':M.ciniki_info_rental.edit.content_id, 'images':'yes'}, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        var p = M.ciniki_info_rental.edit;
                        p.data.images = rsp.content.images;
                        p.refreshSection('images');
                        p.show();
                    });
            }
            return true;
        };
        this.edit.sectionData = function(s) { 
            return this.data[s];
        };
        this.edit.fieldValue = function(s, i, j, d) {
            return this.data[i];
        };
        this.edit.cellValue = function(s, i, j, d) {
            if( s == 'files' && j == 0 ) { return d.file.name; }
            if( s == 'children' && j == 0 ) { return d.child.title; }
        };
        this.edit.rowFn = function(s, i, d) {
            if( s == 'children' ) {
                return 'M.ciniki_info_rental.showChildEdit(\'M.ciniki_info_rental.updateChildren();\',M.ciniki_info_rental.edit.content_id,\'' + d.child.id + '\');';
            }
            if( s == 'files' ) {
                return 'M.startApp(\'ciniki.info.contentfiles\',null,\'M.ciniki_info_rental.updateFiles();\',\'mc\',{\'file_id\':\'' + d.file.id + '\'});';
            }
        };
        this.edit.thumbFn = function(s, i, d) {
            return 'M.startApp(\'ciniki.info.images\',null,\'M.ciniki_info_rental.edit.addDropImageRefresh();\',\'mc\',{\'content_id\':M.ciniki_info_rental.edit.content_id,\'content_image_id\':\'' + d.image.id + '\'});';
        };
        this.edit.addButton('save', 'Save', 'M.ciniki_info_rental.saveContent();');
        this.edit.addClose('Cancel');

        //
        // The child edit panel 
        //
        this.childedit = new M.panel('Suppliers',
            'ciniki_info_rental', 'childedit',
            'mc', 'medium mediumaside', 'sectioned', 'ciniki.info.rental.childedit');
        this.childedit.data = {};   
        this.childedit.parent_id = 0;
        this.childedit.content_id = 0;
        this.childedit.sections = {
            '_image':{'label':'', 'aside':'yes', 'type':'imageform', 'fields':{
                'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 
                    'controls':'all', 'history':'no'},
            }},
//          '_image_caption':{'label':'', 'aside':'yes', 'fields':{
//              'primary_image_caption':{'label':'Caption', 'type':'text'},
//              'primary_image_url':{'label':'URL', 'type':'text'},
//          }},
            '_title':{'label':'', 'fields':{
                'title':{'label':'Name', 'type':'text'},
                'category':{'label':'Category', 'type':'text'},
            }},
            '_content':{'label':'Supplier Details', 'fields':{
                'content':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
            }},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_info_rental.saveChildContent();'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_info_rental.deleteChild();'},
            }},
        };
        this.childedit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.info.contentHistory', 'args':{'tnid':M.curTenantID,
                'content_id':this.content_id, 'field':i}};
        };
        this.childedit.addDropImage = function(iid) {
            M.ciniki_info_rental.childedit.setFieldValue('primary_image_id', iid, null, null);
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
        this.childedit.addButton('save', 'Save', 'M.ciniki_info_rental.saveChildContent();');
        this.childedit.addClose('Cancel');
    }

    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create container
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_info_rental', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        }

        this.showEdit(cb);
    }

    this.showEdit = function(cb) {
        M.api.getJSONCb('ciniki.info.contentGet', {'tnid':M.curTenantID,
            'content_type':this.content_type, 'images':'yes', 'files':'yes', 'children':'list'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_info_rental.edit;
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
                    M.ciniki_info_rental.edit.close();
                });
        } else {
            this.edit.close();
        }
    };

    this.updateFiles = function() {
        if( M.ciniki_info_rental.edit.content_id > 0 ) {
            var rsp = M.api.getJSONCb('ciniki.info.contentGet', {'tnid':M.curTenantID, 
                'content_id':M.ciniki_info_rental.edit.content_id, 'files':'yes'}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_info_rental.edit;
                    p.data.files = rsp.content.files;
                    p.refreshSection('files');
                    p.show();
                });
        }
        return true;
    };

    this.updateChildren = function() {
        M.api.getJSONCb('ciniki.info.contentGet', {'tnid':M.curTenantID,
            'content_id':M.ciniki_info_rental.edit.content_id, 'children':'list'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_info_rental.edit;
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
                    var p = M.ciniki_info_rental.childedit;
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
                        M.ciniki_info_rental.childedit.close();
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
                    M.ciniki_info_rental.childedit.close();
                });
        }
    };

    this.deleteChild = function() {
        M.confirm('Are you sure you want to delete this supplier?',null,function() {
            var rsp = M.api.getJSONCb('ciniki.info.contentDelete', {'tnid':M.curTenantID, 
                'content_id':M.ciniki_info_rental.childedit.content_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_info_rental.childedit.close();
                });
        });
    };
}
