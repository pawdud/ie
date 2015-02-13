
;
var IE = window.IE = function(){
    
    
};

IE.options = {
    viewProject: {
        idTpl: "tpl-project"
    },
    viewAction: {
        idTpl: "tpl-action",
        idTplImage: "tpl-image",
    }
}

IE.init = function(options){    
    Backbone.emulateHTTP = true;
    Backbone.emulateJSON = true;    
   
    var options = $.extend({}, IE.options, options);     
    var router = new IE.Router(options);
    
    Backbone.history.start({
       pushState: true,
       root: ''
    });
    
};



IE.Registry = function(options){
    this.urls = options.urls;    
};


$.extend(IE.Registry.prototype, {
     getUrl: function(name, id_action) {
        var id_action = id_action || false;
        var url = this.urls[name];
        if (id_action) {
            url = url.replace(":id_action", id_action);
        }
        return url;
    }
});


IE.Action = Backbone.Model.extend({
    
    idAttribute: "id_action",
   
    defaults: {
        src: "",
        id_action: ""
    },
    
    initialize: function(options){
        this.urlRoot = options.urlRoot;
        
    }
    
});


IE.ViewProject = Backbone.View.extend({   
    events: {
        "change #file": "upload"
    },
    initialize: function(options) {
        this.router = options.router;
        this.idTpl = options.idTpl;        
        this.template = _.template($("#" + this.idTpl).html()),
        _.bindAll(this, "upload", "render");
        this.render();
    },
    render: function() {
        this.$el.html(this.template({data: null}));
    },
    upload: function() {
        var self = this;
        var formData = new FormData(this.$el.find("#f-image").get(0));
        var file = this.$el.find("#file").get(0).files[0];
        formData.append("file", file);       
        $.ajax({
            url: self.router.registry.getUrl("urlProject"),
            data: formData,
            processData: false,
            contentType: false,
            method: "POST",
            success: function(data) {
                self.router.navigate(self.router.registry.getUrl("urlAction", data.id_action), {trigger: true, replace: true});
            }
        });
    }
});


IE.ViewAction = Backbone.View.extend({    
    
    
    selectionEl: null,
    selectionInstance: null,
    
    /**
     * Anulowanie zaznaczenia obszaru zdjęcia
     */
    cancelSelection: function(){     
        console.log("cancel selection");
        if(null !== this.selectionEl){
           $(this.selectionEl).imgAreaSelect({remove: true});
           this.selectionEl = null;
           this.selectionInstance = null;
        }              
    },
    
    /**
     * Zaznacznie obszaru zdjęcia
     */
    select: function(){
      console.log("select");
      if(null === this.selectionEl){
          this.selectionEl          = this.$el.find("img").get(0);
          this.selectionInstance    =  $(this.selectionEl).imgAreaSelect({
              handles: true,
              instance: true,
              show: true,
              onInit: function(img){                  
                  // Wybrany obszar znajduje sie w srodku obrazka i ma 
                  // wysokosc i szerokosc rowna 1/3 wysokosci i szerokosci obrazka                  
                  var w = $(img).width();
                  var h = $(img).height();
                  
                  var partW = w / 3;
                  var partH = h / 3;
                  
                  var x1 = partW;
                  var y1 = partH;
                  var x2 = x1 + partW;
                  var y2 = y1 + partH;
                  
                  this.setSelection(x1, y1, x2, y2);
                  this.update();                                       
              }
          });
      }          
    },
    
    /**
     * Pobieranie współrzędnych zaznaczenia obszaru zdjęcia
     */
    getSelection: function(){
        if(null === this.selectionInstance){
            return false;
        }
        var selection =  this.selectionInstance.getSelection();  
        console.log("getSelection.selection", selection);
        return selection;
       
    },    
    
    
    events: {
        "click #btn-rotate": "rotate",
        "click #btn-undo": "undo",
        "click #btn-select": "select",
        "click #btn-crop": "crop",
    },
    initialize: function(options){
        this.idTpl          = options.idTpl;
        this.idTplImage     = options.idTplImage;
        this.router         = options.router;       
    
        _.bindAll(this, "rotate", "undo", "render");
        this.template = _.template($("#" + this.idTpl).html());
        this.templateImage = _.template($("#" + this.idTplImage).html());
        this.$el.html(this.template({data: null}));
        this.model.on("change", this.render);

    },
    render: function() {
        this.$el.find("#scene").html(this.templateImage({data: this.model.attributes}));
    },
    
    /**
     * Obracanie
     */
    rotate: function() {
        var self = this;
        this.cancelSelection();
        var url_rotate = self.router.registry.getUrl("urlRotate", this.model.id);
        $.post(url_rotate, function(data) {
            self.router.navigate(self.router.registry.getUrl("urlAction", data.id_action), {trigger: true, replace: false});
        });
    },  
    
    /**
     * Przycinanie
     */
    crop: function(){
      console.log("crop");
     
      var self      = this;
      var selection = this.getSelection();      
      var url       = this.router.registry.getUrl("urlCrop", this.model.id);
      
     
      
      var data = {
          x: selection["x1"],
          y: selection["y1"],
          w: selection["width"],
          h: selection["height"]         
      };
      
//      console.log("crop.selection", selection);
//      console.log("crop.url", url);
//      console.log("crop.data", data);
      
      this.cancelSelection();
      
      $.post(url, data, function(data){
          self.router.navigate(self.router.registry.getUrl("urlAction", data.id_action), {trigger: true, replace: false});
          
          
//          console.log("crop.response");         
      });
    },
    
    
    undo: function() {

    }
});


 IE.Router = Backbone.Router.extend({   
     
     
    
    _setAction: function(){
        var urlRoot   = this.registry.getUrl("urlFetch");
        this.action =  new IE.Action({
            urlRoot: urlRoot            
        });
    },
    
    
    _setRouteProject: function(){  
        var urlProject = this._stripFirstSlash(this.registry.getUrl("urlBase"));
        this.route(urlProject, "projectRoute");       
    },
    
    
    _setRouteAction: function(){
        var urlAction = this._stripFirstSlash(this.registry.getUrl("urlAction"));        
        this.route(urlAction, "actionRoute");     
        
    },
    
    _stripFirstSlash: function(url){
        var regex = /^\//;
        var url = url.replace(regex, '');
        return url;
    },   
     
    
    initialize: function(options){
        this.options        = options;
        this.viewAction     = null;
        this.viewProject    = null;
        
        this.registry = new IE.Registry({
            urls: options.urls
        });
        
        this._setAction();
        
        this._setRouteProject();
        
        this._setRouteAction();       
        
       
        this.viewProjectOptions = options.viewProject;
        this.viewActionOptions  = options.viewAction;
        
    },
    
    
    _getViewAction: function(){
        if(null === this.viewAction){
            var options = $.extend(this.options.viewAction, {model: this.action, router: this});
            this.viewAction = new IE.ViewAction(options);
            $("#cnt-app").append(this.viewAction.el);
        }        
    },
    
    _removeViewAction: function(){
      if(null !== this.viewAction){
          this.viewAction.remove();
      }        
    },
    
     _getViewProject: function(){
        if(null === this.viewProject){
            var options = $.extend(this.options.viewProject, {router: this});
            this.viewProject = new IE.ViewProject(options);
            $("#cnt-app").append(this.viewProject.el);
        }        
    },
    
    _removeViewProject: function(){
      if(null !== this.viewProject){
          this.viewProject.remove();
      }        
    },
    
    projectRoute: function() {       
        this._removeViewAction();
        this._getViewProject();
    },
    
    actionRoute: function(id_action) {      
        this._removeViewProject();
        this._getViewAction();      
        this.action.set("id_action", id_action, {silent: true});
        this.action.fetch();
    }
});