(function($){
	$.entwine('ss', function($){

		$('select.dependentdynamiclistdropdown').entwine({
			onmatch : function(){
				ddl_onMatch(this);
			},

			updateOptions : function(listName, value){
				ddl_updateOptions(this, listName, value);
			}

		});

		$('.uf-dependentdynamiclistdropdown select.form-control').entwine({
			onmatch : function(){
				ddl_onMatch(this);
			},

			updateOptions : function(listName, value){
				ddl_updateOptions(this, listName, value);
			}

		});

		function ddl_updateOptions(el, listName, value) {
			var self = $(el),
				lists = self.data('listoptions'),
				list = lists[listName];
			
			if(typeof list == "object"){
				self.empty();
				for (var k in list) {
					var sel = '';
					if (k == value) {
						sel = ' selected="selected"';
					}
					self.append('<option val="' + k + '"' + sel + '>' + k + '</option>');
				}
			}
			if(self.chosen != undefined) {
				self.trigger('liszt:updated');
				self.trigger("chosen:updated");
			}
		}

		function ddl_onMatch(el) {
			var self = $(el),
				dependentOn = $('select[name=' + self.data('dependenton') + ']');

			if(!dependentOn.length){
				return;
			}

			if(dependentOn.val()){
				self.updateOptions(dependentOn.val());
				if(self.data('initialvalue')){
					self.change(function(){
						var data = $(el).val();
					});

					self.val(self.data('initialvalue')).trigger('change');

					if(self.chosen != undefined) {
						self.trigger("liszt:updated");
						self.trigger("chosen:updated");
					}
				}
			}

			dependentOn.on('change', function(){
				self.updateOptions(dependentOn.val());
			});
		}
	});
})(jQuery);