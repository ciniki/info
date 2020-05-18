//
// The app to add/edit content images
//
function ciniki_info_images() {
    this.webFlags = {
        '1':{'name':'Hidden'},
        };
    this.init = function() {
        //
        // The panel to display the edit form
        //
        this.edit = new M.panel('Edit Image',
            'ciniki_info_images', 'edit',
            'mc', 'medium', 'sectioned', 'ciniki.info.images.edit');
        this.edit.default_data = {};
        this.edit.data = {};
        this.edit.content_id = 0;
        this.edit.sections = {
            '_image':{'label':'Photo', 'type':'imageform', 'fields':{
                'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
            }},
            'info':{'label':'Information', 'type':'simpleform', 'fields':{
                'name':{'label':'Title', 'type':'text'},
                'webflags':{'label':'Website', 'type':'flags', 'join':'yes', 'flags':this.webFlags},
            }},
            '_description':{'label':'Description', 'type':'simpleform', 'fields':{
                'description':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
            }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_info_images.saveImage();'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_info_images.deleteImage();'},
            }},
        };
        this.edit.fieldValue = function(s, i, d) { 
            if( this.data[i] != null ) {
                return this.data[i]; 
            } 
            return ''; 
        };
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.info.contentImageHistory', 'args':{'tnid':M.curTenantID, 
                'content_image_id':this.content_image_id, 'field':i}};
        };
        this.edit.addDropImage = function(iid) {
            M.ciniki_info_images.edit.setFieldValue('image_id', iid, null, null);
            return true;
        };
        this.edit.addButton('save', 'Save', 'M.ciniki_info_images.saveImage();');
        this.edit.addClose('Cancel');
    };

    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create container
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_info_images', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        }

        if( args.add != null && args.add == 'yes' ) {
            this.showEdit(cb, 0, args.content_id);
        } else if( args.content_image_id != null && args.content_image_id > 0 ) {
            this.showEdit(cb, args.content_image_id);
        }
        return false;
    }

    this.showEdit = function(cb, iid, eid) {
        if( iid != null ) { this.edit.content_image_id = iid; }
        if( eid != null ) { this.edit.content_id = eid; }
        if( this.edit.content_image_id > 0 ) {
            var rsp = M.api.getJSONCb('ciniki.info.contentImageGet', 
                {'tnid':M.curTenantID, 'content_image_id':this.edit.content_image_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_info_images.edit;
                    p.data = rsp.image;
                    p.refresh();
                    p.show(cb);
                });
        } else {
            this.edit.reset();
            this.edit.data = {};
            this.edit.refresh();
            this.edit.show(cb);
        }
    };

    this.saveImage = function() {
        if( this.edit.content_image_id > 0 ) {
            var c = this.edit.serializeFormData('no');
            if( c != '' ) {
                var rsp = M.api.postJSONFormData('ciniki.info.contentImageUpdate', 
                    {'tnid':M.curTenantID, 
                    'content_image_id':this.edit.content_image_id}, c,
                        function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            } else {
                                M.ciniki_info_images.edit.close();
                            }
                        });
            } else {
                this.edit.close();
            }
        } else {
            var c = this.edit.serializeFormData('yes');
            var rsp = M.api.postJSONFormData('ciniki.info.contentImageAdd', 
                {'tnid':M.curTenantID, 'content_id':this.edit.content_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } else {
                            M.ciniki_info_images.edit.close();
                        }
                    });
        }
    };

    this.deleteImage = function() {
        M.confirm('Are you sure you want to delete this image?',null,function() {
            var rsp = M.api.getJSONCb('ciniki.info.contentImageDelete', {'tnid':M.curTenantID, 
                'content_image_id':M.ciniki_info_images.edit.content_image_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_info_images.edit.close();
                });
        });
    };
}
