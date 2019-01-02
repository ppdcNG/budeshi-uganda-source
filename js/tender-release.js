var parties = [];
var milestones = [];
var documents = [];
var items = [];
var tenderers = [];
var require_fields = ["tender-title", "tender-desc", "id"];
$("#save-release").click(function () {
    var valid = validateFields(require_fields);
    if(valid != true){
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }

    var release = form2js("release-form",".",false);
    var adate = new Date();
    release.date = adate.toISOString();
    release.parties = parties.filter(filter_undefined);
    release.tender.milestones = milestones.filter(filter_undefined);
    release.milestone = undefined;
    release.tender.items = items.filter(filter_undefined);
    release.tender.documents = documents.filter(filter_undefined);
    var value = JSON.stringify(release);
    console.log(value);
    var id = $("#project_id").val();
    var mda = $("#mda-id").val();
    var url =   ABS_PATH + "Release/transactadd/tender/" + id + "/" + mda;
    UIkit.notification("<div uk-spinner></div>",{status:'warning', timeout: 3000});
    $(this).attr('disabled','disabled');
    modalAction("#planning",value,url);
    setTimeout(function() {window.history.back();}, 3000);
});

$("#edit-release").click(function () {
    var valid = validateFields(require_fields);
    if(valid != true){
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    console.log("blah");
    var release = form2js("release-form", ".",false);
    var adate = new Date();
    release.date = adate.toISOString();
    release.parties = parties;
    release.tender.milestones = milestones.filter(filter_undefined);
    release.tender.items = items.filter(filter_undefined);
    release.tender.documents = documents.filter(filter_undefined)
    release.milestone = undefined;
    var value = JSON.stringify(release);
    console.log(value);
    var id = $("#project_id").val();
    var mda = $("#mda-id").val();
    var url =  ABS_PATH + "Release/transactedit/tender/" + id + "/" + mda;
    UIkit.notification("<div uk-spinner></div>",{status:'warning', timeout: 3000});
    $(this).attr('disabled','disabled');
    modalAction("#planning",value,url);
    setTimeout(function() {window.history.back();}, 3000);
});
$("#tender-org").change(function(){
    console.log("blah");
    var tenderer = $("#tender-org").val();
    var num = tenderer.length;
    console.log(tenderer);
    $("#numberOfTenderers").val(num);
});
//calculate duration function

console.log($("#tender-startDate"));
