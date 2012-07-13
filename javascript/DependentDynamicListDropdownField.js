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
			}

		});
	});	
})(jQuery);