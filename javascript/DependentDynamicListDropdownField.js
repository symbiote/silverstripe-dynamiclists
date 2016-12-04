(function($){
	$.entwine('ss', function($){

		$('select.dependentdynamiclistdropdown').entwine({
			onmatch : function(){
				var self = $(this),
					dependentOn = $('select[name=' + self.data('dependenton') + ']');

				if(!dependentOn.length){
					return;
				}

				if(dependentOn.val()){
					self.updateOptions(dependentOn.val());
					if(self.data('initialvalue')){
						self.change(function(){
							var data = $(this).val();
						});

						self.val(self.data('initialvalue')).trigger('change');

						if(self.chosen != undefined) {
							self.trigger("liszt:updated");
							self.trigger("chosen:updated");
						}
					}
				}

				dependentOn.bind('change', function(){
					self.updateOptions(dependentOn.val());
				});
			},

			updateOptions : function(listName, value){
				var self = $(this),
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

		});
	});	
})(jQuery);