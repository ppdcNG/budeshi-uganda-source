var milestones = [];
var parties = [];
var documents = [];

console.log(  ABS_PATH + "Release/getorg");
var require_fields = ["release_id", "project", "date"];
$("#save-release").click(function () {
    var valid = validateFields(require_fields);
    if(valid != true){
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    console.log("blah");
    var release = form2js("release-form",".",false);
    var adate = new Date();
    release.date = adate.toISOString();
    release.parties = parties.filter(filter_undefined);
    release.planning.milestones = milestones.filter(filter_undefined);
    release.milestone = undefined;
    var value = JSON.stringify(release);
    console.log(value);
    var id = $("#project_id").val();
    var mda = $("#mda-id").val();
    var url =   ABS_PATH + "Release/transactadd/planning/" + id + "/" + mda;
    console.log(url);
    UIkit.notification("<div uk-spinner></div>",{status:'warning', timeout: 3000});
    $(this).attr('disabled','disabled');
    modalAction("#planning",value,url);
    //setTimeout(function() {window.history.back();}, 3000);
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
    release.parties = parties.filter(filter_undefined);
    release.planning.milestones = milestones.filter(filter_undefined);
    release.milestone = undefined;
    var value = JSON.stringify(release);
    console.log(value);
    var id = $("#project_id").val();
    var mda = $("#mda-id").val();
    var type = $("#rel_type").val();
    UIkit.notification("<div uk-spinner></div>",{status:'warning', timeout: 3000});
    let button = $(this);
    button.prop("disabled", true);
    var url =   ABS_PATH + "Release/transactedit/"+type +"/" + id + "/" + mda;
    modalAction("#planning",value,url);
    setTimeout(function() {window.history.back();}, 3000);
});

