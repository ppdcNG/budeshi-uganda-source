var parties = [];
var milestones = [];
var documents = [];
var items = [];
var amendments = [];
var require_fields = ["award-title","award-description", "tender-org"];
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
    release.award.milestones = milestones.filter(filter_undefined);
    release.award.items = items.filter(filter_undefined);
    release.award.documents = documents.filter(filter_undefined);
     release.award.amendments = amendments.filter(filter_undefined);
    release.milestone = undefined;
    var value = JSON.stringify(release);
    console.log(value);
    var id = $("#project-id").val();
    var mda = $("#mda-id").val();
    var url =  ABS_PATH + "Release/transactadd/award/" + id + "/" + mda;
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
    release.award.milestones = milestones.filter(filter_undefined);
    release.award.items = items.filter(filter_undefined);
    release.award.documents = documents.filter(filter_undefined);
     release.award.amendments = amendments.filter(filter_undefined);
    release.milestone = undefined;
    console.log(release);
    var value = JSON.stringify(release);
    console.log(value);
    var id = $("#project_id").val();
    var mda = $("#mda_id").val();
    var type = $("#rel_type").val();
    var url =  ABS_PATH + "Release/transactedit/"+type +"/" + id + "/" + mda;
    $(this).attr('disabled','disabled');
    console.log(url);
    UIkit.notification("<div uk-spinner></div>",{status:'warning', timeout: 3000});
    //this.atr('disabled','disabled');
    modalAction("#planning",value,url);
    setTimeout(function() {window.history.back();}, 3000);
});
