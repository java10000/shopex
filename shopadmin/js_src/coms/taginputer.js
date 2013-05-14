var tagInputer = new Class({
	initialize:function(input,btns){
		this.input = input;
		this.btns = btns.addEvent('click',this.toggle.bind(this));
	},
	setBtns:function(btns){
		this.btns = btns.addEvent('click',this.toggle.bind(this));
	},
	toggle:function(e){
		e = $(new Event(e).target);
		if(e.hasClass('checked')){
			this.removeTag(e.getText());
			e.removeClass('checked');
		}else{
			this.addTag(e.getText());
			e.addClass('checked');
		}
	},
	addTag:function(t){
		var tags = this.input.value.split(/\s+/);
		this.input.value = tags.include(t).join(' ').trim();
	},
	removeTag:function(t){
		var tags = this.input.value.split(/\s+/);
		this.input.value = tags.remove(t).join(' ').trim();
	},
	set:function(tags){
		if(!tags)return;
		this.input.value = tags.join(' ').trim();

		var tagHash = {};
		tags.each(function(k){
				this[k] = 1;
		}.bind(tagHash));
		
		this.btns.each(function(btn){
				if(this[btn.getText()]){
					btn.addClass('checked');
				}else{
					btn.removeClass('checked');
				}
		}.bind(tagHash));

	}
});
