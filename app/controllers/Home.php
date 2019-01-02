<?php
class Home extends Controller{

    public function index(){
        $db = $this->load_model('Explorer');
        $db->projects();
        $db->indexData();
        $view['total'] = number_format($db->total);
        $view['total_sum'] = $this->easy_number($db->sum, 2);
        $view['highest'] = $this->easy_number($db->max, 2);
        $view['lowest'] = $this->easy_number($db->min, 2);
        $categor = $db->categories;
        $consultancy = $categor[0];
        $noncon = $categor[1];
        $supplies = $categor[2];
        $works = $categor[3];
        
        $view['goods'] = number_format($supplies[0]);
        $view['services'] = number_format($consultancy[0]);
        $view['works'] =  number_format($works[0]);
        $view['noncon'] =  number_format($noncon[0]);
        $view['mdas'] = $db->mdas_html;
        $view['veriTable'] = $db->get_cso_reports();
        //echo $db->verifiedTable;
        $view['highid'] = $db->max_id;
        $view['lowestid'] = $db->min_id;
        $this->load_view('frontend/index', $view);
    }
    public function easy_number($n, $pre){
        if ($n < 1000000) {
            // Anything less than a million
            $n_format = number_format($n);
        } else if ($n < 1000000000) {
            // Anything less than a billion
            $n_format = number_format($n / 1000000, $pre) . 'M';
        } else {
            // At least a billion
            $n_format = number_format($n / 1000000000, $pre) . 'B';
        }
        return $n_format;
    }
    public function table_data(){
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            $data_obj["message"] = "Wrong request method";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        $filter_data = (object)$_POST["search_data"];
        $order = $_POST['order'];
        //var_dump($order); die;
        $search_text = $_POST['search']['value'];
        $start = $_POST['start'];
        $length = $_POST['length'];
        $draw = $_POST['draw'];
        $db = $this->load_model('Explorer');
        $data_obj = [];
        $data = $db->ajaxsearch($filter_data, $start, $length,$order,$search_text);
        if(empty($data)){
            $data_obj['empty'] = 'true';
        }
        $data_obj['data'] = $data;

        $data_obj['recordsTotal'] = (int)$db->total;
        $data_obj['recordsFiltered'] = (int)$db->filter_total;
        $data_obj['iTotalDisplayRecords'] = (int)$db->total;
        $data_obj['draw'] = $draw;
        $data_obj["min"] = $db->min;
        $data_obj["max"] = $db->max;
        $data_obj["avg"] = $db->avg;
        $data_obj['total'] = $db->total;
        $json = json_encode($data_obj);
        echo $json;
    }
    public function viewMonitored($id)
    {
        require_once("app/models/recordDb.php");
        $db = new Record();
        $package = $db->getProjectObj($id);
        if (!$package) {
            $data_obj["ajaxstatus"] = "failed";
            $data_obj["message"] = "No record Found for this project";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        $release = $db->releaseObject($package);

        $contract = $release->contracts[0];
        if (isset($contract->implementation) && !empty($contract->implementation->documents) && !empty($contract->implementation->documents[0]->title)) {

            $monitorImages = $db->getMonitorImages($contract->implementation, "verified");
            $monitorReports = $db->getMonitorReports($contract->implementation, "verified");
            $data_obj["images"] = $monitorImages;
            $data_obj["report"] = $monitorReports;
            $data_obj["title"] = $db->projectTitle;
            $data_obj["ajaxstatus"] = "success";
            $data_obj["id"] = $id;
            $data_obj["message"] = "fetched succesfully";
            $json = json_encode($data_obj);
            echo $json;

        } else {
            $data_obj["ajaxstatus"] = "failed";
            $data_obj["message"] = "No Implementation record for this project";
            $json = json_encode($data_obj);
            echo $json;
        }

    }
    public function view_monitored($id){
        $db= $this->load_model('Explorer');
        $output['images'] = $db->getMonitoringImage($id);
        $obj = $db->getMonitorsReport($id);
        $output['report'] = $obj->report;
        $output['report_pdf'] = $obj->report_pdf;
        $output['cso_name'] = $obj->cso_name;
        $output['date_published'] = $obj->date_published;
        $output['title'] = $db->projectTitle;
        $output['id'] = $id;
        $output['status'] = "success";
        $output['message'] = "Fetched Successfully";
        echo json_encode($output);
        
    }
    public function searchSummary(){

        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            $data_obj["message"] = "Wrong request method";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }

        $search_params = json_decode($_POST['data']);
        $db = $this->load_model('Explorer');
        $db->projects($search_params);
        $view['total'] = $db->total;
        $view['sum'] = number_format($db->sum, 2);
        $view['max'] = number_format($db->max, 2);
        $view['min'] = number_format($db->min, 2);
        $categor = $db->categories;
        $view['goods'] = isset($categor['goods'])? number_format($categor['goods']) : 0 ;
        $view['services'] = isset($categor['services'])? number_format($categor['services']) : 0 ;
        $view['works'] =isset($categor['works'])? number_format($categor['works']) : 0 ;
        $view['max_id'] = $db->max_id;
        $view['min_id'] = $db->min_id;
        echo json_encode($view);

    }
    public function viewSummary($id)
    {
        require_once("app/models/recordDb.php");
        require_once("app/models/View.php");
        $db = new Record();
        $vw = new View();
        $output = "";
        $db->getProjectProp($id);
        $releaseArray = $db->getreleaseArray($id);
        if (!empty($releaseArray) && is_array($releaseArray)) {
            foreach ($releaseArray as $key => $val) {
                $output .= $vw->summaryBlock($key, $val);
            }
            $data["title"] = $db->projectTitle;
            $data["mda"] = $db->mda_name;
            $data["summary"] = $output;
            $data["ajaxstatus"] = "success";
            $data["id"] = $id;
            echo json_encode($data);
            die();
        } else {
            echo "<em>Oops Sorry No summary found for this project</em>";
            die();
        }
    }
    public function feedback()
    {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            $data_obj["message"] = "Wrong request method";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        $data = json_decode($_POST["data"]);
        $homeDb = $this->load_model('Explorer');
        $status = $homeDb->feedback($data);
        if ($status) {
            $dataObj["ajaxstatus"] = "success";
            $dataObj["message"] = "Thank you for your feedback, we will be in contact shortly.";
            $json = json_encode($dataObj);
            echo $json;
        } else {
            $dataObj['ajaxstatus'] = "success";
            $dataObj['message'] = "Opps Something went wrong, we couldn't get your request";
            echo json_encode($dataObj);
        }
    }
    public function getChartData($by)
    {
        
        $search_params = json_decode($_POST['data']);
        
        $db = $this->load_model('Explorer');
        $data = $db->get_charts($by, $search_params->search,$search_params->order);
        if (!$db) {
            $data_obj['empty'] = 'yes';
            echo json_encode($data_obj);
            die;
        }
        echo json_encode($data);
    }
    public function download($data)
    {
        $search_data = json_decode($data);
        
        $db = $this->load_model('Explorer');
        $output = $db->download($search_data);
        //echo $output;
        //exit;
    }
    public function project($id)
    {
        require_once("app/models/recordDb.php");
        
        $db = new Record();
        $vw = $this->load_model('View');
        $cm = $this->load_model('Explorer');
        $output = "";
        $nav_tab = "";
        $package = $db->getProjectObj($id);
        $feedbacks = $cm->getComments($id);
        if (!$package) {
            echo "no object found for this project";
            die();
        }
        $release = $db->releaseObject($package);
        if (isset($release->planning)) {
            $planning = $release->planning;
            $nav_tab .= '<li><a href="#">Planning</a></li>';
            if (isset($planning->budget)) {
                $source = isset($planning->budget->source) ? $planning->budget->source : "";
                $source = empty($source) ? $planning->budget->description : $source;
                $output .= $vw->loadPlanningView('', $planning->budget->amount->amount, $source);
            } else {
                $output .= $vw->loadPlanningView("Not Provided", "Not Provided", "Not Provided");
            }
        }

        if (isset($release->tender)) {
            $tender = $release->tender;
            $nav_tab .= '<li><a href="#">Initiation (Tender)</a></li>';
            $ammendments = empty($tender->amendments) ? "<em>Not Provided</em>" : $db->getAmendments($tender);
            $documents = empty($tender->documents) ? "<em>Not Provided</em>" : $db->getDocuments($tender);
            $items = $db->getItems($tender);
            $tenders = $db->getTenderer($tender);
            $tender_status = isset($tender->status) ? $tender->status : "Not Provided";

            $output .= $vw->loadTenderView($db->mda_name, $tender_status, $ammendments, $tenders, $documents, $items);
        }
        if (isset($release->awards) && isset($release->awards[0])) {
            $award = $release->awards[0];
            $nav_tab .= '<li><a href="#">Award</a></li>';
            $ammendments = $db->getAmendments($award);
            $documents = $db->getDocuments($award);
            $items = $db->getItems($award);
            $suppliers = $db->getTenderer($award, "suppliers");
            $award_date = empty($award->date) ? "Not Provided" : date('jS F Y',strtotime($award->date));
            $output .= $vw->loadAwardView($award->title, $award_date, $ammendments, $items, $suppliers, $documents);

        }

        if (isset($release->contracts) && isset($release->contracts[0])) {
            $contract = $release->contracts[0];
            if (isset($contract->status)) {
                $nav_tab .= '<li><a href="#">Contract</a></li>';
                $ammendments = $db->getAmendments($contract);
                $documents = $db->getDocuments($contract);
                $items = $db->getItems($contract);
                $start_date = empty($contract->period->startDate) ? "Not Provided" : date('jS F Y',strtotime($contract->period->startDate));
                $end_date = empty($contract->period->endDate) ? "Not Provided" : date('jS F Y',strtotime($contract->period->endDate));
                $output .= $vw->loadConntractView($contract->title, $contract->description, $contract->status, $start_date, $end_date, $contract->value->amount, $items, $documents);
            }
            //echo "<pre>".json_encode($release, JSON_PRETTY_PRINT)."</pre>";
            //die();
        }

        $contract = empty($release->contracts) ? new Stdclass : $release->contracts[0];
        if (isset($contract->implementation) && !empty($contract->implementation->documents) && !empty($contract->implementation->documents[0]->title)) {

            $nav_tab .= '<li><a href="#">Implementation</a></li>';
            $monitorImages = $db->getMonitorImages($contract->implementation);
            $monitorReports = $db->getMonitorReports($contract->implementation);
            $transactions = $db->getTransaction($contract->implementation);
            $output .= $vw->loadImplementationView($transactions, $monitorReports, $monitorImages);
        }
        $parties = $vw->viewParties($id);


        require("app/views/frontend/viewprojectmodal.html");

    }
    public function viewParty(){
        $data = json_decode($_POST['data']);
        $db = $this->load_model('Explorer');
        $party = $db->fetch_parties($data->id, $data->project_id);
        echo json_encode($party);
    }
    public function viewOrg(){
        $data = json_decode($_POST['data']);
        $db = $this->load_model('Explorer');
        $party = $db->fetch_supplier($data->project_id);
        echo json_encode($party);

    }
    public function supplier($id){
        
        $db = $this->load_model('Explorer');
        $data = $db->supplier($id);
        echo json_encode($data);

    }
    public function stats(){
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            $data_obj["message"] = "Wrong request method";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        $filter_data = (object)$_POST["search_data"];
        $db = $this->load_model('Explorer');
        $db->projects($filter_data);
        $data['min'] = $db->min;
        $data['max'] = $db->max;
        $data['avg'] = $db->avg;
        $data['min_id'] = $db->min_id;
        $data['max_id'] = $db->max_id;
        $data['selective'] = $db->selective;
        $data['open'] = $db->open;
        $data['limited'] = $db->limited;
        $data['goods'] = $db->goods;
        $data['works'] = $db->works;
        $data['categories'] = $db->categories;
        echo json_encode($data);
    }

}
?>
