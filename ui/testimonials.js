//
// The testimonials for a business
//
function ciniki_info_testimonials() {
    this.webFlags = {
        '1':{'name':'Visible'},
        };
    this.init = function() {
        //
        // Setup the main panel to list the collection
        //
        this.main = new M.panel('Testimonials',
            'ciniki_info_testimonials', 'main',
            'mc', 'medium', 'sectioned', 'ciniki.info.testimonials.main');
        this.main.data = {};
        this.main.sections = {
            'testimonials':{'label':'', 'type':'simplegrid', 'num_cols':1,
                'cellClasses':['multiline'],
                'addTxt':'Add Testimonial',
                'addFn':'M.ciniki_info_testimonials.showEdit(\'M.ciniki_info_testimonials.showMain();\',0);',
            }};
        this.main.cellValue = function(s, i, j, d) {
            if( j == 0 ) { 
                return '<span class="maintext">' + d.testimonial.who + '</span>'
                    + '<span class="subtext singleline">' + d.testimonial.quote + '</span>';
            }
        };
        this.main.rowFn = function(s, i, d) {
            return 'M.ciniki_info_testimonials.showEdit(\'M.ciniki_info_testimonials.showMain();\', \'' + d.testimonial.id + '\');'; 
        };
        this.main.sectionData = function(s) { 
            return this.data[s];
        };
        this.main.addButton('add', 'Add', 'M.ciniki_info_testimonials.showEdit(\'M.ciniki_info_testimonials.showMain();\', 0);');
        this.main.addClose('Back');

        //
        // The panel to display the edit form
        //
        this.edit = new M.panel('Testimonial',
            'ciniki_info_testimonials', 'edit',
            'mc', 'medium', 'sectioned', 'ciniki.info.testimonials.edit');
        this.edit.testimonial_id = 0;
        this.edit.data = null;
        this.edit.sections = {
            '_quote':{'label':'Testimonial', 'type':'simpleform', 'fields':{
                'quote':{'label':'', 'type':'textarea', 'size':'large', 'hidelabel':'yes'},
            }},
            'info':{'label':'Details', 'type':'simpleform', 'fields':{
                'who':{'label':'Who', 'type':'text'},
//              'sequence':{'label':'Sequence', 'type':'text'},
                'webflags':{'label':'Website', 'type':'flags', 'flags':this.webFlags},
//              'testimonial_date':{'label':'Who', 'type':'text'{'label':'Sequence', 'type':'text'},
            }},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_info_testimonials.saveTestimonial();'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_info_testimonials.deleteTestimonial();'},
            }},
        };
        this.edit.fieldValue = function(s, i, d) { 
            return this.data[i]; 
        }
//      this.edit.sectionData = function(s) {
//          return this.data[s];
//      };
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.info.testimonialHistory', 'args':{'business_id':M.curBusinessID, 
                'testimonial_id':this.testimonial_id, 'field':i}};
        }
//      this.edit.addDropImage = function(iid) {
//          M.ciniki_info_testimonials.edit.setFieldValue('image_id', iid, null, null);
//          return true;
//      };
//      this.edit.deleteImage = function() {
//          this.setFieldValue('image_id', 0, null, null);
//          return true;
//      };
        this.edit.addButton('save', 'Save', 'M.ciniki_info_testimonials.saveTestimonial();');
        this.edit.addClose('Cancel');
    }

    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create container
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_info_testimonials', 'yes');
        if( appContainer == null ) {
            alert('App Error');
            return false;
        }

        this.showMain(cb);
    }

    this.showMain = function(cb) {
        //
        // If there is not many 
        //
        this.main.data = {};
        M.api.getJSONCb('ciniki.info.testimonialList', 
            {'business_id':M.curBusinessID}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_info_testimonials.main;
                p.data = {'testimonials':rsp.testimonials};
                p.refresh();
                p.show(cb);
            });
    };

    this.showEdit = function(cb, tid) {
        if( tid != null ) { this.edit.testimonial_id = tid; }
        if( this.edit.testimonial_id > 0 ) {
            this.edit.sections._buttons.buttons.delete.visible = 'yes';
            var rsp = M.api.getJSONCb('ciniki.info.testimonialGet', 
                {'business_id':M.curBusinessID, 'testimonial_id':this.edit.testimonial_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_info_testimonials.edit;
                    p.data = rsp.testimonial;
                    p.refresh();
                    p.show(cb);
                });
        } else {
            this.edit.reset();
            this.edit.sections._buttons.buttons.delete.visible = 'no';
            this.edit.data = {};
            this.edit.refresh();
            this.edit.show(cb);
        }
    };

    this.saveTestimonial = function() {
        if( this.edit.testimonial_id > 0 ) {
            var c = this.edit.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONFormData('ciniki.info.testimonialUpdate', 
                    {'business_id':M.curBusinessID, 'testimonial_id':this.edit.testimonial_id}, c,
                        function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            } else {
                                M.ciniki_info_testimonials.edit.close();
                            }
                        });
            } else {
                M.ciniki_info_testimonials.edit.close();
            }
        } else {
            var c = this.edit.serializeForm('yes');
            M.api.postJSONFormData('ciniki.info.testimonialAdd', 
                {'business_id':M.curBusinessID}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } else {
                        M.ciniki_info_testimonials.edit.close();
                    }
                });
        }
    };

    this.deleteTestimonial = function() {
        if( confirm('Are you sure you want to delete this testimonial?') ) {
            var rsp = M.api.getJSONCb('ciniki.info.testimonialDelete', 
                {'business_id':M.curBusinessID, 'testimonial_id':this.edit.testimonial_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_info_testimonials.edit.close();
                });
        }
    };
}
