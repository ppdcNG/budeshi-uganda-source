var cso_page = 1;
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
        },
        placeholder: "Search Contractor"
    });
    $('#feedback-form').submit(function(e){
        e.preventDefault();
        feedback();
    })
    init();
    ////events
    $('#finder').submit(function (e) {
        e.preventDefault();
    })
    $("#finderFind").click(function () {
        let data = form2js("finder", '.', false);
        let search_value = data;
        currpage = 1;
        //if(!is_empty($("#visit").val())) data.monitored = $("#visit").val();
        console.log(data);
        UIkit.notification("Please Wait...", { status: "primary", timeout: 2000 })
        tableSource(data, 'Home/table_data/');
        plot_chart();
        summary();
        UIkit.notification("Done!", { status: "primary", timeout: 2000 });
    });

    $("#comparebttn").click(function () {
        console.log("blah");
        var selected = comTable.rows({ selected: true });
        console.log(selected.data());
        comparison("column", selected.data());

    });
    $("#clearSelect").click(function () {
        comTable.rows().deselect();
    })
    $("#type").change(plot_chart);
    $("#by").change(plot_chart);
    getReports(cso_page);
});
function summary(){
    let url = ABS_PATH + 'Home/searchSummary';
    console.log;
    let search_param = form2js('finder', '.', false);
    console.log(search_param);
    ajaxrequest('blah', JSON.stringify(search_param), url, function (data) { 
        console.log(data);
        let params =proccessJson(data);
        $("#services").html(params.services);
        $("#goods").html(params.goods);
        $("#works").html(params.works);
        $("#sum").html(params.sum);
        $("#lowest").html(params.min);
        $("#highest").html(params.max);
        $("#total").html(params.total);
        $("#highid").val(params.max_id);
        $("#lowestid").val(params.min_id)
     });

}
function clear_filter() {
    search = false;
    search_value = {}
    UIkit.notification("Please Wait...", { status: "primary", timeout: 0 })
    tableSource("", 'Home/search/');
    loadPage(1);
    handleChartChange();


}

function init() {
    //UIkit.modal($("#waitModal")[0]).show();
    let url = ABS_PATH + "Home/getProjectArray/";
    console.log($("#num").val());
    //ajaxrequest("mymodal", "data-prog", url, handleInit);
    $('#num_project').countTo({ from: 0, to: parseInt($("#num").val()), speed: 2000, onComplete: function (value) { $("#num_project").text(accounting.formatNumber($("#num").val())) } });
    tableSource();
    plot_chart();
}
function plot_chart() {
    let by = $("#by").val();
    console.log(by);
    let url = ABS_PATH + 'Home/getChartData/' + by;
    let search_param = form2js('finder', '.', false);
    console.log(search_param);
    let data = {};
    data.order = $("#order").val();
    data.limit = $("#limit").val();
    data.search = search_param
    ///console.log(search_value);
    ajaxrequest('blah', JSON.stringify(data), url, handleChartFetch);
}
////handlers

function handleInit(data) {
    console.log(data);
    var result = proccessJson(data);
    data_array = result.array;
    records = result.array;
    let type = $("#type").val();
    let by = $("#by").val();
    min = accounting.unformat(result.min);
    max = accounting.unformat(result.max);
    avg = accounting.unformat(result.avg);
    $('#num_project').countTo({ from: 0, to: result.total, speed: 2000, onComplete: function (value) { $("#num_project").text(accounting.formatNumber(result.total)) } });
    $('#min').countTo({ from: 0, to: min, speed: 500 * 2, onComplete: function (value) { $("#min").text(accounting.formatNumber(min)) } });
    $('#max').countTo({ from: 0, to: max, speed: 500 * 2, onComplete: function (value) { $("#max").text(accounting.formatNumber(max)) } });
    $('#avg').countTo({ from: 0, to: avg, speed: 500 * 2, onComplete: function (value) { $("#avg").text(accounting.formatNumber(avg)) } });
    table.destroy();
    //load_compare_table(data_array);
    //loadPage(1);
    numProjectChart("short_name", type);
    load_mda_data();
    //contractBudgetChart("short_name", "column");
    //comparison(type)
    UIkit.modal($("#waitModal")[0]).hide();
    console.log(avg);


}

function tableSource(search_data = "", url = "home/table_data") {
    if (comTable != undefined) comTable.destroy();
    comTable = $("#bud-table").DataTable({
        'processing': true,
        'serverSide': true,
        paging: true,
        ajax: {
            url: ABS_PATH + url,
            type: "POST",
            error: function (ts) { console.log(ts.responseText); alert(ts.responseText) },
            data: { 'search_data': search_data },
            dataSrc: function (data) {
                console.log(data);
                let rtd = data;
                if (rtd.hasOwnProperty('min') && rtd.draw == 1) {
                    $('#min').countTo({ from: rtd.min / 2, to: rtd.min, speed: 500 * 3, onComplete: function (value) { $("#min").text(accounting.formatNumber(rtd.min)) } });
                }
                if (rtd.hasOwnProperty('total') && rtd.draw == 1) {
                    $('#num_project').countTo({ from: 0, to: rtd.total, speed: 2000, onComplete: function (value) { $("#num_project").text(accounting.formatNumber(rtd.total)) } });
                }
                if (rtd.hasOwnProperty('max') && rtd.draw == 1) {
                    $('#max').countTo({ from: rtd.max / 2, to: rtd.max, speed: 500 * 3, onComplete: function (value) { $("#max").text(accounting.formatNumber(rtd.max)) } });
                }
                if (rtd.hasOwnProperty('avg') && rtd.draw == 1) {
                    $('#avg').countTo({ from: rtd.avg / 2, to: rtd.avg, speed: 500 * 3, onComplete: function (value) { $("#avg").text(accounting.formatNumber(rtd.avg)) } });
                }
                return rtd.data;
            }
        },
        columns: [
            {
                data: "nothing",
                render: function (data, type, row) {
                    return "";
                }
            },
            {
                data: "title",
                render: function (data, type, row) {
                    return '<a href = "'+ABS_PATH+'Home/Project/'+row.id+'">' + row.title + '</a>';
                }
            },
            {
                data: "name",
                render: function (data, type, row) {
                    if (is_empty(data)) {
                        return '<span>N/A</span>';
                    }
                    else {
                        return  '<span onclick = "view_org_2(\''+ row.id +'\')">'+data+'</span>';
                    }
                }
            },
            {
                data: "budget_amount",
                render: function (data, type, row) {
                    if (data == "Not Provided") {
                        return '<span uk-tooltip = "title: Not Provided By Procuring Entity">' + data + '</span>';
                    }
                    else {
                        return accounting.formatNumber(data, 2);
                    }
                }

            }
            ,
            {
                data: "amount",
                render: function (data, type, row) {
                    if (data == "Not Provided") {
                        return '<span uk-tooltip = "title: Not Provided By Procuring Entity">' + data + '</span>';
                    }
                    else {
                        return accounting.formatNumber(data, 2);
                    }
                }
            },
            { data: "year" },
            {
                data: "mda"
            }
        ],
        columnDefs: [{
            orderable: false,
            className: 'select-checkbox',
            targets: 0
        }],
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'collection',
                text: 'Export',
                buttons: [{
                    text: 'Download CSV',
                    action: function (e, dt, node, config) {
                        let param = JSON.stringify(search_value);
                        let path = ABS_PATH + 'Home/download/' + param;
                        window.location = path;
                    }
                }, 'copy', 'pdf']
            }
        ],
        select: { style: "multi", selector: 'td:first-child' }
    });
}


function handleSearch(data) {
    UIkit.notification.closeAll();

    data_array = results.rows;
    //load_compare_table(results.rows);
    $('#num_project').countTo({ from: 0, to: data_array.length, speed: 1000, onComplete: function (value) { $("#num_project").text(accounting.formatNumber(data_array.length)) } });
    $('#min').countTo({ from: accounting.unformat(results.min_contract / 2), to: accounting.unformat(results.min_contract), speed: 1000, onComplete: function (value) { $("#min").text(accounting.formatNumber(results.min_contract)) } });
    $('#max').countTo({ from: accounting.unformat(results.max_contract / 2), to: accounting.unformat(results.max_contract), speed: 1000, onComplete: function (value) { $("#max").text(accounting.formatNumber(results.max_contract)) } });
    $('#avg').countTo({ from: results.avg / 2, to: results.avg, speed: 500, onComplete: function (value) { $("#avg").text(accounting.formatNumber(avg)) } });
    loadPage(1);
    let type = $("#type").val();
    let by = $("#by").val();
    numProjectChart(by, type);
    //contractBudgetChart(by, type);
    //comparison(type);

}




function handleChartFetch(return_data) {
    console.log(return_data);
    data_array = proccessJson(return_data);

    let type = $("#type").val();
    let by = $("#by").val();
    console.log(by);

    let charts = proccessJson(return_data);
    console.log(charts);
    let data = charts.number_of_procurement;
    let datum = charts.sum_of_procurment_activity;
    console.log(datum);
    var xaxis = [];
    var values = [];
    var xaxis1 = [];
    var values1 = [];

    for (let i = 0; i < data.length; i++) {

        var d = {};
        var f = {};
        if (data[i][1] == null) continue;
        if (by == 'short_name') {
            xaxis.push(data[i][1])
            if(i < datum.length) xaxis1.push(datum[i][1])
            d.name = data[i][1]
            if(i<datum.length) f.name = datum[i][1]
        }
        else {
            if( i < datum.length) xaxis1.push(datum[i][1])
            xaxis.push(data[i][1])
            if( i < datum.length) f.name = datum[i][1]
            d.name = data[i][1]
        }
        d.y = parseInt(data[i][0]);
        if(i < datum.length) f.y = parseInt(datum[i][0]);
        values.push(d);
        if(i < datum.length) values1.push(f);
    }
    activityChart("chart", xaxis, values, type, by);
    activityChart("achart", xaxis1, values1, type, by, "Total Contract Amount ", " chart showing total contract amount by ");
}
function handleChartChange() {
    console.log("blah change")
    let type = $("#type").val();
    console.log(type);
    let by = $("#by").val();
    let order = $("#order").val();
    numProjectChart(by, type)
}
function handleMonitorView(data) {
    UIkit.notification.closeAll();
    console.log(data);
    var obj = proccessJson(data);
    if (obj.status == "success") {
        $("#projectTitle").html(obj.title);
        $("#reports").html(obj.report);
        $("#breakdown").click(function () { viewProject(obj.id); });
        $("#monitorImages").html(obj.images);
        $("#monitor-details").append(obj.report_pdf);
        $("#cso-name").text(obj.cso_name);
        $("#date-published").text(obj.date_published);
        UIkit.modal("#view-report").show();
    }
}
function handleSummaryView(data) {
    UIkit.notification.closeAll();
    console.log(data);
    var obj = proccessJson(data);
    if (obj.ajaxstatus == "success") {
        $("#summaryview").html(obj.summary);
        $("#summarytitle").html(obj.title);
        console.log(obj.id);
        $("#detailLink").click(function () { 
            let  p_id = $("#project_id").val();
            viewProject(p_id); });
        UIkit.modal("#view-summary").show();

    }
}


function numProjectChart(by, type) {
    search_value = form2js('finder', '.', true)
    let url = ABS_PATH + 'Home/getChartData/' + by;
    console.log(search_value);
    ajaxrequest('blah', JSON.stringify(search_value), url, handleChartFetch);
}
function contractBudgetChart(by, type) {
    var contract_series = {
        name: "Contract Amount",
        data: null
    };
    var budget_series = {
        name: "Budget Amount",
        data: null
    };
    var categories = getUniqueCount(by);
    console.log(categories)
    var xaxis = [];
    var ctrtData = [];
    var bdgData = [];
    for (let key in categories) {
        ctrtData.push(Amount(by, key, "amount"));
        bdgData.push(Amount(by, key, "budget_amount"))
        xaxis.push(key)
    }
    contract_series.data = ctrtData;
    budget_series.data = bdgData;
    console.log(contract_series);
    console.log(budget_series);
    var data = [contract_series, budget_series];
    console.log(data);
    AmountChart("amount-chart", xaxis, data, type, by);

}
function comparison(type, data) {
    var contract_series = {
        name: "contract Amount",
        data: null
    };
    var budget_series = {
        name: "Budget Amount",
        data: null
    };
    let xaxis = [];
    let ctrtData = [];
    let bdgData = [];
    for (let i = 0; i < data.length; i++) {
        if (data[i]["amount"]) { var ctValue = "amount"; }
        else { var ctValue = 5 };
        if (data[i]["budget_amount"]) { var bgvalue = "budget_amount"; }
        else { var bgvalue = 4; }
        if (data[i]["title"]) { var xValue = "title"; }
        else { var xValue = 1; }
        ctrtData.push(getAmount(data[i], ctValue));
        bdgData.push(getAmount(data[i], bgvalue));
        xaxis.push(data[i][xValue]);
    }
    contract_series.data = ctrtData;
    budget_series.data = bdgData;
    console.log(contract_series);
    console.log(budget_series);
    var data = [contract_series, budget_series];
    console.log(data);
    AmountChart("comparison", xaxis, data, type, by, "Comparison of Budgeted and Contracted Amount");

}
function viewProject(id) {
    UIkit.notification.closeAll();
    var url = ABS_PATH + "Home/project/" + id;
    UIkit.notification("Please Wait...", { status: "primary", timeout: 0 })
    ajaxrequest("", "blah", url, function (data) {
        UIkit.notification.closeAll();
        $("#project-det").html(data);
        UIkit.modal("#view-project").show();
        
    });
    
}
function viewSummary(type) {
    let id = type == 'highest'? $("#highid").val(): $("#lowestid").val();
    console.log(id);
    
    var url = ABS_PATH + "Home/viewSummary/" + id;
    var det_url = ABS_PATH + "Home/project/" + id;
    $("#project_id").val(id);
    ajaxrequest("modal", "data", url, handleSummaryView);

}
function viewMonitored(id) {
    var url = ABS_PATH + "Home/view_monitored/" + id;
    ajaxrequest("modal", "data", url, handleMonitorView);
}
function feedback(){
    var url = ABS_PATH + 'Home/feedback';
    let data = form2js('feedback-form', '.');
    $("#feedback-form")[0].reset();
    console.log(data);
    data = JSON.stringify(data);
    ajaxrequest('modal',data, url, handleFeedback);
    
}
function handleFeedback(data){
    console.log(data);
    data = proccessJson(data);
    UIkit.notification(data.message, { status: "primary", timeout: 2000 });
}
function fetch_supplier(id = ''){
     id =  is_empty(id) ? $("#project-id").val() : id;
    let url = ABS_PATH + 'Home/supplier/'+id;
    ajaxrequest('modal','data',url, function(data){
        console.log(data);
        data = proccessJson(data);
        $("#org-name").html(is_empty(data.name)? 'N/A':data.name);
        $("#rc-no").html(data.ug_no == null || is_empty(data.ug_no)? 'N/A':data.ug_no);
        $("#email-addrs").html(data.ug_no == null ||is_empty(data.email)? 'N/A':data.email);
        $("#address").html(data.ug_no == null || is_empty(data.address)? 'N/A':data.address);
        $("#contact-person").html(data.ug_no == null || is_empty(data.contact_person)? 'N/A':data.person);
        $("#telephone").html(data.ug_no == null || is_empty(data.phone)? 'N/A':data.phone);
        $("#website").html(data.ug_no == null || is_empty(data.website)? 'N/A':data.website);

        UIkit.modal($("#view-org")[0]).show();

    })

}
function view_party($id){
    console.log($id);;
    let url = ABS_PATH + 'Home/viewParty/';
    let data = { 'id': $id,
    'project_id' : $("#project-id").val()}
    ajaxrequest('modal', JSON.stringify(data),url, function(data){
        console.log(data);
        data = proccessJson(data);
        $("#org-name").html(is_empty(data.name)? 'N/A':data.name);
        $("#rc-no").html(data.id == undefined || is_empty(data.id)? 'N/A':data.id);
        $("#email-addrs").html(data.contactPoint.email == undefined ||is_empty(data.contactPoint.email)? 'N/A':data.contactPoint.email);
        $("#address").html(data.address.postalCode == undefined || is_empty(data.address.postalCode)? 'N/A':data.address.postalCode);
        $("#contact-person").html(data.contactPoint.name == undefined || is_empty(data.contactPoint.name)? 'N/A':data.contactPoint.name);
        $("#telephone").html(data.contactPoint.telephone == undefined || is_empty(data.contactPoint.telephone)? 'N/A':data.phone);
        $("#website").html(data.contactPoint.url == undefined || is_empty(data.contactPoint.url)? 'N/A':data.contactPoint.url);

        UIkit.modal($("#view-org")[0]).show();

    });
}
function view_org_2($id){
    console.log($id);;
    let url = ABS_PATH + 'Home/viewOrg/';
    let data = { 'project_id': $id}
    ajaxrequest('modal', JSON.stringify(data),url, function(data){
        console.log(data);
        data = proccessJson(data);
        $("#org-name").html(is_empty(data.name)? 'N/A':data.name);
        $("#rc-no").html(data.id == undefined || is_empty(data.id)? 'N/A':data.id);
        $("#email-addrs").html(data.contactPoint.email == undefined ||is_empty(data.contactPoint.email)? 'N/A':data.contactPoint.email);
        $("#address").html(data.address.postalCode == undefined || is_empty(data.address.postalCode)? 'N/A':data.address.postalCode);
        $("#contact-person").html(data.contactPoint.name == undefined || is_empty(data.contactPoint.name)? 'N/A':data.contactPoint.name);
        $("#telephone").html(data.contactPoint.telephone == undefined || is_empty(data.contactPoint.telephone)? 'N/A':data.phone);
        $("#website").html(data.contactPoint.url == undefined || is_empty(data.contactPoint.url)? 'N/A':data.contactPoint.url);

        UIkit.modal($("#view-org")[0]).show();

    });

}
// CSO Implementation
function renderReport(data) {
    let date = new Date(data.date_published);
    date = date.toDateString();
    date = date.split(' ');
    let html = `<div class="uk-child-width-1-1" uk-grid>
    <div class = "uk-width-expand">
        <article class="uk-comment">
            <header class="uk-comment-header uk-grid-medium uk-flex-middle" uk-grid>
                <div class="uk-width-auto">
                    <img class="uk-comment-avatar" src="`+ ABS_PATH + `images/monitoring/`+data.cover+`" width="100" height="100" alt="">
                </div>
                <div class="uk-width-expand">
                    <h4 class="uk-comment-title"><a onclick = "viewMonitored('`+data.id+`')" class="uk-link-reset" href="#">`+data.title+`</a></h4>
                    <ul class="uk-comment-meta uk-subnav uk-subnav-divider">
                        <li><a href="#"><span >Published on:</span> `+date[2]+ ' '+date[1] +' ' + date[3] +` </a></li>
                        <li><a href="#" uk-tooltip = "title: Click for more"><span class = "uk-text-small uk-text-background">Published By:</span> `+data.name+`</a></li>
                    </ul>
                </div>
            </header>
        </article>
        <hr>
    </div>
</div>`;
return html;   
}
function renderpagination(page, total, disable = false){
    let prev = page ==1 ? 'uk-disabled': '';
    let next = page >= Math.ceil(total/page)? 'uk-disabled' : '';
    if(disable){
        prev = 'uk-disabled';
        next = 'uk-disabled';
    }
    let html = `
    <ul class="uk-pagination">
        <li class = "`+ prev +`"><button onclick = "prevpage()" class="uk-border-rounded uk-button uk-button-default"><span uk-pagination-previous></span></button></li>
        <li class="`+ next +`"><button onclick = "nextpage()" class  = "uk-border-rounded uk-button uk-button-default"><span uk-pagination-next></span></button></li>
    </ul>
`;
return html;
}

function getReports(page = 1){
    let url = ABS_PATH + "CSO/reports/" + page;
    ajaxrequest('modal', '', url, function(response){
        console.log(response);
        let reports = JSON.parse(response);
        let reps = reports.list;
        let report_html = reps.length == 0? "<h3>No reports Added Yed</h3>": "";
        let pagination = "";
        for(let i = 0; i < reps.length; i++){
            report_html += renderReport(reps[i]);
        }
        let nullpage = reps.length > 5 ? false : true;
        pagination = renderpagination(page, reports.total, nullpage);
        $('#cso-reports').html(report_html);
        $("#cso-pag").html(pagination);
    });
}
function nextpage(){
    cso_page += 1;
    getReports(cso_page);
}
function prevpage(){
    cso_page -= 1;
    getReports(cso_page);
}

