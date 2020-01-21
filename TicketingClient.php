/*

Front-end of a ticketing service based on a DataTable
developed in HTML5, CSS3, Javascript and jQuery
using JSON messages

addresses, account names and sensitive informations
have been censored with the word "[censored]"

*/
<?php
require_once APP_PATH_DOCROOT . [censored];
require_once __DIR__ . [censored];
global $emn_params;

$projectObj = new [censored]($emn_params);
$projectObj->addCss('dashboard');
$base_url = $projectObj->getConf('base_url');
$role = $emn_params->user_rights['role_name'];
$projectName = $projectObj->Proj->project['app_title'];
?>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.1/dist/jquery.validate.min.js"></script>

<!----------------CSS-------------------->

<style>

#ajax-loader{
  display:none;
  height: 16px;
  width: 16px;
}

/* Datatable CSS */
  #drop{
      padding:25px;
      text-align:left;
      font:20pt bold,"Vollkorn";color:#bbb
  }

  a { text-decoration: none }
  .dataTable {
      font-size:0.8em;
      -webkit-touch-callout: none; /* iOS Safari */
      -webkit-user-select: none; /* Safari */
      -khtml-user-select: none; /* Konqueror HTML */
      -moz-user-select: none; /* Firefox */
          -ms-user-select: none; /* Internet Explorer/Edge */
              user-select: none; /* Non-prefixed version, currently
                                  supported by Chrome and Opera */
  }

  .dataTable td { height: 40px; }

/* OpenTicket dialog/button CSS */

  label, input { display:block; }
  input.text { margin-bottom:12px; width:95%; padding: .4em; }
  fieldset { padding:0; border:0; margin-top:25px; }
  h1 { font-size: 1.2em; margin: .6em 0; }
  div#users-contain { width: 350px; margin: 20px 0; }
  div#users-contain table { margin: 1em 0; border-collapse: collapse; width: 100%; }
  div#users-contain table td, div#users-contain table th { border: 1px solid #eee; padding: .6em 10px; text-align: left; }
  .ui-dialog .ui-state-error { padding: .3em; }
  .validateTips { border: 1px solid transparent; padding: 0.3em; }


  table {
    font-family: arial, sans-serif;
    border-collapse: collapse;
    width: 100%;
  }

  td, th {
    border: 1px solid #dddddd;
    text-align: left;
    padding: 8px;
  }

  tr:nth-child(even) {
    background-color: #dddddd;
  }

/* Red text for dialogs */

  .badfile{
    color : red;
  }

.active, .accordion:hover {
  background-color: #ccc;
}

.accordion:after {
  content: '\002B';
  color: #777;
  font-weight: bold;
  float: right;
  margin-left: 5px;
}

.active:after {
  content: "\2212";
}

.panel {
  padding: 0 18px;
  background-color: white;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.2s ease-out;
}

</style>

<!----------------HTML-------------------->

<br>
<div id="drop"></div><br>

<br><br>

<table id="ticketDataTable">
    <thead>
        <tr>
          <th></th>
          <th></th>
          <th> <!-- status filter -->
            <select id="status" class="filter">
              <option value="">All Status</option>
              <option value="Opened_InProgress" selected>Open and in progress</option>
              <option value="Opened">Opened</option>
              <option value="InProgress">In Progress</option>
              <option value="Closed">Closed</option>
            </select>
          </th>
          <th></th>
          <th></th>
          <th> <!-- category filter -->
            <select id="ticket_category" class="filter">
              <option value="">All Categories</option>
              <option value="sae">SAE</option>
              <option value="randomization_eligibility">Randomization/eligibility</option>
              <option value="query">Query</option>
              <option value="general">General</option>
              <?php if($role == '[censored]') echo'<option value="technical">Technical</option>' ?>
            </select>
          </th>
          <th></th>
          <th></th>
          <th></th>
        </tr>
        <tr> <!-- table header -->
          <th>Ticket no.</th>
          <th>Insert at</th>
          <th>Status</th>
          <th>Requester</th>
          <th>Last answerer</th>
          <th>Category</th>
          <th>Title</th>
          <th>Attachment</th>
          <th>Reply</th>
        </tr>
       
    </thead>
</table>

<!-- HTML Dialog Open Ticket -->

<div id="dialog-form-open-ticket" title="Open Ticket">
  
 
  <form id="ticketForm_open-new-ticket">
    <p>Category</p>
      <select id="open_ticket_filter">
        <option value=""></option>
        <option value="[censored]">[censored]</option>
        <option value="[censored]">[censored]</option>
        <option value="query">Query</option>
        <option value="general">General</option>
        <?php if($role == '[censored]') echo'<option value="technical">Technical</option>' ?>
      </select>
      <label for="title">Title</label>
      <input type="text" name="title" id="title" value="" class="text ui-widget-content ui-corner-all">
      <label for="content">Content</label>
      <textarea rows = "4" cols = "40" name = "content" value="" class="text ui-widget-content ui-corner-all"></textarea>
      <p style="color:blue;">If this is not a general request, please indicate patient ID </p>
      <p>All form fields are required.</p>

      <p class="validateTips"></p>

      <input class="input-file" id="fileInput" type="file" name="file">

      <p class="ext">Available extensions for the attachment: <br>
                     Text: .pdf, .doc, .docx <br>
                     Image: .jpg, .jpeg, .png <br>
                     Archive: .zip, .rar
      </p>

      <!-- Allow form submission with keyboard without duplicating the dialog button -->
      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
  </form>
</div>
<button id='ont' data-ticket_type='Request' class="open_new_ticket">Open new ticket</button>

<p><br><br>
  Summary of categories:<br><br>
	[censored]
</p>

<!-- HTML Dialog Reply -->

<div id="dialog-form-reply" title="Leave a response">
  
  <div id="myDialog">
    <div id="myDialogText">
    </div>
  </div>
 
  <form id="ticketForm_reply">
    <fieldset>
      <label for="title" id="title_r1">Title</label>
      <input type="text" name="title" id="title_r" value="" class="text ui-widget-content ui-corner-all" disabled>
      <label for="content" id="content_r1">Content</label>
      <textarea rows = "4" cols = "93" name = "content" id="content_r" value="" class="text ui-widget-content ui-corner-all"></textarea>
      <p class="validateTips"></p>
      <input class="input-file" id="fileInputR" type="file" name="file">

      <p class="ext" id="ext_reply">Available extensions for the attachment: <br>
                                    Text: .pdf, .doc, .docx <br>
                                    Image: .jpg, .jpeg, .png <br>
                                    Archive: .zip, .rar
      </p>
 
      <!-- Allow form submission with keyboard without duplicating the dialog button -->
      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
    </fieldset>
  </form>

  <table id="table_reply"></table>
</div>

<!----------------Javascript-------------------->

<script>

var pid = <?php echo $emn_params->project_id; ?>;
var base_url = "<?php echo $base_url; ?>";
var projectName = "<?php echo $projectName; ?>";
var role = "<?php echo $role; ?>";
var username = "<?php echo $emn_params->user; ?>";

$( document ).ready(function() {
  
  $("#drop").html('Ticketing System: '+projectName);

//---------------------DATATABLE------------------------------------------

    filters = {}; //filter array
    var url = "[censored]"+pid;
    datatable = $('#ticketDataTable').DataTable(
        {
            "processing": true,
            "serverSide": true, // server-side elab => true
            "order": [[ 0, "DESC" ]],
            'ajax' : {
                "url": url,
                "type": "POST",
            },

            "initComplete":function( settings, json){
            },

            "pagingType": "full_numbers", // paging buttons (prev,next,ecc)

            "fnServerParams" : function(aoData) { // send customFilter to server
                aoData['customFilters'] = filters;
            },
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
            columns: [
                { data: 'ticket_id', "className": 'details-control', "width": "7%" },

                { data: 'insert_at', "className": 'details-control', "width": "8%"},

                { data: 'status', "width": "2%", // status button
                  render: function ( data, type, row ) {
                    var btn_stat = ''
                    var data_style = ''
                    if(data == 'Opened'){
                      btn_stat = 'Close'
                      data_style = "green"
                    }
                    else if(data == 'InProgress'){
                      btn_stat = 'Close'
                      data_style = "blue"
                    }
                    else {
                      btn_stat = 'Open'
                      data_style = "red"
                    }

                    var displayed_status = ''
                    $("#status > option").each(function(i,el) {
                      if(this.value == row.status){
                        displayed_status = this.text
                      }
                    });

                    var text = ''
                    $("#ticket_category > option").each(function(i,el) {
                      if(this.value == row.ticket_category){
                        text = this.text
                      }
                    });
                    return "<button class='change_status' data-ticket_title='"+row.ticket_title+"'data-content='"+row.content+"' data-ticket_category='"+text+"' data-ui_id='"+row.ui_id+"' data-ticket_id='"+row.ticket_id+"' data-status='"+data+"' id="+data+">"+btn_stat+"</button> <ul style='display: inline-block;'><li style='color:"+data_style+";'>"+displayed_status+"</li></ul></span>"
                  }
                },

                { data: 'requester', "className": 'details-control', sortable:false,
                  render: function ( data, type, row ){
                    if(data != '[censored]' || role == '[censored]' || role == '[censored]') return row.username
                    else return data
                  }
                },

                { data: 'answerer',"className": 'details-control', "width": "10%", sortable:false,
                  render: function (data, type, full, row){ // last answerer
                    if(data == null) return "No one has answered"
                    else return '<div title="' + escapeHtml(full.content_r) + '">' + escapeHtml(data)
                  }
                },

                { data: 'ticket_category', "className": 'details-control', "width": "10%",
                  render: function (data, type, full, meta) { // show category
                    var text = ''
                    $("#ticket_category > option").each(function(i,el) {
                      value = this.value
                      if(this.value == data){
                        text = this.text
                      }
                    });
                    return text
                  }
                },

                { data: 'ticket_title', "className": 'details-control', "width": "40%",
                  render: function (data, type, full, meta) {
                    return type === 'display' ? '<div title="' + escapeHtml(full.content) + '">' + escapeHtml(data) : escapeHtml(data)
                  }
                },

                { data: 'filename', "className": 'details-control', "width": "30%",
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                      if(oData.filename != null)
                      $(nTd).html("<a href='"+base_url+"[censored]"+oData.filename+"' target='_blank'>"+oData.filename+"</a>")
                  }
                },

                { "width": "1%", sortable:false, // reply button
                  render: function ( data, type, row ) { // status near status button
                    var disabled = ''
                    if(row.status == 'Opened' || row.status == 'InProgress')
                      disabled = 'Opened'
                    else disabled = 'disabled'
                    return "<button data-requester='"+row.requester+"' data-ticket_type='Response' data-ticket_category='"+row.ticket_category+"' data-ticket_id='"+row.ticket_id+"' "+disabled+" class='reply' id='single_reply_"+row.ticket_id+"'>Reply</button>"
                  }
                }

            ],

               "drawCallback": function( settings ) {

                  var to = settings.json.start+settings.json.perPage
                  if(to > settings.json.recordsFiltered) to = settings.json.recordsFiltered
                  if(settings.json.recordsFiltered == 0) settings.json.start = 0
                  else if(settings.json.start == 0) settings.json.start = 1
                  $('#ticketDataTable_info').html('Showing '+settings.json.start+' to '+to+' of '+settings.json.recordsFiltered)

                  $('#dialog-form-reply .validateTips').text('Content fields is required.')

                  // PERMITS
                  if(settings.json.role != '[censored]'){ // simple user
                    $('.change_status').hide(); // don't open/close thread
                    $('#tecnical').hide() // does not send technical requests in reply form (but can reply)
                  }
                  else if(settings.json.role == '[censored]'){ // quindi è assimilabile ad un utente semplice?
                    $('.change_status').hide(); // don't open/close thread
                    $('#tecnical').hide() // does not send technical requests in reply form (but can reply)
                  }
                  else if(settings.json.role == '[censored]'){
                    $('.change_status').hide(); // don't open/close thread
                    $('#tecnical').hide() // does not send technical requests in reply form (but can reply)
                  }
                  else if(settings.json.username == '[censored]'){
                    $('.change_status').hide(); // don't open/close thread
                    $('#tecnical').hide() // does not send technical requests in reply form (but can reply)
                    
                  }
              }
            }
        );

      // print table rows
      $(".filter").each(function(i, el){
          $(el).change(function(){
              filters[$(this).attr("id")] = $(this).val();
              datatable.clear().draw(); // refresh datatable data from ajax call
          });
      });
    
    $('#ticketDataTable_filter').hide()
    $('#ticketDataTable_processing').hide()

    if(role == '[censored]' || username == '[censored]') $('#ont').hide()


//----------------BOTTONE CHANGE STATUS---------------------------------------

    $('body').delegate('.change_status','click', function (e){
      
      var ticket_id = $(e.target).data('ticket_id') // status button
      var ui_id = $(e.target).data('ui_id') // user_id
      var status = $(e.target).data('status') // Opened / Closed
      var category = $(e.target).data('ticket_category')
      var title = $(e.target).data('ticket_title')
      var content = $(e.target).data('content')

    if(status == 'Opened' || status == 'InProgress') $.blockUI({ message: 'Thread is closing, please wait' });
      else $.blockUI({ message: 'Thread is opening, please wait' });

      $.ajax({
            type: "POST",
            url: "[censored]"+pid,
            data: {
              status : status,
              ticket_id : ticket_id,
              ui_id : ui_id,
              category : category,
              title : title,
              content : content
            },
            success: function(data)
            {
              $('#ticketDataTable').DataTable().ajax.reload()
              $('#ticketDataTable_processing').hide()
              setTimeout(function() {
                $("div.blockMsg").text("Done");
                $.unblockUI();
              }, 1100);
            },
            error: function(data) { }
        });
    });

//-------------------OPEN DIALOG--------------------------------

var dialog, form,
    category = $( "#category" ),
    title = $( "#title" ),
    content = $( "#content" ),
    allFields = $( [] ).add( title ).add( content ),
    tips = $( ".validateTips" );

$('body').delegate('.reply','click', function (){ // reply button

  $('#ticketDataTable_processing').hide()
  var data = $(this).data()
  dialog_reply.data('data', data).dialog( "open" );
  $("#tecnical").attr('id', 'tecnical'+data.ticket_id);
  if((role == '[censored]' && data.requester == '[censored]') || role != '[censored]')
    $("#tecnical"+data.ticket_id).hide()
  else $("#tecnical"+data.ticket_id).show()

  if(username == '[censored]'){
    $("#Response").hide()
    $("#fileInputR").hide()
    $("#content_r").hide()
    $("#title_r").hide()
    $("#title_r1").hide()
    $("#content_r1").hide()
    $('.validateTips').hide()
    $('#ext_reply').hide()
  }

  getRequestText(data)
  setTableReply(data,role)
});

$( ".open_new_ticket" ).button().on( "click", function() { // new request popup
  var data = $(this).data()
  dialog_open_ticket.data('data', data).dialog( "open" );
});

    dialog_open_ticket = $( "#dialog-form-open-ticket" ).dialog({ // new request dialog
    autoOpen: false,
    height: 580,
    width: 355,
    modal: true,

    buttons: {
      "Open ticket": function(){
        addTicket(dialog_open_ticket.data().data)
      },
      Cancel: function() {
        dialog_open_ticket.dialog( "close" );
      }
    },
    close: function() {
      $("#ticketForm_open-new-ticket").trigger( "reset" );
      allFields.removeClass( "ui-state-error" );
    }
  });

  dialog_reply = $( "#dialog-form-reply" ).dialog({
    autoOpen: false,
    height: 600,
    width: 750,
    modal: true,

    buttons: {
      "Tecnical": {
      id: "tecnical",
      text: "Tecnical",
      click: function(){
        var data = dialog_reply.data().data
        data['ticket_type'] = 'Tecnical'
        addTicket(data)
      }
    },
      "Response": {
        id: "Response",
        text: "Response",
        click: function(){
          $('#ticketDataTable_processing').hide()
          var data = dialog_reply.data().data
          if(role == '[censored]') data['ticket_type'] = 'Tecnical'
          else data['ticket_type'] = 'Response'
          addTicket(data)
        }
      },
    Cancel: function() {
      $('#ticketDataTable_processing').hide()
      dialog_reply.dialog( "close" );
    }
  },
    close: function() {
      var data = dialog_reply.data().data
      $("#tecnical"+data.ticket_id).attr('id', 'tecnical');
      $('#ticketDataTable_processing').hide()
      $("#ticketForm_reply").trigger( "reset" );
      allFields.removeClass( "ui-state-error" );
    }
  });
 
//----------FUNCTIONS IN DIALOGS------------

    // show error
    function updateTips( t ) {
      tips
        .text( t )
        .addClass( "ui-state-highlight" )
      setTimeout(function() {
        tips.removeClass( "ui-state-highlight", 1500 )
      }, 500 );
      setTimeout(function() {
        t = ''
        tips.text(t)
      }, 4500);
    }
 
    // check lenght of fields in dialogs popup
    function checkLength( o, n, min, max ) {
      if(n == 'category'){
        if(o.length <1){
          updateTips("Select category")
          return false
        }
        else return true
      }
      else if(n == 'title' || n == 'content'){
        if ( o.length > max || o.length < min ) {
          updateTips( "Length of " + n + " must be between " +
                        min + " and " + max + "." );
          return false
        }
        else return true
      }
    }

    // send ticket to server
    function addTicket(data) {
      
      var message = ''
      
      allFields.removeClass( "ui-state-error" );

      var form_data = new FormData()

      if(data.ticket_type == 'Request'){

        message = 'Opening new ticket, please wait'

        var category = $('#open_ticket_filter').val()
        var category_text = $("#open_ticket_filter option:selected").text();
        var title = $("#ticketForm_open-new-ticket").serializeArray()[0].value
        var content = $("#ticketForm_open-new-ticket").serializeArray()[1].value

        valid_cat = checkLength( category, "category", null, null );
        valid_tit = checkLength( title, "title", 2, 40 );
        valid_con = checkLength( content, "content", 4, 1000 );
        

        if (!valid_cat || !valid_tit || !valid_con) return false

        form_data.append('title', title)
        form_data.append('category', category)
        form_data.append('category_text', category_text)
        form_data.append('ticket_id', null)
        if($('#fileInput').prop('files')[0] != null){
          var file_data = $('#fileInput').prop('files')[0]
          if(check_extention(file_data['name'])){
            form_data.append('file', file_data)
          }
          else{
            $(".ext").prepend("<p class=badfile>Wrong file selection!</p>").show();
            setTimeout(function () {
              $(".badfile").hide()
            },5000)
            return false
          }
        }
      }

      else if(data.ticket_type == 'Response' || data.ticket_type == 'Tecnical'){

        if(data.ticket_type == 'Response')
          message = 'Sending message, please wait'
        else message = 'Sending technical message, please wait'

        var title = $('#title_r').val()
        var content = $("#ticketForm_reply").serializeArray()[0].value
        var category = data.ticket_category;

        valid_con = checkLength( content, "content", 4, 1000 );

        if(!valid_con) return false

        form_data.append('category', category)
        form_data.append('ticket_id', data.ticket_id)
        if($('#fileInputR').prop('files')[0] != null){
          var file_data = $('#fileInputR').prop('files')[0]
          if(check_extention(file_data['name'])){
            form_data.append('file', file_data)
          }
          else{
            $(".ext").prepend("<p class=badfile>Wrong file selection!</p>").show();
            setTimeout(function () {
              $(".badfile").hide()
            },5000)
            return false
          }
        }
      }
      
      form_data.append('title', title)
      form_data.append('content', content)
      form_data.append('ticket_type', data.ticket_type)

      $.blockUI({ message: message });

      $.ajax({
            type: "POST",
            url: "[censored]"+pid,

            cache: false,
            contentType: false,
            processData: false,

            data : form_data,
            
            success: function(data)
            {
              if(data.ticket_type == 'Request') dialog_open_ticket.dialog( "close" );
              else setTableReply(data,role)
              $('#ticketDataTable').DataTable().ajax.reload();
              $('#ticketDataTable_processing').hide();
              $("div.blockMsg").text("Done");
                setTimeout(function() {
                  $.unblockUI();
                }, 1500);
            },
            error: function(data)
            {
                dialog_open_ticket.dialog( "close" );
                dialog_reply.dialog( "close" );
            }
        })
    }

  function check_extention(filename){
    var ext = /[^.]+$/.exec(filename);
    var allowedExt = ['pdf','doc','docx','jpg','jpeg','png','zip','rar']
    for(var i=0;i<allowedExt.length;i++){
      if(ext == allowedExt[i])
        return true
    }
    return false
  }

  // create and change dinamically table replying
  function setTableReply(data,role){
    $.ajax({
      type: "POST",
      url: "[censored]"+pid,
      data: data,

      success: function(data)
      {
        $('#table_reply').empty()

        if(role == '[censored]' || role == '[censored]' || username == '[censored]') type_header = '<th>Type</th>'
        else type_header = ''

        $('#table_reply').append(
          '<tr id="header">'+
            type_header+
            '<th>Insert at</th>'+
            '<th>Owner</th>'+
            '<th>Content</th>'+
            '<th>Attachment</th>'+
            '<th></th>'+
          '</tr>'
        )
        var content = ''
        var file = ''
        data.data.forEach( function(el){

          if(role == '[censored]' || role == '[censored]' || username == '[censored]') type_content = '<td>'+el.ticket_type+'</td>'
          else type_content = ''

          if(el.filename != null) file = '<a href="'[censored]'+el.filename+'" target="_blank">'+el.filename+'</a>'
          if(el.content.length > 14) content = el.content.substring(0, 15)+'...'
          else content = el.content
            $('#table_reply').append(
              '<tr class="accordion">'+
                type_content+
                '<td>'+el.insert_at+'</td>'+
                '<td>'+el.role_name+'</td>'+
                '<td id="content'+el.ticket_number+'">'+content+'</td>'+
                '<td>'+file+'</td>'+
              '</tr>'+
              '<tr>'+
                '<td colspan="10">'+el.content+'</td>'+
              '</tr>'
            )
          })

          $("#table_reply tr:not(.accordion)").hide()
          $("#table_reply tr:first-child").hide()

          $("#table_reply tr.accordion").click(function(){
              $(this).next("tr").fadeToggle(250);
          }).eq(0).trigger('click');
        
        $("#header").show()
      },
      error: function()
      {}
    })
  }


  // get data(title and content ticket) and show it in replying dialog
  function getRequestText(data){
    $.ajax({
      type: "POST",
      url: "[censored]"+pid,
      data: data,

      success: function(data)
      {
        var obj = ''
        data.data.forEach( function(el){
          obj = $("#myDialogText").text(htmlDecode(el.ticket_title)+'\n'+htmlDecode(el.content))
          obj.html(obj.html().replace(/\n/g,'<br/>'))
          $('#title_r').val(htmlDecode(el.ticket_title))
        })
      },
      error: function()
      {}
    });
  }

  function escapeHtml(text) {
    var map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };

    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
  }

  function htmlDecode(input){
    var e = document.createElement('div');
    e.innerHTML = input;
    return e.childNodes.length === 0 ? "" : e.childNodes[0].nodeValue;
  }

});
 
</script>
