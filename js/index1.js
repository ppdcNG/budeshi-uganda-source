const ABS_PATH = 'http://localhost/uganda/';
const PER_PAGE = 8;
var loaded = false;
var currpage = 1;
var records = [];
var data_array = [];
var waitmodal;
var search = false;
var search_value = {};
var columnMap = {
    short_name: "MDA",
    year: "Year",
    budget_amount: "Budget Amount",
    amount: "Contract Amount",
    state: "Location"
}
var min;
var max;
var avg;
var table;
var comTable;
var mdaTable;
var is_empty = (object)=>{return !Object.keys(object).length > 0}
function ajaxrequest(modal, json_data, to_url, call_back) {
    var dataObject = { data: json_data }
    $.ajax({
        type: "post",
        data: dataObject,
        url: to_url,
        content: "application/json",
        success: call_back,
        complete: function () { },
        beforSend: function () { }
    });
}
function numberWithCommas(x) {
    var parts = x.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
}
function initComponents() {

}

function proccessJson(data) {
    var obj = JSON.parse(data);
    return obj;
}

function renderProjectCard(id, mda, title, des, state, year) {
    var yr = (year == "" || year == "null" || year == null) ? "N/A" : year;
    var html = `
                <div class = "project-card">
                    
					<div class="uk-card uk-card-default uk-card-hover uk-card-body baka-card" onclick="viewSummary(\'` + id + `\')">
					<div class="uk-card-badge uk-label">` + yr + `</div>
                    <h3 class="uk-card-title uk-heading-bullet">` + state + `</h3>
                    <p>`+ mda + `</p>
					<p class="uk-text-truncate" title="`+ title + `">` + title + `</p>
					</div>
				</div>
                `;
    return html;
}


///Charts 
function activityChart(id, category, column_series, chart_type, by,yaxisLabel = 'number of procurement activity', title=' chart showing number of procurement activity by ') {

    let chart_title = chart_type + title + columnMap[by];
    let yLabel = "number of procurement"
    Highcharts.chart(id, {
        chart: {
            type: chart_type,
            height: '80%',
        },
        title: {
            text: chart_title
        },
        xAxis: {
            categories: category,
            tickInterval: 1
        },
        yAxis: {
            min: 0,
            title: {
                text: yaxisLabel
            }
        },
        credits: {
            enabled: true,
            href: "budeshi.ng",
            position: {

            },
            style: {
                "cursor": "pointer",
                "color": "#999999",
                "fontSize": "10px"
            },
            text: "budeshi.ng"
        },
        legend: {
            reversed: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0,
                minPointLength: 3
            },
            bar: {
                minPointLength: 3
            }
        },
        series: [{
            name: yaxisLabel,
            data: column_series,
        }]
    });
}

function AmountChart(id, category, Data, chart_type, by, title = undefined) {
    if (title == undefined) {
        var chart_title = chart_type + " chart showing the budget and contract amount by " + columnMap[by];
    }
    else {
        var chart_title = title;
    }
    let yLabel = "Amounts in Naira"
    Highcharts.chart(id, {
        chart: {
            type: chart_type
        },
        credits: {
            enabled: true,
            href: "budeshi.ng",
            position: {

            },
            style: {
                "cursor": "pointer",
                "color": "#999999",
                "fontSize": "10px"
            },
            text: "budeshi.ng"
        },
        title: {
            text: chart_title
        },
        xAxis: {
            categories: category
        },
        yAxis: {
            min: 0,
            title: {
                text: 'contract and budget amount'
            }
        },
        legend: {
            reversed: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        tooltip: {
            pointFormat: '<span style="color:{point.color}">\u25CF</span> {series.name}: N<b>{point.y}</b><br/>'
        },
        series: Data
    });
}


///Aray funcions

function getUniqueCount(column) {
    console.log("blah start");
    let count = {};
    for (let i = 0; i < data_array.length; i++) {
        let cell = data_array[i][column];
        mda_dict[data_array[i][column]] = data_array[i]['mda'];
        console.log(cell);
        if (cell == 0 || cell == "" || cell == undefined) {
            continue;
        }
        else {
            count[cell] = 1 + (count[cell] || 0);
        }
    }
    return count;
}
function Amount(by, where, type = "budget_amount") {
    var amount = 0;
    for (let i = 0; i < data_array.length; i++) {
        let row = data_array[i][by];
        if (row == where) {
            if (data_array[i][type] == undefined || data_array[i][type] == "") {
                amount += 0
            }
            else {
                amount += parseInt(data_array[i][type]);
            };
        }
    }
    return amount;
}
function getAmount(data, column) {
    let amount = data[column];
    if (amount == "" || amount == undefined) {
        return 0;
    }
    else {
        return accounting.unformat(amount);
    }

}
function getStateCordinates() {
    console.log("blah start");
    let count = {};
    let number = {};
    for (let i = 0; i < data_array.length; i++) {
        let cell = data_array[i]["state"];
        console.log(cell);
        if (cell == 0 || cell == "" || cell == undefined) {
            continue;
        }
        else {
            number[cell] = 1 + (number[cell] || 0);
            count[cell] = {

                lat: data_array[i]["latitude"],
                lng: data_array[i]["longitude"],
                number: number[cell]


            }

        }
    }

    return count;
}
function drawMap() {
    markers = [];
    cordinates = getStateCordinates();
    console.log(cordinates);
    for (cord in cordinates) {
        if (true) {
            var mark = new google.maps.Marker({
                position: { lat: parseInt(cordinates[cord].lat), lng: parseInt(cordinates[cord].lng) },
                title: cordinates[cord].number.toString(),
            });
            console.log(mark);
            mark.setMap(map);
            markers.push(mark);
        }
    }

}
function filter_data(by, value) {
    let return_array = [];
    for (let i = 0; i < data_array.length; i++) {
        if (data_array[i][by] == value) {
            return_array.push(data_array[i]);
        }
    }
    return return_array;
}
function filter_advanced() {

}
function to_select(row, checksets, type = 'and') {
    return_value = undefined;
    switch (type) {
        case 'and':
            for (field in checksets) {
                let checkfield = checksets[field]

                //console.log(checkfield);
                if (checkfield.constructor === Array) {
                    let chck = undefined;
                    let eq;
                    for (let a = 0; a < checkfield.length; a++) {
                         eq = row[field] == checkfield[a];
                         //console.log(checkfield[a]);
                         //console.log(row[field]);
                         chck = chck == undefined ? eq: eq || chck;
                    }
                    //console.log(chck)
                    return_value = return_value == undefined?chck : return_value && chck;
                }
                else if(field == 'title'){
                    $row_title = row[field].toLowerCase();
                    $check_title = checksets[field].toLowerCase();
                    if($row_title.includes($check_title)){
                        return_value = return_value == undefined?true: return_value && true;
                    }
                    else{
                        return_value = return_value == undefined?false: return_value && false;
                    }
                    
                }
                else {

                    let check = row[field] == checksets[field];
                    //console.log(row[field]);
                    //console.log(row[field]);
                    if (check) {
                        //console.log(row[field]);
                        console.log(field)
                        return_value = return_value == undefined ? check : return_value && check;
                    }
                    else{
                        return_value = return_value == undefined? false: return_value && false;
                    }
                }
            }
            break;
        case 'or':
        for (field in checksets) {
            //console.log(field);
            let checkfield = checksets[field]
            //console.log(checkfield);
            if (checkfield.constructor === Array) {
                let chck = undefined;
                let eq;
                for (let a = 0; a < checkfield.length; a++) {
                     eq = row[field] == checkfield[a];
                     //console.log(checkfield[a]);
                     //console.log(row[field]);
                     chck = chck == undefined ? eq: eq || chck;
                }
                //console.log(chck)
                return_value = return_value == undefined?chck : return_value || chck;
            }
            else {
                let check = row[field] == checksets[field];
                //console.log(row[field]);
                //console.log(row[field]);
                if (check) {
                    //console.log(row[field]);
                    //console.log(field)
                    return_value = return_value == undefined ? check : return_value || check;
                }
                else{
                    return_value = return_value == undefined? false: return_value || false;
                }
            }
        }
            


    }
    return return_value;
}
function search(params) {
    result = {};
    rows = [];
    let budget_amount = 0
    let contract_amount = 0;
    let lowest = null;
    let highest = 0;
    let count = 0;
    noresult = [];
    for (let i = 0; i < records.length; i++) {
        if (to_select(records[i], params)) {
            rows.push(records[i]);
            if(records[i]["amount"] != "N/A") {
                let amount = parseInt(records[i]["amount"]);
                contract_amount += parseInt(records[i]["amount"]);
                if(amount > highest) highest = amount;
                if(lowest == null) lowest = amount;
                if(amount < lowest && lowest != null) lowest = amount;
                count++;
            }
        }
        else{
            noresult.push(records[i]);
        }
    }
    result.max_contract =  highest;
    result.min_contract = lowest;
    result.avg = contract_amount/count;
    result.total = contract_amount;
    result.rows = rows;
    console.log(rows);
    
    return result
}




