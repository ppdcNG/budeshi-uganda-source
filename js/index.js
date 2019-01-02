const ABS_PATH = 'http://localhost/uganda/';
///organisation ajax search
$(document).ready(function () {
    $('#select-org').select2({
        ajax: {
            url: ABS_PATH + "Release/getorg",
            type: "get",
            dataType: 'json',
            data: function (params) {
                console.log(params.term);
                return { searchText: params.term }
            },
            processResults: function (data) {
                console.log(data);
                //var obj = JSON.parse(data);
                //console.log(obj);
                return {
                    results: data
                }
            },
            cache: true
        }
    });

    $('.org').select2({
        ajax: {
            url: ABS_PATH + "Release/getorg",
            type: "get",
            dataType: 'json',
            data: function (params) {
                console.log(params.term);
                return { searchText: params.term }
            },
            processResults: function (data) {
                console.log(data);
                //var obj = JSON.parse(data);
                //console.log(obj);
                return {
                    results: data
                }
            },
            cache: true
        }
    });



    $('#tender-org').select2({
        ajax: {
            url: ABS_PATH + "Release/getorg",
            type: "get",
            dataType: 'json',
            data: function (params) {
                console.log(params.term);
                return { searchText: params.term }
            },
            processResults: function (data) {
                console.log(data);
                //var obj = JSON.parse(data);
                //console.log(obj);
                return {
                    results: data
                }
            },
            cache: true
        }
    });
    $("#file-input").change(function (e) {
        if (this.files[0]) {
            var type = this.files[0].type;
            var name = this.files[0].name;
            $("#select-file").html(name);
            $("#doc-format").val(type);
        }
    });
    //document upload function
    /*  var progressBar = $("#progressbar")[0];
      UIkit.upload('.document-upload', {
          url: ABS_PATH + "Release/ajaxdocument",
          type: "POST",
          multiple: false,
          loadStart: function (e) {
              console.log("ajax started");
              progressBar.removeAttribute("hidden");
              progressBar.max = e.total;
              progressBar.value = e.loaded;
          },
          progress: function (e) {
              console.log("making progress");
              progressBar.max = e.total;
              progressBar.value = e.loaded;
          },
          loadEnd: function (e) {
              progressBar.setAttribute("hidden", "hidden");
              UIkit.notification("File uploaded", { status: "success", timeout: 2000 });
          },
          error: function (e) {
              UIkit.notification("File uploade failed", { status: "danger", timeout: 2000 });
          },
          complete: function (e) {
              console.log(arguments[0].responseText);
              var data = arguments[0].responseText.split("**");
              $("#select-file").prop("disabled", true);
              $("#file-input").prop("disabled", true);
              $("#document-uri").val(data[0]);
              $("#doc-id").val(data[1]);
              $("#doc-format").val(data[2]);
  
              console.log(arguments);
              console.log(arguments.statusText);
          }
      });*/
});
function uploadDocument(to_url, inputElement) {
    var file_data = $("#" + inputElement).prop("files")[0];
    console.log(file_data);
    var formData = new FormData();
    var returnvalue = false;
    formData.append("file", file_data);
    console.log(formData.get("file"));
    $.ajax({
        url: to_url,
        contentType: false,
        processData: false,
        type: 'post',
        data: formData,
        success: function (data) {
            console.log(data);
            var resp = proccessJson(data)
            if (resp.ajaxstatus == 'success') {
                UIkit.notification("File Upload Successful", { status: "success", timeout: 1500 });
                var doc_obj = form2js("documents", ".", false);
                console.log(doc_obj);
                if (doc_obj.dateModified != "" && doc_obj.datePublished != "") {
                    var date_modified = new Date(doc_obj.dateModified);
                    var date_published = new Date(doc_obj.datePublished);
                    doc_obj.dateModified = date_modified.toISOString();
                    doc_obj.datePublished = date_published.toISOString();
                }
                doc_obj.id = documents.length + 1;
                doc_obj.url = resp.url;
                documents.push(doc_obj);
                console.log(documents);
                num = documents.length - 1;
                showDocument(num, doc_obj.title, doc_obj.documentType, doc_obj.format, doc_obj.url);
                $("#documents")[0].reset();
            }
            else {
                UIKit.notification("File Upload Failed", { status: "danger", timeout: 1500 });
                returnvalue = false
            }
        }
    });
    return returnvalue;
}
function calculateDuration(start_id, end_id, duration_id) {
    var start_date = $("#" + start_id).val();
    var end_date = $("#" + end_id).val();
    console.log(start_date);
    console.log(end_date);
    if (start_date == undefined || end_date == undefined) {
        $("#" + duration_id).val(0);
    }
    else {
        start_date = new Date(start_date);
        end_date = new Date(end_date);
        var duration = end_date - start_date;
        duration = duration / 86400000;
        console.log(duration);
        $("#" + duration_id).val(duration);
    }
}
function calculateDays(startDate, endDate) {

    var date1 = startDate;
    var date2 = endDate;
    var intv = date2 - date1;
    date1 = date1.getTime();
    date2 = date2.getTime();
    console.log(date1);
    var interval = date2 - date1;
    var days = interval / 86400000;
   
    var realdays = intv / 86400000;
    console.log(" days = " + days);
    days +=1;
    var sundays = [];
    let pub = [];
    var i;
    var abv = [];
    for (i = date1; i <= date2; i += 86400000) {
        
        var date = new Date(i);
        console.log(i + "-" + date);
        abv.push(date);
        var day = date.getDay();
        if (day == 00 || day ==06) {
            days = days - 1;
            sundays.push(date);
        }
        
    }
    console.log(abv);
    console.log("holidays");
    console.log(pub.length);
    console.log(pub);
    console.log("sundays");
    console.log(sundays)
    if(days < 0){
        days = 0;
    }
    return days;
}
function getDaysResponse(start_date, end_date, duration) {
    var reqdate = $("#"+start_date).val();
    var respdate = $("#"+ end_date).val();

    var error;
    var aerror;
    var mainerror;
    if ((respdate == "" || respdate == null) || (reqdate == "" || reqdate == null)) {
        error = respdate == "" || respdate == null ? "empty response date" : "";
        aerror = (reqdate == "" || reqdate == null) ? "empty request date" : "";
        mainerror = error == "" ? aerror : error;
        $("#"+duration).val(mainerror);
        $("#"+duration).css("color", "red");

        return;
    }
    var date1 = new Date(reqdate);
    var date2 = new Date(respdate);
    var days;
    if (date1 > date2) {
        $("#"+duration).val("start date later than end date");
        $("#days").css("color", "red");
        return;
    }
    else {
        days = calculateDays(date1, date2);
        $("#"+duration).val(days);
        $("#"+duration).css("color", "green");


    }
    var yes = date1 > date2;
    console.log(yes);
    console.log(reqdate);
    console.log(respdate);
}
function viewProject() {
    var page = document.getElementById('projects-cards');
    var proj = document.getElementById('ubecproject');
    var projOv = document.getElementById('project-overview');
    var ovTab = document.getElementById('ov-table');
    if (page.style.display === 'none') {
        page.style.display = 'block';
        proj.style.display = 'none';
        projOv.style.display = 'none';
        ovTab.style.display = 'block';

    } else {
        page.style.display = 'none';
        proj.style.display = 'block';
        projOv.style.display = 'block';
        ovTab.style.display = 'none';
    }
}

//Start add functions
function addParty() {
    var id = $("#select-org").val();
    var roles = String($("#party-role").val()).split(",");
    var name = $("#select-org option:selected").html();
    console.log(name);
    var party = {};
    party.id = id;
    party.roles = roles;
    parties.push(party);
    var num = parties.length - 1;
    showParty(num, name);
    console.log(roles);
    console.log(party);
}
function addMilestone() {
    let required_fields = ["mil-title", "mil-type", "mil-desc", "mil-status"];
    var valid = validateFields(required_fields);
    if (valid != true) {
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    var release = form2js("release-form", ".", false);
    console.log(release.milestone);

    var date = new Date();
    if (release.milestone.dateMet) {
        var date_met = new Date(release.milestone.dateMet);
        release.milestone.dateMet = date_met.toISOString();
    }
    if (release.milestone.due_date) {
        var due_date = new Date(release.milestone.dueDate);
        release.milestone.dueDate = due_date.toISOString();
    }
    release.milestone.dateModified = date.toISOString();
    milestones.push(release.milestone);
    var num = milestones.length;
    showMilestone(num);
    console.log(milestones);

}
function addItem() {
    let required_fields = ["item-desc"];
    var valid = validateFields(required_fields);
    if (valid != true) {
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    let require_fields = [""]
    var items_obj = form2js("items", ".", false);
    console.log(items_obj);
    items.push(items_obj);
    num = items.length;
    showItem(num, items_obj.description, items_obj.quantity);
    console.log(items);

}
function addDocument() {
    let required_fields = ["doc-title", "doc-description", "documentType"];
    var uploadSuccess = true;
    var valid = validateFields(required_fields);
    if (valid != true) {
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    ///Handle File upload;
    var file = $("#file-input").val();
    
    var documentType = $("#documentType").val();
    console.log(documentType);
    if (file != "" && file != undefined) {
        let url = ABS_PATH + "Release/ajaxdocument/" + documentType;
        uploadSuccess = uploadDocument(url, "file-input");
    }
    else {
        var doc_obj = form2js("documents", ".", false);
        console.log(doc_obj);
        if (doc_obj.dateModified != "" && doc_obj.datePublished != "") {
            var date_modified = new Date(doc_obj.dateModified);
            var date_published = new Date(doc_obj.datePublished);
            doc_obj.dateModified = date_modified.toISOString();
            doc_obj.datePublished = date_published.toISOString();
        }
        doc_obj.id = documents.length + 1;
        documents.push(doc_obj);
        console.log(documents);
        num = documents.length - 1;
        showDocument(num, doc_obj.title, doc_obj.documentType, doc_obj.format, doc_obj.url);
        $("#documents")[0].reset();
        $("#select-file").html('Select FILE');
    }
    
}
function addAmendment() {
    let required_fields = ["amendment-decription", "amendsReleaseID"];
    var valid = validateFields(required_fields);
    if (valid != true) {
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    var amends_obj = form2js("amendments", ".", false);
    let date = new Date(amends_obj.date);
    amends_obj.date = date.toISOString();
    let id = amendments.length + 1;
    id = to4digits(id);
    amends_obj.id = id;
    amendments.push(amends_obj);
    console.log(amendments);
    showAmendments(amends_obj.id, amends_obj.description, amends_obj.rationale);
}

function addTransaction() {
    let required_fields = ["value-amount", "payer", "payee"];
    var valid = validateFields(required_fields);
    if (valid != true) {
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    var transaction = form2js("transaction", ".", false);
    if (transaction.date != "") {
        let date = new Date(transaction.date);
        transaction.date = date.toISOString();
    }
    let id = transactions.length + 1;
    id = to4digits(id);
    transaction.id = id;
    transactions.push(transaction);
    console.log(transactions);
    showTransactions(transaction.id, transaction.source, transaction.source);
}
//End add functions

//Start remove functions
function removeParty(id) {
    $("#party-card" + id).remove();
    id = parseInt(id);
    console.log(id);
    console.log("blah");
    delete parties[id];
    console.log(parties);
}
function removeMilestone(id) {
    $("#milestone" + id).remove();
    id = parseInt(id);

    delete milestones[id - 1];
    console.log(milestones);
}
function removeItem(id) {
    $("#item-card" + id).remove();
    id = parseInt(id);
    delete items[id - 1];
    console.log(items);
}
function removeAmendment(id) {
    $("#amendment-card" + id).remove();
    id = parseInt(id);
    delete amendments[id - 1];
    console.log(amendments);
}
function removeDocument(id, document,type) {
    var to_url = ABS_PATH + "Release/delajaxdocument/" + type;
    var data_obj = { data: document }
    $.ajax({
        type: "post",
        data: data_obj,
        url: to_url,
        content: "application/json",
        success: function (data) {
            console.log(data);
            //var d_modal = UIkit.modal(modal);
            //d_modal.hide();
            var returnedData = proccessJson(data);
            if (returnedData.ajaxstatus == "success") {
                $("#" + id).remove();
                var num = parseInt(id);
                delete documents[num];
                console.log(documents);
                UIkit.notification(returnedData.message, { status: 'success', timeout: 3000 });
            }
            else {
                $("#" + id).remove();
                var num = parseInt(id);
                delete documents[num];
                console.log(documents);
                UIkit.notification(returnedData.message, { status: 'danger', timeout: 3000 });
                console.log(returnedData.message);
            }
        }
    });

}

function removeTransaction(id) {
    $("#transaction-card" + id).remove();
    id = parseInt(id);
    delete transactions[id - 1];
    console.log(transactions);
}
//End Remove functions

///Show Functions 
function showParty(id, name) {
    $("#parties").append(`<div id='party-card` + id + `' class='uk-section-mute uk-hover'>
                <table class='uk-table uk-table-divider'>
                <tbody><tr><td>`+ name + `</td>
                <td><a  id='remove-party' title='Delete Party' uk-tooltip='pos: bottom' class='uk-float-right'
                 onclick='removeParty("`+ id + `")' ><span class= 'uk-margin-small-right' uk-icon='icon: trash'></span></a>
                </td>
                </tr>
               </tbody>
             </table>
            </div>`);
}
function showMilestone(id) {
    $("#milestones").append(`<div id = "milestone` + id + `">
                            <div class="uk-card uk-card-default uk-card-body">Milestone ` + id + `
                            <a id = "del" onclick = "removeMilestone('`+ id + `')"  uk-tooltip='pos: bottom' class='uk-float-right'
                            ><span class= 'uk-margin-small-right' uk-icon='icon: trash'></span></a>
                                </div>
                            </div>`);
}
function showItem(id, name, quantity) {
    name = name.substring(0, 30) + "...";
    $("#items-container").append(`<div id='item-card` + id + `'>
                            <div class="uk-card uk-card-small uk-card-secondary uk-card-hover uk-card-body uk-light uk-margin">
                                <h3 class="uk-card-title" id='item-des-card-display'>`+ name + `</h3>
                                <div class='uk-display-block'>
                                    Quantity:` + quantity + `
                                </div>
                                <div class="uk-card-footer">
                                    <a href="#view-item" title="View Item" uk-tooltip="pos: bottom" uk-toggle class='uk-float-left'><span class="uk-margin-small-right" uk-icon="icon: expand"></span></a>
                                    <a href="#" id='remove-item' title="Delete Item" uk-tooltip="pos: bottom" class='uk-float-right' onclick="removeItem('`+ id + `')" data-message="<span uk-icon='icon: check'></span> Removed Item"
                                        data-status="success"><span class="uk-margin-small-right" uk-icon="icon: trash"></span></a>
                                </div>

                            </div>
                        </div>`);
}

function showDocument(id, title, type, format, document) {
    title = title.substring(0, 30) + "...";
    $("#documents-container").append(`<div id='` + id + `'>
                            <div class="uk-card uk-card-small uk-card-secondary uk-card-hover uk-card-body uk-light uk-margin">
                                <h3 class="uk-card-title">`+ title + `</h3>
                                <div class='uk-display-block'>`+
        format +
        `</div>
                                <div class='uk-display-block'>`+
        type + `
                                </div>
                                <div class="uk-card-footer">
                                    <a href="#view-document" title="View Document" uk-tooltip="pos: bottom" uk-toggle class='uk-float-left'><span class="uk-margin-small-right" uk-icon="icon: expand"></span></a>
                                    <a href="#" id='remove-ducument' title="Delete Document" uk-tooltip="pos: bottom" class='uk-float-right' onclick="removeDocument('`+ id + `','` + document + `','`+type+`')"
                                        data-message="<span uk-icon='icon: check'></span> Removed Document" data-status="success"><span class="uk-margin-small-right" uk-icon="icon: trash"></span></a>
                                </div>
                            </div>
                        </div>`);
}

function showAmendments(id, description, rationale) {
    $("#amendments-container").append(`
                        <div id='amendment-card`+ id + `'>
                            <div class="uk-card uk-card-small uk-card-secondary uk-card-hover uk-card-body uk-light uk-margin">
                                <h3 class="uk-card-title">`+ description + `</h3>
                                <div class='uk-display-block'>`+
        rationale +
        `</div>
                                <div class="uk-card-footer">
                                    <a href="#view-amendment" title="View Amendment" uk-tooltip="pos: bottom" uk-toggle class='uk-float-left'><span class="uk-margin-small-right" uk-icon="icon: expand"></span></a>
                                    <a href="#" id='remove-amendment' title="Delete Amendment" uk-tooltip="pos: bottom" class='uk-float-right' onclick="removeAmendment('`+ id + `')"
                                        data-message="<span uk-icon='icon: check'></span> Removed Party" data-status="success"><span class="uk-margin-small-right" uk-icon="icon: trash"></span></a>
                                    
                                </div>
                            </div>
                        </div>`);
}

function showTransactions(id, title, amount, currency = "NGN") {
    console.log($("#transaction-container"));
    $("#transaction-container").append(`<div id='transaction-card` + id + `'>
                            <div class="uk-card uk-card-small uk-card-secondary uk-card-hover uk-card-body uk-light uk-margin">
                                <h3 class="uk-card-title">`+ title + `</h3>
                                <div class='uk-display-block'>
                                    `+ amount + `: ` + currency + `
                                </div>
                                <div class="uk-card-footer">
                                    <a href="#view-transaction" title="View Party" uk-tooltip="pos: bottom" uk-toggle class='uk-float-left'><span class="uk-margin-small-right" uk-icon="icon: expand"></span></a>
                                    <a href="#" id='remove-transaction' title="Delete Transaction" uk-tooltip="pos: bottom" class='uk-float-right' onclick="removeTransaction(`+ id + `)"
                                        data-message="<span uk-icon='icon: check'></span> Removed Transaction" data-status="success"><span class="uk-margin-small-right" uk-icon="icon: trash"></span></a>
                                </div>
                            </div>
                        </div>`);
}
// End show functions

function modalAction(modal, json_data, to_url) {
    var dataObject = { data: json_data }
    $.ajax({
        type: "post",
        data: dataObject,
        url: to_url,
        content: "application/json",
        success: function (data) {
            console.log(data);
            var returnedData = proccessJson(data);
            if (returnedData.ajaxstatus == "success") {
                UIkit.notification.closeAll();
                UIkit.notification(returnedData.message, { status: 'success', timeout: 3000 });
            }
            else {
                UIkit.notification.closeAll();
                UIkit.notification(returnedData.message, { status: 'danger', timeout: 3000 });
                console.log(returnedData.message);
            }
        }
    });
}
function proccessJson(data) {
    var obj = JSON.parse(data);
    return obj;
}
function validateFields(fieldsArray) {
    var empty;
    for (var i = 0; i < fieldsArray.length; i++) {
        var val = $("#" + fieldsArray[i]).val();
        if (val == "" || val == undefined) {
            empty = fieldsArray[i] + " not set";
            break;
        }
        else {
            empty = true;
        }
    }
    return empty;
}
function to4digits(num) {
    let str = "" + 1;
    let pad = "0000";
    num = pad.substring(0, pad.length - str.length) + str;
    return str;
}
function filter_undefined(data) {
    console.log(data);
    return data !== undefined;
}
function releaseElements(elements) {
    for (let i = 0; i < elements.length; i++) {
        $("#" + elements[0]).removeAttr("disabled");
    }
}
function ajaxrequest(modal, json_data, to_url, call_back) {
    var dataObject = { data: json_data }
    $.ajax({
        type: "post",
        data: dataObject,
        url: to_url,
        content: "application/json",
        success: call_back,
        complete: function(){},
        beforSend: function(){}
    });
}
