{extends file="helpers/form/form.tpl"}



{block name="script"}

$(document).ready(function(){


  var  frequency_html='<select>';

        {foreach from=$frequency key=myId item=i}
        frequency_html += '<option id={$i.id}  >{$i.name}</option>';
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

      {* console.log({$row_parameters});   *}
      var headerCols = ['&nbsp;&nbsp;#&nbsp;&nbsp;','','','{l s='Page name' mod='asitemap'}','{l s='Frequency' mod='asitemap'}','{l s='Priority' mod='asitemap'}'];



      var selected_pages = {$selected_pages};
      var available_pages = {$available_pages};

      var row_parameters = {$row_params};

      //console.log(row_parameters);

    //  console.log(selected_pages);


      if (selected_pages.manual.length == 0)
      {
        selected_pages.manual.push([false,null,"manual","","monthly","0.5"])
        //console.log(selected_pages.cms);
      }


{foreach from=$page_tabs item=i}



  var sp_{$i.id} =  $("#selected_{$i.id}").editTable({
            tableClass: 'inputtable'+' tbl_selected_{$i.id}',
            field_templates: field_templates,
                row_template: row_template,
                headerCols: headerCols,
                first_row: false,

                data: selected_pages.{$i.id},

  });

  $("#selected_{$i.id} thead tr").each(function(i){
        var tr = $(this);
        tr.children().each(function(r,th){

            if (th.innerHTML == '' ) {
                $(this).css('display','none');
                 }

        });
  });



  $("#selected_{$i.id} tbody tr").each(function(i){

        $(this).children().each(function(r){


          if ($(this).children().attr("type")=='hidden'
          {if $i.id!='manual'}
            || $(this).children().hasClass("addrow")
            || $(this).children().hasClass("delrow")
          {/if}
              ) {
              $(this).css('display','none');
          }


          if (row_parameters.{$i.id}[r]==false) {
                $(this).children().attr('disabled','true');
          }
          else {
               $(this).children().removeAttr('disabled');
          }

        });
  });

  $('.tbl_selected_{$i.id} tbody').pageMe({
                pagerSelector:'#se_pag_{$i.id}',
                showPrevNext:true,
                hidePageNumbers:false,
                perPage:10,
                });

  {if $i.id!='manual'}

    var ap_{$i.id} = $("#available_{$i.id}").editTable({
            tableClass: 'inputtable'+' tbl_available_{$i.id}',
            field_templates: field_templates,
                row_template: row_template,
                headerCols: headerCols,
                first_row: false,
                data: available_pages.{$i.id},
      });

      $("#available_{$i.id} thead tr").each(function(i){
          var tr = $(this);
          tr.children().each(function(r,th){

              if (th.innerHTML == '' ) {
                  $(this).css('display','none');
                   }

          });
      });


      $("#available_{$i.id} tbody tr").each(function(i){

          $(this).children().each(function(r){

            if ($(this).children().attr("type")=='hidden'
              || $(this).children().hasClass("addrow")
              || $(this).children().hasClass("delrow")
                ) {
                $(this).css('display','none');
            }

            if (row_parameters.{$i.id}[r]==false) {
                 $(this).children().attr('disabled','true');
            }
            else {
                 $(this).children().removeAttr('disabled');
            }

          });
      });

    $('.tbl_available_{$i.id} tbody').pageMe({
                  pagerSelector:'#av_pag_{$i.id}',
                  showPrevNext:true,
                  hidePageNumbers:false,
                  perPage:10,
                  });

  {/if}

{/foreach}

    var tb =  $("#selected_pages").tabs(
        {
          activate: function(event ,ui){



          },
          create: function(event ,ui){



        },
        beforeActivate: function(event ,ui){

        },


       disabled: [ 3 ],


      });



  function update_pages(t,p_t)
  {




  {foreach from=$page_tabs item=i}

    if (p_t=="{$i.id}") {

          if (t==0) {

            selected_pages.{$i.id} = [];

            sp_{$i.id}.getData().forEach(function(item, i) {

              if (item[0]===false) {
                selected_pages.{$i.id}.push(item);
              } else {
                available_pages.{$i.id}.push(item);
              }

            });
          } else {

            available_pages.{$i.id} = [];
            ap_{$i.id}.getData().forEach(function(item, i) {

              if (item[0]===false) {
                available_pages.{$i.id}.push(item);
              } else {
                selected_pages.{$i.id}.push(item);
              }
            });

          }

            $('#selected_{$i.id}').children('.inputtable').remove();
             sp_{$i.id} = $("#selected_{$i.id}").editTable({
                    tableClass: 'inputtable'+' tbl_selected_{$i.id}',
                    field_templates: field_templates,
                        row_template: row_template,
                        headerCols: headerCols,
                        first_row: false,
                        data: selected_pages.{$i.id},

              });

            $('#available_{$i.id}').children('.inputtable').remove();
             ap_{$i.id} = $("#available_{$i.id}").editTable({
                        tableClass: 'inputtable'+' tbl_available_{$i.id}',
                        field_templates: field_templates,
                        row_template: row_template,
                        headerCols: headerCols,
                        first_row: false,
                        data: available_pages.{$i.id},

              });

              $("#selected_{$i.id} thead tr").each(function(i){
                  var tr = $(this);
                  tr.children().each(function(r,th){

                      if (th.innerHTML == '' ) {
                          $(this).css('display','none');
                           }

                  });
              });

              $("#available_{$i.id} thead tr").each(function(i){
                  var tr = $(this);
                  tr.children().each(function(r,th){

                      if (th.innerHTML == '' ) {
                          $(this).css('display','none');
                           }

                  });
              });

              $("#available_{$i.id} tbody tr").each(function(i){

                  $(this).children().each(function(r){

                    if ($(this).children().attr("type")=='hidden'


                    {if $i.id!='manual'}

                    || $(this).children().hasClass("addrow")
                    || $(this).children().hasClass("delrow")

                    {/if}


                    ) {


                        $(this).css('display','none');
                    }

                    if (row_parameters.{$i.id}[r]==false) {
                         $(this).children().attr('disabled','true');
                    }
                    else {
                         $(this).children().removeAttr('disabled');
                    }

                  });
              });

              $("#selected_{$i.id} tbody tr").each(function(i){

                  $(this).children().each(function(r){

                    if ($(this).children().attr("type")=='hidden'


                    {if $i.id!='manual'}

                    || $(this).children().hasClass("addrow")
                    || $(this).children().hasClass("delrow")

                    {/if}



                    ) {
                          $(this).css('display','none');
                    }

                    if (row_parameters.{$i.id}[r]==false) {
                          $(this).children().attr('disabled','true');
                    }
                    else {
                         $(this).children().removeAttr('disabled');
                    }

                  });
              });
    }

    $("#av_pag_{$i.id}").empty();
    $("#se_pag_{$i.id}").empty();
    $('.tbl_available_{$i.id} tbody').pageMe({
                  pagerSelector:'#av_pag_{$i.id}',
                  showPrevNext:true,
                  hidePageNumbers:false,
                  perPage:10,
                  });
    $('.tbl_selected_{$i.id} tbody').pageMe({
                  pagerSelector:'#se_pag_{$i.id}',
                  showPrevNext:true,
                  hidePageNumbers:false,
                  perPage:10,
                  });
  {/foreach}



  return false;
  }

            $(".add_page").click(function() {
              p_t =  $(this).attr('page_type');
              update_pages(1,p_t);
              return false;

                      });
            $(".remove_page").click(function() {
              p_t =  $(this).attr('page_type');
              update_pages(0,p_t);
                return false;

                      });

  $('.inputtable').on('click','.addrow', function() {

    var bt=$(this);

    setTimeout(function() {
        pt = $(bt).closest('tr').children().children('.page_type').val();
        //console.log($(bt).closest('tr').children().children('.page_type'));


        $(bt).closest('tr').next().children().each(function(r){

        //  console.log($(this));
        //  console.log(r);

          if ($(this).children().attr("type")=='hidden') {
                $(this).css('display','none');
          }

          if (row_parameters[pt][r]==false) {
                $(this).children().attr('disabled','true');
          }
          else {
               $(this).children().removeAttr('disabled');
          }
          if (r==2) {

            $(this).children().val(pt);
          }
          if (r==4) {

            $(this).children().children("option[id='4']").attr('selected','selected');
            //$(this).children().attr('page_type',pt);
          }
          if (r==5) {
            $(this).children().val('0.5');
          }


        });

      //  alert(pt);

      }, 100);


  });


    $('.inputtable thead tr').on('click','th', function() {

      var h = $(this);

    //  console.log($(h).text());
      if ($(h).text().trim()=="#")

      var state = false;

            $(h).parent().parent().next().children('tr:visible').each(function(r,i){



              if (r==0) {

                state = $(this).children().children('input[type="checkbox"]').prop('checked');

                if (state == true) {
                  state = false;
                } else
                {
                  state = true;
                }

              }

              $(this).children().children('input[type="checkbox"]').prop('checked',state);

            });


      });


          $("#selected_pages").closest('form').on('submit', function(e) {


          var form=$($(this));
          var input = '';

          {foreach from=$page_tabs item=i}

          sp_{$i.id}.getData().forEach(function(item, i) {

            item[3]=item[3].replace(',',escape(','));

          input = $("<input>")
                    .attr("type", "hidden")
                    .attr("name", "selected_{$i.id}["+i+"]").val(item);

          form.append($(input));


          });

          {/foreach}

          });



});



{/block}



{block name="input"}


 {if $input.type == 'p_list_choice'}
 <div id="selected_pages">
 	<ul>

    {foreach from=$page_tabs item=i}

      <li><a href="#{$i.id}" class={$i.id}>{$i.name}</a></li>

    {/foreach}


 	</ul>


  {foreach from=$page_tabs item=i}

    {if $i.id=='manual'}


    <div id="{$i.id}">

      <div class="row">
       <div class="col-sm-12">
         <h4>{l s='Selected pages' mod='asitemap'}</h4>
         <div id="selected_{$i.id}"></div>
         <div class="col-md-12 text-center">
            <ul class="pagination pagination-sm pager " id="se_pag_{$i.id}"></ul>
        </div>
       </div>
     </div>
    </div>
    {else}

    <div id="{$i.id}">

      <div class="row">
       <div class="col-sm-6">
         <h4>{l s='Selected pages' mod='asitemap'}</h4>
         <div id="selected_{$i.id}"></div>
         <div class="col-md-12 text-center">
            <ul class="pagination pagination-sm pager " id="se_pag_{$i.id}"></ul>
        </div>
       </div>
       <div class="col-sm-6">
         <h4>{l s='Available pages' mod='asitemap'}</h4>
         <div id="available_{$i.id}"></div>
         <div class="col-md-12 text-center">
            <ul class="pagination pagination-sm pager " id="av_pag_{$i.id}"></ul>
        </div>
       </div>
      </div>
      <div class="row">
        <div class="col-sm-6">
         <a href="#" page_type="{$i.id}" class="remove_page btn btn-default">
           <i class="icon-arrow-right"></i>{l s='Remove' mod='asitemap'}
         </a>
        </div>
        <div class="col-sm-1">
         <a href="#" page_type="{$i.id}" class="add_page btn btn-default">
           <i class="icon-arrow-left"></i>{l s='Add' mod='asitemap'}
         </a>
        </div>
      </div>
    </div>

    {/if}
  {/foreach}


</div>

 {else}
      {$smarty.block.parent}
 {/if}


{/block}


{block name="after"}


<link rel="stylesheet" href="{$csstable}">
<link rel="stylesheet" href="{$cssui}">

<script src={$jstable}></script>
<script src={$jspag}></script>


<script src={$jstabsui}></script>




{literal}


	<script>

	</script>



{/literal}


{/block}
