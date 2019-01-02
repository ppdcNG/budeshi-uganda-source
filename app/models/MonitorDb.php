<?php
class MonitorDb extends Model
{
    public $MDAS = null;

    public function __construct()
    {
        Parent::__construct();
    }
    public function getMDAList()
    {
        $query = "SELECT * FROM mdas ORDER BY id DESC";
        $result = $this->query($query);
        $outHTML = "";
        if (!$result) {
            die($this->error);
        }
        else {
            if (mysqli_num_rows($result) <= 0) {
                $outHTML = "<em>No MDAS found</em>";
            }
            else {
                while ($row = mysqli_fetch_array($result)) {
                    $outHTML .= $this->tableListTemplate($row["id"], $row["name"],$row["short_name"]);
                }
            }
        }
        return $outHTML;
    }
    private function tableListTemplate($id, $name, $short_name)
    {
        $tableRow = "<tr>
                    <td><a href='#gin' onclick = 'editmodal(\"".$id."\")' title='Edit MDA' uk-tooltip='pos: bottom'><span class='uk-margin-small-right' uk-icon='icon: file-edit'></span></a></td>
                    <td><a href= '" . ABS_PATH. "Project/" . $id . "'>" . $name ."</a></td>
                    <td><a href='#' title='Delete MDA' uk-tooltip='pos: bottom'><span class='uk-margin-small-right' uk-icon='icon: trash' onclick = 'delete_mda(\"".$id."\")'></span></a></td>
                </tr>";
        return $tableRow;
    }
    
    
    public function getMDAProjects($id)
    {
        $output = "";
        $query = "SELECT * FROM projects WHERE mda_id = ".$id;
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        else{
            while($row = mysqli_fetch_array($result)){
                $output .= $this->projectListTemplate($row["id"],$row["title"],$row["description"],$row["state"],$row["year"]);
            }
        }
        return $output;

    }
    
    public function getMDA($mda_id){
        $json_obj = null;
        $query = "SELECT * FROM mdas WHERE id = ".$mda_id;
        $result = $this->queryToJson($query, "e_");
        return $result;
        
    }
    public function addMDA($obj){
        $email = isset($obj->email)? $obj->email : "NULL";
        $ins['name'] = $insert_mda['name'] = $obj->commonName;
        $insert_mda['short_name'] = $obj->shortname;
        $ins['address'] = $insert_mda['address'] = $obj->address;
        $ins['phone'] = $insert_mda['phone'] = $obj->phone;
        $insert_mda['sector'] = $obj->sector;
        $ins['url'] = $insert_mda['website'] = $obj->website;
        $ins['email'] = $insert_mda['email'] = $email;
        $ins['ug_no'] = $insert_mda['ug_id'] = $obj->ug_id;

        $this->insert($insert_mda, 'mdas');
        if(!$this->select('name, id', 'institutions','name', $insert_mda['name'])) $this->insert($ins,'institutions');
        return true;

    }
    public function updateMDA($id, $obj){
        $data = Array();
        $data["name"] = $obj->commonName;
        $data["address"] = $obj->address or "";
        $data["short_name"] = $obj->shortname;
        $data["email"] = $obj->email or "NULL";
        $data["website"] = $obj->website or "";
        $data["phone"] = $obj->phone or "NULL";
        $data['ug_id'] = $obj->ug_id or "NULL";
        $this->update($id,$data,"mdas","id");


    }
    public function relativeTime($time, $short = false){
        $SECOND = 1;
        $MINUTE = 60 * $SECOND;
        $HOUR = 60 * $MINUTE;
        $DAY = 24 * $HOUR;
        $MONTH = 30 * $DAY;
        $before = time() - $time;
    
        if ($before < 0)
        {
            return "not yet";
        }
    
        if ($short){
            if ($before < 1 * $MINUTE)
            {
                return ($before <5) ? "just now" : $before . " ago";
            }
    
            if ($before < 2 * $MINUTE)
            {
                return "1m ago";
            }
    
            if ($before < 45 * $MINUTE)
            {
                return floor($before / 60) . "m ago";
            }
    
            if ($before < 90 * $MINUTE)
            {
                return "1h ago";
            }
    
            if ($before < 24 * $HOUR)
            {
    
                return floor($before / 60 / 60). "h ago";
            }
    
            if ($before < 48 * $HOUR)
            {
                return "1d ago";
            }
    
            if ($before < 30 * $DAY)
            {
                return floor($before / 60 / 60 / 24) . "d ago";
            }
    
    
            if ($before < 12 * $MONTH)
            {
                $months = floor($before / 60 / 60 / 24 / 30);
                return $months <= 1 ? "1mo ago" : $months . "mo ago";
            }
            else
            {
                $years = floor  ($before / 60 / 60 / 24 / 30 / 12);
                return $years <= 1 ? "1y ago" : $years."y ago";
            }
        }
    
        if ($before < 1 * $MINUTE)
        {
            return ($before <= 1) ? "just now" : $before . " seconds ago";
        }
    
        if ($before < 2 * $MINUTE)
        {
            return "a minute ago";
        }
    
        if ($before < 45 * $MINUTE)
        {
            return floor($before / 60) . " minutes ago";
        }
    
        if ($before < 90 * $MINUTE)
        {
            return "an hour ago";
        }
    
        if ($before < 24 * $HOUR)
        {
    
            return (floor($before / 60 / 60) == 1 ? 'about an hour' : floor($before / 60 / 60).' hours'). " ago";
        }
    
        if ($before < 48 * $HOUR)
        {
            return "yesterday";
        }
    
        if ($before < 30 * $DAY)
        {
            return floor($before / 60 / 60 / 24) . " days ago";
        }
    
        if ($before < 12 * $MONTH)
        {
    
            $months = floor($before / 60 / 60 / 24 / 30);
            return $months <= 1 ? "one month ago" : $months . " months ago";
        }
        else
        {
            $years = floor  ($before / 60 / 60 / 24 / 30 / 12);
            return $years <= 1 ? "one year ago" : $years." years ago";
        }
    
        return "$time";
    }
    public function render_feedbacks($page){
        $starte = PERPAGE * ($page - 1);
        $query = 'SELECT f.*, p.title feedbacks f LEFT JOIN projects p ON f.project_id = p.id ORDER BY date_posted DESC  LIMIT '.$start.' OFFSET '.PER_PAGE;
        $result = $this->query($query);
        if(mysqli_num_rows($result)>0){
            $output = "";
            while($row = mysqli_fetch_assoc($result)){

            }
        }
    }
    private function backendFeedback($id, $name, $time, $comment,$title ){
        $html = '';
    }



    


}
?>