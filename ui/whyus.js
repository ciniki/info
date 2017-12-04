//
// The info app to manage Why Us information
//
function ciniki_info_whyus() {
    this.content_type = 15;
    this.init = function() {
        //
        // The panel to display the add form
        //
        this.edit = new M.panel('Why Us',
            'ciniki_info_whyus', 'edit',
            'mc', 'medium mediumaside', 'sectioned', 'ciniki.info.whyus.edit');
        this.edit.data = {};    
        this.edit.content_id = 0;
        this.edit.sections = {
            '_image':{'label':'', 'aside':'yes', 'type':'imageform', 'fields':{
                'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 
                    'controls':'all', 'history':'no', 
                    'addDropImage':function(iid) {
                        M.ciniki_info_whyus.edit.setFieldValue('primary_image_id', iid, null, null);
                        return true;
                        },
                    'addDropImageRefresh':'',
                    'deleteImage':'M.ciniki_info_whyus.edit.deletePrimaryImage',
                    },
            }},
            '_image_caption':{'label':'', 'aside':'yes', 'fields':{
                'primary_image_caption':{'label':'Caption', 'type':'text'},
//              'primary_image_url':{'label':'URL', 'type':'text'},
            }},
            '_title':{'label':'', 'fields':{
                'title':{'label':'Title', 'type':'text', 'hint':'Why Us?'},
            }},
            '_content':{'label':'Why Us', 'fields':{
                'content':{'label':'', 'type':'textarea', 'size':'large', 'hidelabel':'yes'},
            }},
            'images':{'label':'Gallery', 'type':'simplethumbs'},
            '_images':{'label':'', 'type':'simplegrid', 'num_cols':1,
                'addTxt':'Add Additional Image',
                'addFn':'M.startApp(\'ciniki.info.images\',null,\'M.ciniki_info_whyus.edit.addDropImageRefresh();\',\'mc\',{\'content_id\':M.ciniki_info_whyus.edit.content_id,\'add\':\'yes\'});',
                },
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_info_whyus.saveContent();'},
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
                'content_id':M.ciniki_info_whyus.edit.content_id});
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            return true;
        };
        this.edit.addDropImageRefresh = function() {
            if( M.ciniki_info_whyus.edit.content_id > 0 ) {
                var rsp = M.api.getJSONCb('ciniki.info.contentGet', {'tnid':M.curTenantID, 
                    'content_id':M.ciniki_info_whyus.edit.content_id, 'images':'yes'}, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        var p = M.ciniki_info_whyus.edit;
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
            if( j == 0 ) { return d.file.name; }
        };
        this.edit.rowFn = function(s, i, d) {
            return 'M.ciniki_info_whyus.showFileEdit(\'M.ciniki_info_whyus.updateFiles();\',M.ciniki_info_whyus.edit.content_id,\'' + d.file.id + '\');';
        };
        this.edit.thumbFn = function(s, i, d) {
            return 'M.startApp(\'ciniki.info.images\',null,\'M.ciniki_info_whyus.edit.addDropImageRefresh();\',\'mc\',{\'content_id\':M.ciniki_info_whyus.edit.content_id,\'content_image_id\':\'' + d.image.id + '\'});';
        };
        this.edit.addButton('save', 'Save', 'M.ciniki_info_whyus.saveContent();');
        this.edit.addClose('Cancel');
    }

    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create container
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_info_whyus', 'yes');
        if( appContainer == null ) {
            alert('App Error');
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
                var p = M.ciniki_info_whyus.edit;
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
                    M.ciniki_info_whyus.edit.close();
                });
        } else {
            this.edit.close();
        }
    };
}
