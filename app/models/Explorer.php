<?php
class Explorer extends Model
{
    public $project_cards = "";
    public $table_rows = "";
    public $perpage = 8;
    public $projects_array = [];
    public $total;
    private $pages;
    public $avg;
    public $max;
    public $max_title;
    public $min_title;
    public $min;
    public $sum;
    public $goods;
    public $works;
    public $services;
    public $direct;
    public $limited;
    public $selective;
    public $open;
    public $mdas_html;
    public $mdas;
    public $projectHtml;
    public $verifiedTable;
    public $lowest_row;
    public $highest_row;
    public $projectTitle;



    public function __construct()
    {
        Parent::__construct();
    }
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->total / $this->perpage;
        }
    }
    public function get_charts($by, $search_query, $order = 'DESC')
    {
        $queries = $this->getSQL($search_query);
        $attach = implode("AND ", $queries);
        $attach = empty($attach) ? "" : " AND " . $attach;
        $q_by = "";
        $q_feilds = "";
        switch ($by) {
            case 'short_name':
                $q_by = 'm.name';
                $q_feilds = 'm.name, UPPER(m.short_name)';
                break;
            case 'year':
                $q_by = 'p.year';
                $q_feilds = 'p.year';
                break;

            case 'category':
                $q_by = 'p.project_category';
                $q_feilds = 'p.project_category';
                break;
            case 'lga':
                $q_by = 'p.lga';
                $q_feilds = 'p.lga';
                $attach = $attach . " AND p.lga != 'NULL'";
                break;
            case 'method':
                $q_by = 't.procurement_method';
                $q_feilds = 't.procurement_method';
                $attach = $attach . " AND t.procurement_method != 'NULL'";

        }
        $query = "SELECT COUNT(p.id), " . $q_feilds . " FROM projects p LEFT JOIN mdas m ON p.mda_id = m.id LEFT JOIN tender t ON p.id = t.project_id WHERE  p.published = 'yes' " . $attach . "  GROUP BY(" . $q_by . ") 
        ORDER BY COUNT(p.id) " . $order;
        $result = $this->query($query);
        if (mysqli_num_rows($result) < 1) {
            return false;
        }
        $number = mysqli_fetch_all($result);
        $query = 'SELECT SUM(c.amount), ' . $q_feilds . ' FROM contract c LEFT JOIN projects p on c.project_id = p.id LEFT JOIN tender t ON p.id = t.project_id LEFT JOIN mdas m ON m.id = p.mda_id WHERE p.published = "yes" ' . $attach . "GROUP BY(" . $q_by . ") 
        ORDER BY SUM(c.amount) " . $order;
    
        $result = $this->query($query);
        $sum = mysqli_fetch_all($result);
        $data = new Stdclass;
        $data->number_of_procurement = $number;
        $data->sum_of_procurment_activity = $sum;
        return $data;
    }

    public function projects($search_query = "")
    {
        $queries = $this->getSQL($search_query);
        $attach = implode("AND ", $queries);
        $attach = empty($attach) ? "" : " AND " . $attach;

        $query = "SELECT  COUNT(p.id) AS num, AVG(c.amount) AS average, SUM(c.amount) AS total FROM  projects p LEFT JOIN mdas m ON p.mda_id = m.id 
        LEFT JOIN contract c ON c.project_id = p.id LEFT JOIN contractors ct ON p.id = ct.project_id LEFT JOIN institutions i ON ct.contractor_id = i.id 
        LEFT JOIN tender t ON p.id = t.project_id WHERE p.published = 'yes' " . $attach;
        $result = $this->query($query);
        $row = mysqli_fetch_assoc($result);
        $this->total = $row['num'];
        $this->avg = $row['average'];
        $this->sum = $row['total'];
        $query = "SELECT tend.c_amount AS max_amount, tend.project_id FROM (SELECT c.amount as c_amount, c.project_id FROM contract c LEFT JOIN mdas m ON c.mda_id = m.id 
        LEFT JOIN projects p ON c.project_id = p.id LEFT JOIN contractors ct ON c.project_id = ct.project_id LEFT JOIN tender t ON 
        c.project_id = t.project_id WHERE p.published = 'yes' AND c.amount != 'NULL' ".$attach."  ORDER BY (c.amount) ASC LIMIT 1 ) AS tend ";
        $result = $this->query($query);
        $row = mysqli_fetch_assoc($result);
        $this->min = $row['max_amount'];
        //$this->min_title = $row['title'];
        $this->min_id = $row['project_id'];

        $query = "SELECT tend.c_amount AS max_amount, tend.project_id FROM (SELECT c.amount as c_amount, c.project_id FROM contract c LEFT JOIN mdas m ON c.mda_id = m.id 
        LEFT JOIN projects p ON c.project_id = p.id LEFT JOIN contractors ct ON c.project_id = ct.project_id LEFT JOIN tender t ON 
        c.project_id = t.project_id WHERE p.published = 'yes'  ".$attach." ORDER BY (c.amount) DESC LIMIT 1) AS tend";
        $result = $this->query($query);
        $row = mysqli_fetch_assoc($result);
        $this->max = $row['max_amount'];
        //$this->max_title = $row['title'];
        $this->max_id = $row['project_id'];
       
        //query mdas
       
        
        
        //query project category grouping
        $query = "SELECT COUNT(p.id), p.project_category FROM projects p LEFT JOIN mdas m ON p.mda_id = m.id LEFT JOIN tender t ON p.id = t.project_id WHERE  p.published = 'yes' " . $attach . "  GROUP BY(p.project_category)";

        $result = $this->query($query);

        
        $categories = mysqli_fetch_all($result);
        $this->categories = $categories;
        ///Procurement Method grouping
        $query = "SELECT COUNT(tend.id), tend.procurement_method FROM (SELECT DISTINCT * FROM tender)  as tend LEFT JOIN projects p ON p.id = tend.project_id LEFT JOIN mdas m ON p.mda_id = m.id
        LEFT JOIN contractors ct ON p.id = ct.project_id LEFT JOIN tender t ON p.id = t.project_id  " . $attach . " GROUP BY procurement_method ";
        $result = $this->query($query);
        $row = mysqli_fetch_array($result);
        $this->direct = isset($row['direct']) ? $row['direct'] : "";
        $this->selective = isset($row['selective']) ? $row['selective'] : "";
        $this->limited = isset($row['limited']);
        $this->open = isset($row['open']) ? $row['selective'] : "";

        

        return true;
    }
    public function indexData($search_query = ''){
        $queries = $this->getSQL($search_query);
        $attach = implode("AND ", $queries);
        $attach = empty($attach) ? "" : " AND " . $attach;
        $query = "SELECT id, short_name, name FROM mdas WHERE id IN (SELECT DISTINCT mda_id FROM projects) " . $attach . " ORDER BY name ASC";
        $result = $this->query($query);
        while ($row = mysqli_fetch_array($result)) {
            $this->mdas_html .= $this->renderMda($row["id"], ucwords(strtolower($row["name"])));
            $this->mdas .= $this->renderMda(strtoupper($row["short_name"]), ucwords(strtolower($row["name"])) . " (" . strtoupper($row["short_name"]) . ")");
        }


    }
    public function get_cso_reports(){
        $query = 'SELECT r.date_published, r.id, i.name, p.title FROM cso_reports r LEFT JOIN 
        cso_projects p ON r.project_id = p.id LEFT JOIN institutions i ON r.cso_id = i.id';
        $result = $this->query($query);
        $output = "";
        if(mysqli_num_rows($result)> 0){
            while($row = mysqli_fetch_assoc($result)){
                $output.="<tr>";
                $output.='<td><a href = "#" onclick = "viewMonitored(\''.$row['id'].'\')">'.$row['title'].'</a></td>';
                $output .= '<td>'.$row['name'].'</td>';
                $output .= '<td>'.date('jS F',strtotime($row['date_published'])).'</td>';
                $output .= "</tr>";
            }
        }
        else{
            $output = "<p> No Reports Added Yes</p>";
        }
        return $output;
    }
    
    public function fetchMDA($mda_id, $search, $start, $length)
    {
        $where = empty($search) ? "" : " AND p.title LIKE '%" . mysqli_real_escape_string($this->conn, $search) . "%' OR m.name LIKE '%" . mysqli_real_escape_string($this->conn, $search) .
            "%' OR m.short_name LIKE '%" . mysqli_real_escape_string($this->conn, $search) . "%' OR i.name LIKE '%" . mysqli_real_escape_string($this->conn, $search) . "%'";
        $arrayToReturn = [];
        $query = "SELECT SQL_CALC_FOUND_ROWS  p.oc_id,p.title,p.year, p.id, p.state, p.status, p.monitored, p.description, m.short_name, m.name AS mda, b.budget_amount, c.amount, ct.contractor_id, i.name, cd.latitude, cd.longitude FROM projects p LEFT JOIN mdas m
         ON p.mda_id = m.id LEFT JOIN planning b on p.id = b.project_id LEFT JOIN contract c ON c.project_id = p.id LEFT JOIN contractors ct ON p.id = ct.project_id LEFT JOIN institutions i ON ct.contractor_id = i.id LEFT JOIN cordinates cd ON p.state = cd.state WHERE p.published = 'yes'
        AND p.mda_id = " . $mda_id . $where . " ORDER BY p.id DESC LIMIT " . $length . " OFFSET " . $start;
        $result = $this->query($query);
        $n_query = "SELECT FOUND_ROWS()";
        $number = $this->query($n_query);
        $number = mysqli_fetch_array($number)[0];
        $this->total = $number;
        while ($row = mysqli_fetch_assoc($result)) {
            $rt = [];
            $rt['id'] = $row['id'];
            $rt['title'] = ucwords(strtolower($row['title']));
            $rt['state'] = $row['state'];
            $rt['name'] = ucwords(strtolower($row['name']));
            $rt['budget_amount'] = empty($row['budget_amount']) || $row['budget_amount'] == '0.00' ? "Not Provided" : $row['budget_amount'];
            $rt['amount'] = empty($row['amount']) || $row['budget_amount'] == '0.00' ? "Not Provided" : $row['amount'];
            $rt['year'] = $row['year'];
            $rt['mda'] = ucwords(strtolower($row['mda'])) . " (" . strtoupper($row['short_name']) . ")";
            $arrayToReturn[] = $rt;
        }
         //echo "blah";
         //echo json_encode($arrayToReturn); die;
        return $arrayToReturn;

    }
    public function getTableData($start, $length, $search, $draw)
    {
        $where = empty($search) ? "" : "AND p.title LIKE '%" . mysqli_real_escape_string($this->conn, $search) . "%' OR m.name LIKE '%" . mysqli_real_escape_string($this->conn, $search) .
            "%' OR m.short_name LIKE '%" . mysqli_real_escape_string($this->conn, $search) . "%' OR i.name LIKE '%" . mysqli_real_escape_string($this->conn, $search) . "%'";
        $arrayToReturn = [];
        $query = "SELECT SQL_CALC_FOUND_ROWS  p.oc_id,p.title,p.year, p.id, p.state, p.status, p.monitored, p.description, m.short_name, m.name AS mda, b.budget_amount, c.amount, ct.contractor_id, i.name, FROM projects p LEFT JOIN mdas m
         ON p.mda_id = m.id LEFT JOIN planning b on p.id = b.project_id LEFT JOIN contract c ON c.project_id = p.id LEFT JOIN contractors ct ON p.id = ct.project_id LEFT JOIN institutions i ON ct.contractor_id = i.id WHERE p.published = 'yes'
         " . $where . " ORDER BY p.id DESC LIMIT " . $length . " OFFSET " . $start;

        $result = $this->query($query);
        $n_query = "SELECT FOUND_ROWS()";
        $number = $this->query($n_query);
        $number = mysqli_fetch_array($number)[0];
        $this->total = $number;
        while ($row = mysqli_fetch_assoc($result)) {
            $rt = [];
            $rt['nothing'] = "";
            $rt['id'] = $row['id'];
            $rt['title'] = ucwords(strtolower($row['title']));
            $rt['state'] = $row['state'];
            $rt['name'] = ucwords(strtolower($row['name']));
            $rt['budget_amount'] = empty($row['budget_amount']) || $row['budget_amount'] == 0.00 ? "Not Provided" : $row['budget_amount'];
            $rt['amount'] = empty($row['amount']) || $row['budget_amount'] == '0.00' ? "Not Provided" : $row['amount'];
            $rt['year'] = $row['year'];
            $rt['mda'] = ucwords(strtolower($row['mda']));
            $arrayToReturn[] = $rt;
        }
        //echo "blah";
        //echo json_encode($arrayToReturn); die;
        return $arrayToReturn;



    }


    public function ajaxsearch($search_query, $start, $length, $ordering, $search)
    {
        $column_indexs = ['p.id', 'p.title', 'b.budget_amount', 'c.amount', 'i.name', 'p.lga', 'p.year'];
        $ordering = $ordering[0];
        $order_column = $column_indexs[$ordering['column']];
        $order_dir = $ordering['dir'];

        $where = empty($search) ? "" : " AND p.title LIKE '%" . mysqli_real_escape_string($this->conn, $search) . "%' OR m.name LIKE '%" . mysqli_real_escape_string($this->conn, $search) .
            "%' OR m.short_name LIKE '%" . mysqli_real_escape_string($this->conn, $search) . "%' OR i.name LIKE '%" . mysqli_real_escape_string($this->conn, $search) . "%' OR p.lga LIKE '%" .
            mysqli_real_escape_string($this->conn, $search) . "%'";
        $arrayToReturn = [];
        $queries = $this->getSQL($search_query);
        $attach = implode("AND ", $queries);
        $attach = empty($attach) ? "" : " AND " . $attach;
        $query = "SELECT SQL_CALC_FOUND_ROWS p.oc_id,p.title,p.project_category,p.year, p.id, p.lga, m.short_name, m.name AS mda, b.budget_amount, c.amount, ct.contractor_id, i.name FROM projects p LEFT JOIN mdas m ON 
        p.mda_id = m.id LEFT JOIN planning b on p.id = b.project_id LEFT JOIN contract c ON c.project_id = p.id LEFT JOIN tender t ON p.id = t.project_id LEFT JOIN contractors ct ON p.id = ct.project_id LEFT JOIN institutions i ON ct.contractor_id = i.id " . " 
        WHERE p.published = 'yes' " . $attach . $where . " ORDER BY " . $order_column . " " . strtoupper($order_dir) . " LIMIT " . $length . " OFFSET " . $start;

        $result = $this->query($query);
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
            $rt['nothing'] = "";
            $rt['id'] = $row['id'];
            $rt['title'] = ucwords(strtolower($row['title']));
            $rt['lga'] = empty($row['lga']) || $row['lga'] == 'NULL' ? "N/A" : $row['lga'];
            $rt['name'] = ucwords(strtolower($row['name']));
            $rt['budget_amount'] = empty($row['budget_amount']) || $row['budget_amount'] == 0.00 ? "N/A" : $row['budget_amount'];
            $rt['amount'] = empty($row['amount']) || $row['amount'] == '0.00' ? "N/A" : $row['amount'];
            $rt['year'] = $row['year'];
            $rt['mda'] = $row['mda'];
            $arrayToReturn[] = $rt;
        }
        $query = "SELECT MIN(c.amount) AS lowest, MAX(c.amount) AS highest, AVG(c.amount) AS average, SUM(c.amount) AS total FROM projects p LEFT JOIN mdas m
        ON p.mda_id = m.id LEFT JOIN planning b on p.id = b.project_id LEFT JOIN tender t ON p.id = t.project_id LEFT JOIN contract c ON c.project_id = p.id LEFT JOIN contractors ct ON p.id = ct.project_id LEFT JOIN institutions i ON ct.contractor_id = i.id" .
            " WHERE p.published = 'yes' " . $attach . " ORDER BY p.id DESC LIMIT " . $length . " OFFSET " . $start;
        $result = $this->query($query);
        $row = mysqli_fetch_assoc($result);
        $this->max = $row['highest'];
        $this->min = $row['lowest'];
        $this->avg = $row['average'];
        return $arrayToReturn;

    }
    public function getMonitoringImage($id){
        $query = 'SELECT * FROM report_images WHERE report_id = '.$id;
        $result = $this->query($query);
        $output = "";
        if(mysqli_num_rows($result)> 0){
            while($row = mysqli_fetch_assoc($result)){
                $output .= '<div>
                                <a class="uk-inline" uk-toggle href="' . ABS_PATH .'images/monitoring/'. $row['imagename'] . '" caption="' . $row['caption'] . '">
                                        <img src="' . ABS_PATH.'images/monitoring/' . $row['imagename'] . '" alt="">
                                </a>
                                </div>';

            }
        }
        return $output;
    }
    public function getMonitoredDetails($id){
        $query = 'SELECT * FROM report_images WHERE report_id = '.$id;
        $result = $this->query($query);
        $images = "";
        $report_pdf = "";
        if(mysqli_num_rows($result)> 0){
            while($row = mysqli_fetch_assoc($result)){
                $images .= '<div>
                                <a class="uk-inline" uk-toggle href="' . ABS_PATH .'images/monitoring/'. $row['imagename'] . '" caption="' . $row['caption'] . '">
                                        <img src="' . ABS_PATH.'images/monitoring/' . $row['imagename'] . '" alt="">
                                </a>
                                </div>';
                

            }
        }
        return $images;

    }
    public function getMonitorsReport($id){
        $query = "SELECT r.report, r.title, r.id, r.filename, r.date_published, i.name, p.title AS project_title FROM cso_reports r LEFT JOIN
        cso_projects p ON r.project_id = p.id LEFT JOIN institutions i ON r.cso_id = i.id WHERE r.id = ".$id;
        $result = $this->query($query);
        $output = "";
        $report_pdf = "";
        if(mysqli_num_rows($result) > 0){
            $row = mysqli_fetch_assoc($result);
            $output .= '<div>
                    <div class="uk-tile uk-tile-default uk-tile-small">
                        ';
                        $output .= '
                    <div clas="uk-panel uk-panel-scrollable">' . $row['report']. '
                    </div>';
                        $output .= '</div>
                    </div>';
                    $this->projectTitle = $row['project_title'];
                    if(!empty($row['filename'])){
                        $report_pdf .= '<div>
                        <h4 class="uk-heading-line uk-text-left">
                            <em title="Download Monitoring Documents Attached" uk-tooltip="pos: left">
                                <span class="uk-margin-small-right" uk-icon="icon: document; ratio: 1"></span>Monitoring
                                Document</em>
                        </h4>
                        <a class = "uk-padding" href = "'.ABS_PATH.'images/report/'.$row['filename'].'"><span uk-icon = "icon: download; ratio: 2"></span></a>
    
                    </div>';
                }
        }
        $obj = new stdclass();
        $obj->report = $output;
        $obj->report_pdf = $report_pdf;
        $obj->cso_name = $row['name'];
        $obj->date_published = date('jS M Y',strtotime($row[ 'date_published']));
        return $obj;
    }

    public function summaryStat($search_query)
    {
        $arrayToReturn = [];
        $queries = $this->getSQL($search_query);
        $attach = implode("AND ", $queries);
        $attach = empty($attach) ? "" : " AND " . $attach;

        $query = "SELECT MIN(c.amount) AS lowest, MAX(c.amount) AS highest, AVG(c.amount) AS average, SUM(c.amount) AS total FROM projects p LEFT JOIN mdas m
        ON p.mda_id = m.id LEFT JOIN planning b on p.id = b.project_id LEFT JOIN contract c ON c.project_id = p.id LEFT JOIN contractors ct ON p.id = ct.project_id LEFT JOIN institutions i ON ct.contractor_id = i.id" .
            " WHERE p.published = 'yes' " . $attach . " ORDER BY p.id DESC LIMIT " . $length . " OFFSET " . $start;
        $result = $this->query($query);
        $row = mysqli_fetch_assoc($result);
        $this->max = $row['highest'];
        $this->min = $row['lowest'];
        $this->avg = $row['average'];

        $query = "SELECT COUNT(p.id), p.procurement_category FROM projects p LEFT JOIN mdas m ON p.mda_id = m.id LEFT JOIN planning b on p.id = b.project_id LEFT JOIN contract c ON 
        c.project_id = p.id LEFT JOIN contractors ct ON p.id = ct.project_id LEFT JOIN institution i ON ct.contractor_id = i.id " . " WHERE p.published = 'yes' " . $attach . " GROUP BY p.project_category";
        $result = $this->query($query);
        $row = mysqli_fetch_assoc($query);
        $this->direct = $row['direct'];
        $this->open = $row['open'];
        $this->limited = $row['limited'];
        $this->selective = $row['selective'];
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
    public function getProjectObj($id, $type)
    {
        $query = "SELECT release_id FROM " . $type . " WHERE project_id = " . $id;
        $result = $this->query($query);
        if (mysqli_num_rows($result) < 0) {
            return null;
        }
        $row = mysqli_fetch_array($result)[0];
        if (empty($row)) {
            return false;
        }
        $file = FILE_ROOT . "releases/" . $row . ".json";
        $file = fopen($file);
        $fileJson = fread($file, 100000);
        $obj = json_decode($fileJson);
        return $obj;

    }

    public function renderProjectCard($id, $mda, $title, $des, $state, $year)
    {

        $html = '<div class = "project-card">
                <div class="uk-card uk-card-default uk-card-hover uk-card-body baka-card" onclick="viewSummary(\'' . $id . '\')">
                    <div class="uk-card-badge uk-label">' . $year . '</div>
                    <h3 class="uk-card-title uk-heading-bullet">' . $state . '</h3>
                    <p>' . $mda . '</p>
                    <p  class="uk-text-truncate" title="' . $title . '" uk-tooltip>' . $title . '</p>
                </div>
            </div>';
        return $html;
    }
    public function renderTable($id, $ocid, $title, $state, $contractor, $c_amount, $b_amount, $year, $mda, $status, $monitored)
    {
        //$monitored = ($monitored == 'no') ? '<span class="uk-margin-small-right" uk-icon="icon: close"></span>' : '<span class="uk-margin-small-right" uk-icon="icon: check"></span>';
        $html = '<tr>
                    <td></td>
					<td><a href = "#" onclick = "viewSummary(\'' . $id . '\')">' . $title . '</a> </td>
					<td>' . $state . '</td>
					<td>' . $contractor . '</td>
					<td>' . $c_amount . '</td>
                    <td>' . $b_amount . '</td>
					<td>' . $year . '</td>
                    <td>' . $mda . '</td>
                    <td>' . $status . '</td>
				</tr>';
        return $html;
    }
    public function renderCompareTable($id, $title, $state, $contractor, $c_amount, $b_amount, $year, $mda, $status)
    {
        $dict = array(
            "Not Provided" => "Not Available", "Active Complete" => "The Project is Completed and Seen to be in use at the time of monitoring", "Active Incomplete" => "The project is in use, but has not been completed as at the time of monitoring",
            "Inactive Complete" => "The project has been completed, however it was seen not to be in use as at the time of monitoring", "Inactive Incomplete" => "The project has not been completed and not in use as at the time of Monitoring", "N/A" => 'Not provided',
            "Not Located" => "Project was not Located at the time of monitoring"
        );
        
        return '<tr>
        <td><a onclick ="viewMonitored(\'' . $id . '\')">' . $title . '</a></td>
        <td>' . $contractor . '</td>
        <td>' . $c_amount . '</td>
        <td>' . $b_amount . '</td>
        <td> ' . $year . '</td>
        <td>' . $mda . '</td>
        <td>'.$status.'</td></tr>';
        
    }
    public function renderMda($value, $option)
    {
        $html = "<option value = '" . $value . "'>" . $option . "</option>";
        return $html;
    }
    public function feedback($data)
    {
        $insert['firstname'] = $data->firstname;
        $insert['lastname'] = $data->lastname;
        $insert['email'] = $data->email;
        $insert['phone'] = $data->phone;
        $insert['date_posted'] = date('Y-m-d H:i:s');
        $insert['project_id'] = $data->projectId;
        $insert['feedback_comment'] = $data->comment;
        $result = $this->insert($insert, 'feedbacks');
        return true;
    }
    public function supplier($id){
        $query = 'SELECT c.project_id AS projectId, i.* FROM contractors c LEFT JOIN institutions i ON c.contractor_id = i.id WHERE c.project_id = '.$id;
        $result = $this->query($query);
        $row = mysqli_fetch_assoc($result);
        return $row;
    }
    public function download($search_query)
    {
        $arrayToReturn = [];
        $queries = $this->getSQL($search_query);
        $attach = implode("AND ", $queries);
        $attach = empty($attach) ? "" : " AND " . $attach;
        $query = "SELECT p.title, p.state,i.name, b.budget_amount, c.amount, p.year, CONCAT(m.name, ' (', m.short_name,')') AS mda FROM projects p LEFT JOIN mdas m
         ON p.mda_id = m.id LEFT JOIN planning b on p.id = b.project_id LEFT JOIN contract c ON c.project_id = p.id LEFT JOIN contractors ct ON p.id = ct.project_id LEFT JOIN institutions i ON ct.contractor_id = i.id " .
            " WHERE p.published = 'yes' " . $attach . " ORDER BY p.id DESC";
        $result = $this->query($query);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Type:application/force-download');
        header('Content-Disposition: attachment; filename="budeshi.csv"');
        $headers = ['title', 'state', 'contractor', 'Budget Amount', 'Contract Amount', 'Year', 'MDA'];
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        while ($row = mysqli_fetch_row($result)) {
            fputcsv($output, $row);
        }
        exit;
    }
    public function download_pdf($search_query)
    {

    }
    public function render_comment($name, $date, $comment)
    {
        $html = '<li>
        <article class="uk-comment uk-visible-toggle">
            <header class="uk-comment-header uk-position-relative">
                <div class="uk-grid-medium uk-flex-middle" uk-grid>
                    <div class="uk-width-expand">
                        <h4 class="uk-comment-title uk-margin-remove"><a class="uk-link-reset" href="#">' . $name . '</a></h4>
                        <p class="uk-comment-meta uk-margin-remove-top"><a class="uk-link-reset" href="#">' . $date . '</a></p>
                    </div>
                </div>

            </header>
            <div class="uk-comment-body">
                <p>' . $comment . '</p>
            </div>
        </article>
    </li>';
        return $html;
    }
    public function getComments($id){

        $query = 'SELECT * FROM feedbacks WHERE published = "yes" AND project_id = '.$id;
        $result = $this->query($query);
        $feedbacks = '';
        if(mysqli_num_rows($result)> 0){
            while($row = mysqli_fetch_array($result)){
                $date =str_replace('*','at',date('jS F Y  * H:ia', strtotime($row['date_posted'])));
                $feedbacks.= $this->render_comment($row['lastname'],$date,$row['feedback_comment']);
            }
            $feedbacks .= "<h3 class = 'uk-heading-bullet'>Drop Your Comments About this Project</h3>";
        }
        else{
            $feedbacks = 'Be the first see comment on this project';
        }
        return $feedbacks;
    }
    public function fetch_parties($id, $project_id){
        $query = 'SELECT release_id FROM releases WHERE project_id = '.$project_id.' ORDER BY id DESC limit 1';
        $result = $this->query($query);
        $release_id = mysqli_fetch_assoc($result)['release_id'];
        $path = FILE_ROOT.'app/releases/'.$release_id.'.json';
        if(file_exists($path)){
            $release = json_decode(file_get_contents($path));
            $d_pary = '';
            if(isset($release->parties) && is_array($release->parties)){
                //var_dump($release->parties);
                //die;
                foreach($release->parties as $party){
                    if($party->id == $id){
                        
                        $d_pary = $party;
                    }
                }
                return $d_pary;
            }
            else{
                return "";
            }
        }
        else{
            return "";
        }
    }
    public function fetch_supplier($project_id){
        $query = 'SELECT release_id FROM releases WHERE project_id = '.$project_id.' ORDER BY id DESC limit 1';
        $result = $this->query($query);
        $release_id = mysqli_fetch_assoc($result)['release_id'];
        $path = FILE_ROOT.'app/releases/'.$release_id.'.json';
        if(file_exists($path)){
            $release = json_decode(file_get_contents($path));
            $d_pary = '';
            if(isset($release->parties) && is_array($release->parties)){
                //var_dump($release->parties);
                //die;
                foreach($release->parties as $party){
                    if(in_array('supplier',$party->roles)){
                        
                        $d_pary = $party;
                    }
                }
                return $d_pary;
            }
            else{
                return "";
            }
        }
        else{
            return "";
        }
    }
    private function getSinceTime($date){
        $date = strtotime($date);
        $now = time();
        $diff = $now - $date;
        if($diff > 36000);
    }

}
?>