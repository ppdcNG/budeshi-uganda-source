<?php
// Model an abstract class that exports basic database functionalities like connecting, reading
//updating e.t.c the database
class Model{
    protected $conn = null;
    protected $result = null;
    public $error = null;
    public $errorNo = null;
    public $absPath = "http://www.naca.gov.ng/open-contracting/";

    function __construct(){
        $this->conn = mysqli_connect(SQL_HOST,SQL_USER,SQL_PASS,SQL_DB);
        if(!$this->conn){
            $this->error = mysqli_error();
            die($this->error);
        }
    }

    function query($querystring){
        $this->result = mysqli_query($this->conn, $querystring);
        if($this->result){
          return $this->result;  
        }
        else{
            $this->error = mysqli_error($this->conn);
            $this->errorNo = mysqli_errno($this->conn);
            die($this->error);
            return false;
        }

    }
    protected function load_helper($name)
    {
        if (is_array($name)) {
            foreach ($name as $nm) {
                $helpername = HELPERS . $nm . ".php";
                if (file_exists($helpername)) {
                    require_once($helpername);
                } else {
                    die('could not find the helper file' . $helpername);
                }
            }
        }
    }

    public function update($id, $fieldSet = [], $table = "institution", $where = "release_id"){
        $fieldString = "";
        if(!empty($fieldSet)){
            foreach($fieldSet as $name=> $value){
                if(is_string($value))
                $fieldString .= $name."= '".mysqli_real_escape_string($this->conn,$value)."', ";
                else
                $fieldString .= $name."=".$value.", ";

            }
            $fieldString = rtrim($fieldString,' ,');
        }
        else{
            echo "Error Empty FieldSet passed to update function..";
            print_r($fieldSet);
            die();
        }
        $query = "UPDATE ".$table." SET ".$fieldString." WHERE ".$where." = '".$id."'";
        $result = $this->query($query);
        if(!$result){
            echo $this->error;
            die();
        }
        return $result;
    }

    public function read($id, $table = "institution"){
        $query = "SELECT * FROM ".$table." WHERE id = ".$id;
        $result = $this->query($query);
        if(!$result){
            echo $this->error;
            die();
        }
        return $result;
    }

    public function delete($id, $table = "institution"){
        $query = "DELETE FROM ".$table." WHERE id = ".$id." LIMIT 1;";
        $result = $this->query($query);
        if(!$result){
            echo $this->error;
            die();
        }
        return $result;
    }
    //create function requires fieldset parmeter to be assoc array of tablecolumn=>value set
    public function create($fieldset, $table = "institution"){
        $fieldString = [];
        $valueString = [];
        if(!empty($fielset)){
            foreach($fieldset as $name=> $value){
                $fieldString[] = $name;
                if(is_string($value)){
                $valueString[] = "'".$value."'";
                }
                else{
                    $valueString[] = $value;
                }
            }
        }
        $fields = "(".implode(",",$fieldString).")";
        $values = "(".implode(",", $valueString).")";

        $query = "INSERT INTO ".$table.$fields." VALUES ".$values;
        $result = $this->query($query);
        if(!$result){
            echo $this->error;
            die();
        }
        return $result;

    }
    public function insert($fieldset, $table){
        $fieldString = [];
        $valueString = [];
        if(!empty($fieldset)){
            foreach($fieldset as $name=> $value){
                $fieldString[] = $name;
                if(is_string($value)){
                $valueString[] = "'".$this->conn->real_escape_string($value)."'";
                }
                else{
                    $valueString[] = $value;
                }
            }
        }
        $fields = "(".implode(",",$fieldString).")";
        $values = "(".implode(",", $valueString).")";

        $query = "INSERT INTO ".$table.$fields." VALUES ".$values;
        //echo $query;
        $result = $this->query($query);
        if(!$result){
            echo $this->error;
            die();
        }
        else{
            $result = $this->conn->insert_id;
        }
        return $result;

    }
    public function queryToJson($query, $prefix= ""){
        $output = array();
        $result = $this->query($query);
        if(!$result){
            die($this->error);
        }
        if(mysqli_num_rows($result) <= 0){
            return "empty";
        }
        else{
            while($row = mysqli_fetch_assoc($result)){
                foreach($row as $name=>$value){
                    $output[$prefix.$name] = $value;
                }
            }
        
            $output["ajaxstatus"] = "success";
            $output["message"] = "Fetched successfully";
            $output = json_encode($output);
            return $output; 
        }
    }
    public function ajaxSuccess($data_obj, $type = "success"){
        
        
    }

    protected function trimText($text, $max = 100, $pgrh = 1)
    {

        $textToReturn = '';
        $len = strlen($text);
        if (strlen($text) > $max) {
            for ($i = 0; $i < $pgrh; $i++)
                {
                if ($pos = strpos($text, '\n'))
                    {
                    $textToReturn .= substr($text, 0, $pos);
                    $text = substr($text, $pos + 1, $len);
                }
                else {
                    $pos = strrpos($text, ' ');
                    $textToReturn .= substr($text, 0, $max) . "...";
                }
            }
        }
        else {
            $textToReturn = $text;
        }
        return $textToReturn;
    }
    public function generate_ocid($mda_id){
        $query = "SELECT short_name FROM mdas WHERE id = ".$mda_id;
        $result = $this->query($query);
        if(!$result){
            die($this->error);
        }
        $name =strtolower(mysqli_fetch_array($result)[0]);
        $code = md5(time()* rand(0, 22883746));
        $code = substr($code,0,3).substr($code,-3)."ng";
        $ocid = "ocds-".OC_PREFIX."-".$code."-".$name;
        return $ocid;
    }
    public function renderRow($type, $value){
        $type = strtolower($type);
        $row = "";
      
            $row = "<".$type.">".$value."</".$type.">";
        
        return $row;
    }
    public function upsert($insertfields, $updatefields, $table){
        $fieldString = [];
        $valueString = [];
        if(!empty($insertfields)){
            foreach($insertfields as $name=> $value){
                $fieldString[] = $name;
                if(is_string($value)){
                $valueString[] = "'".$this->conn->real_escape_string($value)."'";
                }
                else{
                    $valueString[] = $value;
                }
            }
        }
        $fields = "(".implode(",",$fieldString).")";
        $values = "(".implode(",", $valueString).")";

        $fieldString = "";
        if(!empty($updatefields)){
            foreach($updatefields as $name=> $value){
                if(is_string($value))
                $fieldString .= $name."= '".mysqli_real_escape_string($this->conn,$value)."', ";
                else
                $fieldString .= $name."=".$value.", ";

            }
            $fieldString = rtrim($fieldString,' ,');
        }
        else{
            echo "Error Empty FieldSet passed to update function..";
            print_r($fieldSet);
            die();
        }

        $query = "INSERT INTO ".$table.$fields." VALUES ".$values. " ON DUPLICATE KEY UPDATE ".$fieldString;
        $result = $this->query($query);
        return true;


    }
    public function select($fields, $table, $where_col = "id", $where_val = ""){
        $value = is_string($where_val) and !empty($where_col) ? "'".$where_val."'": $where_val;
        $query = "SELECT ".$fields." FROM ".$table." WHERE ".$where_col ;

    }
    public function check_value($fields, $table, $where_col = 'id', $wher_val = ""){
        $where = empty($where_col) && $wher_val? "": " WHERE ".$where_col." = ".is_string($wher_val)?"'".$wher_val."'": $wher_val;
        $query = "SELECT ".$fields.$where;
        $result = $this->query($query);
        if(mysqli_num_rows($result) > 0){
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        else{
            return false;
        }
    }
    public function queryToSelectOption($query,$tag = 'option'){
        $result = $this->query($query);
        $output = "";
        
        if($result)while($row = mysqli_fetch_row($result)){
            if($tag == 'option'){
                $output .= "<option value = '".$row[0]."'>".$row[1]."</option>";
            }
            else{
                $output .= "<".$tag." id = '".$row[0]."'>".$row[1]."</".$tag.">";
            }
            
        }
        else $output = FALSE;
        return $output;
    }
    
}