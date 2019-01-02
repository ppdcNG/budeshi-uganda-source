<?php
class CSOModel extends Model{

    function __construct()
    {
        Parent::__construct();
    }

    public function register($org, $user, $user_id){
        $ins['ug_no'] = $org->general->reg_ID;
        $ins['name'] =$org->general->legal_name;
        $ins['url'] = $org->general->website;
        $ins['address'] = $org->address->address;
        $ins['state'] = $org->address->region;
        $ins['lga'] = $org->address->locality;
        $ins['country'] = $org->address->country;
        $ins['contact_name'] = $org->contact->name;
        $ins['email'] = $org->contact->email;
        $ins['phone'] = $org->contact->phone;
        $org_id = $this->insert($ins,'institutions');
        $cs['user_id'] = $user_id;
        $cs['cso_id'] = $org_id;
        $id = $this->insert($cs,'cso_users');
    }
    public function getCSO($userId){
        $query = "SELECT c.cso_id, i.name FROM cso_users c LEFT JOIN institutions i ON c.cso_id = i.id WHERE c.user_id = ".$userId;
        $result = $this->query($query);
        $row = mysqli_fetch_assoc($result);
        return $row;
    }
    public function getReports($cso_id){
        $query = "SELECT  r.id, r.title as report_title , r.report, r.date_published, r.cover as imagename, p.title FROM cso_reports r LEFT JOIN cso_projects p ON r.project_id = p.id 
        WHERE r.cso_id = ".$cso_id. " ORDER BY r.id DESC";
        $result = $this->query($query);
        $output = "";
        if(mysqli_num_rows($result)> 0){
            while($row = mysqli_fetch_assoc($result)){
                $row['date_published'] = date('D jS M ', strtotime($row['date_published']));
                $output .= $this->renderReport($row);
            }

        }
        else{
            $output = $this->renderEmptyReport();
        }

        return $output;
        
    }

    private function renderReport($row){
        $html = '<li>
        <article class="uk-comment uk-comment-primary">
        <header class="uk-comment-header uk-grid-medium uk-flex-middle" uk-grid>
            <div class="uk-width-auto">
                <img class="uk-comment-avatar" src="'.ABS_PATH.'images/monitoring/'.$row['imagename'].'" width="80" height="80"
                    alt="">
            </div>
            <div class="uk-width-expand">
                <h4 class="uk-comment-title uk-margin-remove"><a class="uk-link-reset" href="#">'.$row['report_title'].'</a></h4>
                <ul class="uk-comment-meta uk-subnav uk-subnav-divider uk-margin-remove-top">
                    <li><a href="#">'.$row['date_published'].'</a></li>
                    <li><span class="uk-text-primary">'.$row['title'].'</span> </li>
                    <li><a class="uk-button uk-button-default uk-border-rounded" uk-toggle  href="#edit-report" 
                        onclick = "edit_report(\''.$row['id'].'\')">Edit
                            <span uk-icon="icon: edit"></span> </a><a class="uk-button uk-button-default uk-border-rounded uk-padding-left"   href="#edit-report" 
                            onclick = "deletePrompt(\''.$row['id'].'\',\'report\')">Delete
                                <span uk-icon="icon: edit"></span> </a></li>
                </ul>

            </div>
        </header>
        <div class="uk-comment-body">
            <p class = "uk-text-truncate">'.$row['report'].'</p>
        </div>
    </article></li>';
    return $html;
    }
    public function get_report_files($id){
        $query = "SELECT imagename FROM report_images WHERE report_id = ".$id;
        $result = $this->query($query);
        $files = mysqli_fetch_all($result);
        //var_dump($files); die;
        return $files;
    }
    public function get_report_info($id){
        $query = "SELECT * FROM cso_reports WHERE id = ".$id;
        $result = $this->query($query);
        $row = mysqli_fetch_assoc($result);
        $n_query = "SELECT * FROM report_images WHERE report_id = ".$id;
        $n_result = $this->query($n_query);
        $images = mysqli_fetch_all($n_result,MYSQLI_ASSOC);
        $obj = new StdClass();
        $obj->details = $row;
        $obj->images = $images;
        return $obj;

    }
    public function ajaxsearch($search_query, $start, $length){
        $arrayToReturn = [];
        $queries = $this->getSQL($search_query);
        $attach = implode("AND ", $queries);
        $attach = empty($attach) ? "" : " WHERE " . $attach;
        $query = "SELECT SQL_CALC_FOUND_ROWS p.title, p.id, p.year, m.name FROM projects p LEFT JOIN mdas m ON p.mda_id = m.id ".$attach. " LIMIT " . $length . " OFFSET " . $start;
        //echo $query;
        $result  = $this->query($query);
        if (mysqli_num_rows($result) <= 0) {
            return $arrayToReturn;
        }
        $n_query = "SELECT FOUND_ROWS()";
        $number = $this->query($n_query);
        $number = mysqli_fetch_array($number)[0];
        $this->total = $number;
        $this->filter_total = mysqli_num_rows($result);
        $contract_amount = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rt = [];
            $rt['title'] = $row['title'];
            $rt['id'] = $row['id'];
            $rt['title'] = ucwords(strtolower($row['title']));
            $rt['name'] = ucwords(strtolower($row['name']));
            $rt['year'] = $row['year'];
            $arrayToReturn[] = $rt;
        }
        return $arrayToReturn;

    }
    public function edit_report_body($data, $id)
    {
        $up['title'] = $data->title;
        $up['report'] = $data->report;
        $this->update($id,$up,'cso_reports', 'id');
        
    }
    public function projects($cso_id,$search_query, $start, $length){
        $query = "SELECT SQL_CALC_FOUND_ROWS p.id, p.title, p.year, p.date_published, p.contractor_name, m.name as mda FROM cso_projects p LEFT JOIN mdas m ON p.mda_id = m.id WHERE p.cso_id = ".$cso_id. " LIMIT " . $length . " OFFSET " . $start;
        $result = $this->query($query);
        $arrayToReturn = [];
        if (mysqli_num_rows($result) <= 0) {
            $this->total = 0;
            $this->filter_total = 0;
            return $arrayToReturn;
            
        }
        $n_query = "SELECT FOUND_ROWS()";
        $number = $this->query($n_query);
        $number = mysqli_fetch_array($number)[0];
        $this->total = $number;
        $this->filter_total = mysqli_num_rows($result);
        $contract_amount = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rt = [];
            $rt['id'] = $row['id'];
            $rt['title'] = $row['title'];
            $rt['date'] = empty($row['date_published'])?"N/A": date('M d',strtotime($row['date_published']));
            $rt['mda'] = $row['mda'];
            $rt['contractor'] = empty($row['contractor_name'])? "N/A": $row['contractor_name'];
            $rt['year'] = $row['year'];
            $arrayToReturn[] = $rt;
        }
        return $arrayToReturn;
    }
    public function renderEmptyReport(){
        return '<li> <h4 class = "uk-text-center">No Reports Added Yet</h4></li>';
    }
    private function getSQL($obj)
    {

        $queries = [];
        $method = empty($obj->method) ? false : $this->queryBuilder($obj->method, "method");
        $mda = empty($obj->mda) ? false : $this->queryBuilder($obj->mda, "mda");
        $year = empty($obj->year) ? false : $this->queryBuilder($obj->year, "year");
        $contractor = empty($obj->contractor) ? false : $this->queryBuilder($obj->contractor, "contractor");
        $text = empty($obj->text) ? false : $this->queryBuilder($obj->text, "text");
        $category = empty($obj->category) ? false : $this->queryBuilder($obj->category, "category");
        $monitored = empty($obj->monitored) ? false : $this->queryBuilder($obj->monitored, "monitored");
        if ($method) {
            $queries[] = $method;
        }
        if ($year) {
            $queries[] = $year;
        }
        if ($mda) {
            $queries[] = $mda;
        }
        if ($contractor) {
            $queries[] = $contractor;
        }
        if ($text) {
            $queries[] = $text;
        }
        if ($category) {
            $queries[] = $category;
        }
        if ($monitored) {
            $queries[] = $monitored;
        }
        return $queries;

    }
    private function queryBuilder($value, $type)
    {
        $query = "";
        switch ($type) {
            case "year":
                if (is_array($value) and count($value) > 1) {
                    $data = [];
                    foreach ($value as $val) {
                        $data[] = "'" . $val . "'";
                    }
                    $join = implode(",", $data);
                    $join = "(" . $join . ")";
                    $query = " p.year IN " . $join;
                } else {
                    $val = is_array($value) ? $value[0] : $value;
                    $query = " p.year = '" . $val . "' ";
                }
                break;

            case "category":
                if (is_array($value) and count($value) > 1) {
                    $data = [];
                    foreach ($value as $val) {
                        $data[] = "'" . $val . "'";
                    }
                    $join = implode(",", $data);
                    $join = "(" . $join . ")";
                    $query = " p.project_category IN " . $join . " ";
                } else {
                    $val = is_array($value) ? $value[0] : $value;
                    $query = "p.project_category = '" . $val . "' ";
                }
                break;
            case "contractor":
                if (is_array($value) and count($value) > 1) {
                    $join = implode(",", $value);
                    $join = "(" . $join . ")";
                    $query = " ct.contractor_id IN " . $join . " ";
                } else {
                    $val = is_array($value) ? $value[0] : $value;
                    $query = " ct.contractor_id = " . $val . " ";
                }
                break;
            case "mda":
                if (is_array($value) and count($value) > 1) {
                    $join = implode(",", $value);
                    $join = "(" . $join . ")";
                    $query = " p.mda_id IN " . $join . " ";
                } else {
                    $val = is_array($value) ? $value[0] : $value;
                    $query = " p.mda_id = " . $val . " ";
                }
                break;
            case "text":
                $query = " p.title LIKE '%" . $value . "%' ";
                break;
            case "method":
                if (is_array($value) and count($value) > 1) {
                    $data = [];
                    foreach ($value as $val) {
                        $data[] = "'" . $val . "'";
                    }
                    $join = implode(",", $data);
                    $join = "(" . $join . ")";
                    $query = " t.procurement_method IN " . $join . " ";
                } else {
                    $val = is_array($value) ? $value[0] : $value;
                    $query = " t.procurement_method = '" . $val . "' ";
                }
                break;
            case "monitored":
                $val = is_array($value) ? $value[0] : $value;
                $query = " p.monitored = '" . $val . "' ";
                break;






        }
        return $query;
    }
    public function add_project($id, $cso_id){
        $query = "SELECT p.title, p.description, p.mda_id, p.year, i.name, i.address, i.phone, i.email, c.amount, b.budget_amount FROM projects p LEFT JOIN contract c ON p.id = c.project_id 
        LEFT JOIN planning b ON p.id = b.project_id LEFT JOIN contractors ct ON p.id = c.project_id LEFT JOIN institutions i ON ct.contractor_id = i.id WHERE p.id = ".$id;
        $result = $this->query($query);
        $row = mysqli_fetch_assoc($result);

        $ins = [];
        $ins['title'] = $row['title'];
        $ins['description'] = empty($row['description'])? "": $row['description'];
        $ins['mda_id'] = $row['mda_id'];
        $ins['cso_id'] = $cso_id;
        $ins['year'] = $row['year'];
        $ins['project_scope'] = 'external';
        $ins['date_published'] = date('Y-m-d');
        $ins['budget_amount'] = empty($row['budget_amount'])? "NULL" : $row['budget_amount'];
        $ins['contract_amount'] = empty($row['amount'])? "NULL":$row['amount'];
        $ins['contractor_name'] = empty($row['name'])? "NULL": $row['name'];
        $ins['contractor_address'] = empty($row['address'])? 'NULL': $row['address'];
        $ins['contractor_email'] = empty($row['email'])? "NULL" : $row['email'];
        $ins['contractor_phone'] = empty($row['phone'])? "NULL" : $row['phone'];
        $id = $this->insert($ins,'cso_projects');
        return $id;

    }
    public function add_new_project($cso_id, $obj){
        $ins = [];
        $ins['title'] = $obj->title;
        $ins['description'] = $obj->description;
        $ins['mda_id'] = $obj->mda;
        $ins['cso_id'] = $cso_id;
        $ins['year'] = $obj->year;
        $ins['location'] = $obj->location;
        $ins['project_scope'] = 'internal';
        $ins['date_published'] = date('Y-m-d');
        $ins['budget_amount'] = $obj->budget_amount;
        $ins['contract_amount'] = $obj->contract_amount;
        $ins['contractor_name'] = $obj->contractor->name;
        $ins['contractor_address'] = $obj->contractor->address;
        $ins['contractor_email'] = $obj->contractor->email;
        $ins['contractor_phone'] = $obj->contractor->phone;
        $id = $this->insert($ins, 'cso_projects');
        return $id;
    }
    public function add_report($project_id, $cso_id, $obj){
        $ins = [];
        $ins['project_id'] = $project_id;
        $ins['cso_id'] = $cso_id;
        $ins['title'] = $obj->title;
        $ins['report'] = $obj->report;
        $ins['filename'] = $obj->filename;
        $ins['cover'] = $obj->cover;
        $ins['date_published'] = date('Y-m-d');
        $id = $this->insert($ins, 'cso_reports');
        return $id;

    }
    public function insert_report_image($report_id, $filenames){
        $ins = [];
        foreach($filenames as $file){
            $ins['imagename'] = $file;
            $ins['report_id'] = $report_id;
            $this->insert($ins, 'report_images');
        }
        
        
    }
    public function reports($page){
        $start = ($page - 1) * 5;
        $start = $start < 0? 0: $start; 
        $end = 5;
        $query = 'SELECT SQL_CALC_FOUND_ROWS r.id, r.title, r.date_published, r.cover, i.name FROM cso_reports r LEFT JOIN institutions i ON r.cso_id = i.id ORDER BY r.id DESC LIMIT 5 
         OFFSET '.$start;
        $result = $this->query($query);
        $n_query = "SELECT FOUND_ROWS()";
        $number = $this->query($n_query);
        $number = mysqli_fetch_array($number)[0];
        $list = mysqli_fetch_all($result,MYSQLI_ASSOC);
        $obj = new stdclass;
        $obj->list = $list;
        $obj->total = $number;
        return $obj;

    }
    
}

?>