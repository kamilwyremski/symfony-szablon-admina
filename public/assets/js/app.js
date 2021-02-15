$(document).ready(function(){
	
  if (location.hash) {
		let url = location.href.replace(/\/$/, "");
    const hash = url.split("#");
    $('a[href="#'+hash[1]+'"]').tab("show");
    url = location.href.replace(/\/#/, "#");
		history.replaceState(null, null, url);
		setTimeout(() => {
      $(window).scrollTop(0);
    }, 400);
  } 
   
  $('a[data-toggle="tab"]').on("click", function() {
		location.hash = $(this).attr('href').substr(1);
  });

  $('.select-all-checkbox').click(function(){
		$this = $(this);
		if ($this.is(':checked')) {
			$this.parents('.select-all-checkbox-parent').find('.select-checkbox').prop('checked', true);
		}else{
			$this.parents('.select-all-checkbox-parent').find('.select-checkbox').prop('checked', false);
		}
  })

  function set_required(object){
		let $target = object.parents('.parent_set_required').find('.form-control');
		if (object.is(':checked')) {
			$target.prop('required', true);
		}else{
			$target.prop('required', false);
		}
	}
	$('.set_required').click(function(){
		set_required($(this));
	})
	$('.set_required').each(function(){
		set_required($(this));
	})

	function set_disabled(object){
		let $target = object.parents('.parent_set_disabled').find('.form-control');
		if (object.is(':checked')) {
			$target.prop('disabled', false);
		}else{
			$target.prop('disabled', true);
		}
	}

	$('.set_disabled').change(function(){
		set_disabled($(this));
	})
	$('.set_disabled').each(function(){
		set_disabled($(this));
	})

  $('#filter_form_type').change(function(){
		$this = $(this);
		if($this.val()=='select'){
			$('#filter--form--choices').show().find('textarea').attr("disabled", false);
			$('#filter--form--required').show().find('input[type=checkbox]').attr("disabled", false);
		}else if($this.val()=='checkbox'){
			$('#filter--form--choices').show().find('textarea').attr("disabled", false);
			$('#filter--form--required').hide().find('input[type=checkbox]').attr("disabled", true);
		}else{
      $('#filter--form--choices').hide().find('textarea').attr("disabled", true);
			$('#filter--form--required').show().find('input[type=checkbox]').attr("disabled", false);
		}
	})
  $('#filter_form_type').change();
  
  $('#filter_form_all_categories').change(function(){
		$this = $(this);
		if($this.is(':checked')){
			$('#filter--form--categories').hide().find('input[type=checkbox]').prop('disabled', true);
		}else{
			$('#filter--form--categories').show().find('input[type=checkbox]').prop('disabled', false);
		}
  })
  $('#filter_form_all_categories').change();
  
  $('.btn-open-file-manager').click(function(){
    $('.file-manager-target-input').removeClass('file-manager-target-input')
    $('.file-manager-target-image').removeClass('file-manager-target-image')
    let $parent = $(this).parents('.form-group');
    $parent.find('input').addClass('file-manager-target-input')
    $parent.find('img').addClass('file-manager-target-image')
  })

  $('#fileManagerFrame').on('load', function () {
    $(this).contents().on('click','.select',function () {
        var path = $(this).attr('data-path')
        $('.file-manager-target-input').val(path);
        $('.file-manager-target-image').attr('src', path)
        $('#fileManagerModal').modal('hide')
    });
  });

  let sortable = document.getElementById('sortable')
  if(sortable){
    Sortable.create(sortable);
  }
})

function sortTable() {
  let rows, switching, i, x, y, shouldSwitch;
  rows = document.getElementById("sortable").getElementsByTagName('tr');
  switching = true;
  while (switching) {
    switching = false;
    for (i = 0; i < (rows.length-1); i++) {
			shouldSwitch = false;
			x = rows[i].dataset.sort.toLowerCase();
			y = rows[i + 1].dataset.sort.toLowerCase();
      if (x.localeCompare(y) > 0) {
        shouldSwitch = true;
        break;
      }
    }
    if (shouldSwitch) {
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
    }
  }
}

$(document).on({'show.bs.modal': function () {
	$(this).removeAttr('tabindex');
} }, '.modal');