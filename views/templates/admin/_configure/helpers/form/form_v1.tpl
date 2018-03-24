{extends file="helpers/form/form.tpl"}



{block name="script"}

$(document).ready(function(){


  var  frequency_html='<select>';

        {foreach from=$frequency key=myId item=i}
        frequency_html += '<option id={$i.id}>{$i.name}</option>';
        {/foreach}

  frequency_html += '</select>';





  var field_templates ={
          'checkbox' : {
              html: '<input type="checkbox"/>',
              getValue: function (input) {
                  return $(input).is(':checked');
              },
              setValue: function (input, value) {
                  if ( value ){
                      return $(input).attr('checked', true);
                  }
                  return $(input).removeAttr('checked');
              }
          },
          'text' : {
              html: '<input type="text"/>',
              getValue: function (input) {
                  return $(input).val();
              },
              setValue: function (input, value) {

                    return $(input).attr('value',value);
              }
          },
          'hidden' : {
              html: '<input type="hidden"/>',
              getValue: function (input) {
                  return $(input).val();
              },
              setValue: function (input, value) {

                    return $(input).attr('value',value);
              }
          },
          'hidden_page_type' : {
              html: '<input class="page_type" type="hidden"/>',
              getValue: function (input) {
                  return $(input).val();
              },
              setValue: function (input, value) {

                    return $(input).attr('value',value);
              }
          },
          'priority' : {
              html: '<input class="priority" maxlength="4" size="4" min="0" max="1" step="0.1" pattern="\d*" type="number" value="0"></input>',
              getValue: function (input) {

                  return $(input).val();
              },
              setValue: function (input, value) {


                  return $(input).attr('value',value);
              }
          },


          'frequency' : {
              html: frequency_html,
              getValue: function (input) {
                  return $(input).val();
              },
              setValue: function (input, value) {
                  var select = $(input);
                  select.find('option').filter(function() {
                      return $(this).val() == value;
                  }).attr('selected', true);
                  return select;
              }
          }
      };

      var row_template = ['checkbox','hidden','hidden_page_type', 'text', 'frequency', 'priority'];
        {* var row_template = {$row_template}; *}

      {* console.log({$row_parameters});   *}
      var headerCols = ['&nbsp;&nbsp;#&nbsp;&nbsp;','','','Page name','Frequency','Priority'];



      var selected_pages = {$selected_pages};
      var available_pages = {$available_pages};



      var sp =  $("#selected_pages").editTable({

              field_templates: field_templates,
                  row_template: row_template,
                  headerCols: headerCols,
                  first_row: false,
                  data: selected_pages,

        });

      var ap = $("#available_pages").editTable({

              field_templates: field_templates,
                  row_template: row_template,
                  headerCols: headerCols,
                  first_row: false,
                  data: available_pages,

        });

      var row_parameters = {$av_row_params};

      $("#available_pages thead tr").each(function(i){
            var tr = $(this);
            tr.children().each(function(r,th){



                  if (th.innerHTML == '' ) {
                    $(this).css('display','none');
                   }

               });
        });
      $("#available_pages tbody tr").each(function(i){

            $(this).children().each(function(r){

              if ($(this).children().attr("type")=='hidden') {
                $(this).css('display','none');
               }

             if (row_parameters[i][r]==false) {
               $(this).children().attr('disabled','true');
              }
              else {
                $(this).children().removeAttr('disabled');
              }

               });
        });

        $("#selected_pages thead tr").each(function(i){
              var tr = $(this);
              tr.children().each(function(r,th){



                    if (th.innerHTML == '' ) {
                      $(this).css('display','none');
                     }

                 });
          });
        $("#selected_pages tbody tr").each(function(i){

              $(this).children().each(function(r){

                if ($(this).children().attr("type")=='hidden') {
                  $(this).css('display','none');
                 }

               if (row_parameters[i][r]==false) {
                 $(this).children().attr('disabled','true');
                }
                else {
                  $(this).children().removeAttr('disabled');
                }

                 });
          });





          var tb =  $("#tabs").tabs(
              {
                activate: function(event ,ui){

                  alert('activated');

                },
                create: function(event ,ui){

                if (ui.tab.children().hasClass('cms'))
                {
                  filter_tab('cms');

                }
                if (ui.tab.children().hasClass('categories'))
                {
                  filter_tab('categories');

                }
                if (ui.tab.children().hasClass('manual'))
                {
                  filter_tab('manual');

                }

              },
              beforeActivate: function(event ,ui){

              if (ui.newTab.children().hasClass('cms')) {
                  filter_tab('cms');
              }
              else if (ui.newTab.children().hasClass('categories')) {
                filter_tab('categories');

              }
              else if (ui.newTab.children().hasClass('manual')) {
                  filter_tab('manual');

              }

            },
             disabled: [ 3 ],


            });

        function filter_tab(tab_name){
          tr =  $("#selected_pages tbody tr").each(function(i,tr){

          var cat_show = 0;
          td = $(tr).children().each(function(ii,td){
            if ($(td).children('.page_type').val()==tab_name) {
              cat_show = 1;
            }
          });

          if (cat_show==0) {
            $(tr).hide();
          }  else {
            $(tr).show();
          }

          });
          tr =  $("#available_pages tbody tr").each(function(i,tr){

          var cat_show = 0;
          td = $(tr).children().each(function(ii,td){
            if ($(td).children('.page_type').val()==tab_name) {
              cat_show = 1;
            }
          });

          if (cat_show==0) {
            $(tr).hide();
          }  else {
            $(tr).show();
          }

          });
        }
        function add_pages()

        {
        available_pages = [];
        ap.getData().forEach(function(item, i) {

          if (item[0]===false) {
            available_pages.push(item);
          } else {
            selected_pages.push(item);
          }

        });

        $('#available_pages').children('.inputtable').remove();
         ap = $("#available_pages").editTable({

                field_templates: field_templates,
                    row_template: row_template,
                    headerCols: headerCols,
                    first_row: false,
                    data: available_pages,

          });

        $('#selected_pages').children('.inputtable').remove();
         sp = $("#selected_pages").editTable({

                field_templates: field_templates,
                    row_template: row_template,
                    headerCols: headerCols,
                    first_row: false,
                    data: selected_pages,

          });

        return false;
        }




      function remove_pages()

            {


            selected_pages = [];
            sp.getData().forEach(function(item, i) {

              if (item[0]===false) {
                selected_pages.push(item);
              } else {
                available_pages.push(item);
              }

            });

            $('#selected_pages').children('.inputtable').remove();
             sp = $("#selected_pages").editTable({

                    field_templates: field_templates,
                        row_template: row_template,
                        headerCols: headerCols,
                        first_row: false,
                        data: selected_pages,

              });

            $('#available_pages').children('.inputtable').remove();
             ap = $("#available_pages").editTable({

                    field_templates: field_templates,
                        row_template: row_template,
                        headerCols: headerCols,
                        first_row: false,
                        data: available_pages,

              });
              var current_index =$("#tabs").tabs("option","active");
              $('#tabs').tabs("load","#pages");

              alert('ddd='+current_index);
              console.log(tb);

            return false;
            }




                    $("#add_page").click(add_pages);
                    $("#remove_page").click(function() {

                      remove_pages();

                      });


            $("#selected_pages").closest('form').on('submit', function(e) {


              var form=$($(this));
              var input = '';

              sp.getData().forEach(function(item, i) {

                input = $("<input>")
                              .attr("type", "hidden")
                              .attr("name", "selected_pages["+i+"]").val(item);

                 form.append($(input));

                });


            });



    });



{/block}



{block name="input"}


 {if $input.type == 'p_list_choice'}
 <div id="tabs">
 	<ul>

    {foreach from=$page_tabs item=i}

      <li><a href="#pages" class={$i.id}>{$i.name}</a></li>

    {/foreach}
    <li><a href="#pages">test</a></li>

 	</ul>

  <div id="pages">

 <div class="row">
 <div class="col-sm-6">
   <h4>{l s='Selected pages' mod='asitemap'}</h4>
 <div id="selected_pages"></div>
 </div>
 <div class="col-sm-6">
   <h4>{l s='Available pages' mod='asitemap'}</h4>
   <div id="available_pages"></div>
 </div>
 </div>
 <div class="row">
         <div class="col-sm-6">
         <a href="#" id="remove_page" class="btn btn-default">
           <i class="icon-arrow-right"></i>{l s='Remove' mod='asitemap'}
         </a>
         </div>
         <div class="col-sm-1">
         <a href="#" id="add_page" class="btn btn-default">
           <i class="icon-arrow-left"></i>{l s='Add' mod='asitemap'}
         </a>
         </div>
 </div>
</div>


</div>


 {else}
      {$smarty.block.parent}
 {/if}


{/block}


{block name="after"}


<link rel="stylesheet" href="{$csstable}">

<script src={$jstable}></script>


<script src={$jstabsui}></script>
<script src=/ff/js/jquery/ui/jquery.ui.tabs.min.js></script>

<link rel="stylesheet" href="/ff/js/jquery/ui/themes/base/jquery.ui.all.css">

{literal}


	<script>

	</script>



{/literal}


{/block}
