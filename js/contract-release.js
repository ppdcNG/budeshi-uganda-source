var parties = [];
var milestones = [];
var documents = [];
var items = [];
var tenderers = [];
var amendments = [];
var require_fields = ["contract-title", "contract-desc", "value-amount", "award-id", "id"];

$("#save-release").click(function () {
    var valid = validateFields(require_fields);
    if(valid != true){
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    var release = form2js("release-form", ".", false);
    var adate = new Date();
    release.date = adate.toISOString();
    release.contract.dateSigned;
    release.parties = parties.filter(filter_undefined);
    release.contract.milestones = milestones.filter(filter_undefined);
    release.contract.items = items.filter(filter_undefined);
    release.contract.documents = documents.filter(filter_undefined);
    release.contract.amendments = amendments.filter(filter_undefined);
    release.milestone = undefined;
    var value = JSON.stringify(release);
    console.log(value);
    var id = $("#project-id").val();
    var mda = $("#mda-id").val();
    var url =  ABS_PATH + "Release/transactadd/contract/" + id + "/" + mda;
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
    var release = form2js("release-form", ".", false);
    var adate = new Date();
    release.date = adate.toISOString();
    release.contract.dateSigned;
    release.parties = parties.filter(filter_undefined);
    release.contract.milestones = milestones.filter(filter_undefined);
    release.contract.items = items.filter(filter_undefined);
    release.contract.documents = documents.filter(filter_undefined);
    release.contract.amendments = amendments.filter(filter_undefined);
    release.milestone = undefined;
    var value = JSON.stringify(release);
    console.log(value);
    var id = $("#project_id").val();
    var mda = $("#mda_id").val();
    var type = $("#rel_type").val();
    var url =  ABS_PATH + "Release/transactedit/"+type +"/" + id + "/" + mda;
    UIkit.notification("<div uk-spinner></div>",{status:'warning', timeout: 3000});
    let ele = $(this);
    console.log(ele);
    ele.attr("disabled", "disabled");
    modalAction("#planning",value,url);
    setTimeout(function() {window.history.back();}, 3000);
});
