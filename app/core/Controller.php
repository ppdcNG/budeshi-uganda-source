<?php 
class Controller{
    public $absPath = "http://www.budeshi.ng/";
    public $fileroot = "C:/xampp/htdocs/budeshi-2.0/app/";
    public $notSet = [];

    public function checkLogin(){
        session_start();
        $status = false;
        if(isset($_SESSION["username"]) and isset($_SESSION["id"]) and isset($_SESSION["access_level"])){
            $status = $_SESSION["access_level"];
        }
        return $status;
    }
    protected function trimData($data){
        $dataToReturn = trim($data);
        $dataToReturn = stripslashes($dataToReturn);
        $dataToReturn = htmlspecialchars($dataToReturn);
        return $dataToReturn;
    }
    protected function view_variables($array)
    {
        foreach ($array as $key => $value) {
            $array[$key] = $value;
        }
        return $array;
    }
    protected function load_view($view_name, $data)
    {
        $view_name = VIEWS . $view_name . ".html";
        if (file_exists($view_name)) {
            extract($this->view_variables($data));
            require_once($view_name);
        } else {
            die("cannot find the view file '" . $view_name . "'");

        }
    }
    protected function file_extension($name){
        if(!empty($_FILES[$name]['name'])){
            $ext = '.'.pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);
            return $ext;
        }
    }
    protected function load_model($model_name)
    {
        $model_name = $model_name;
        $name = MODELS . $model_name . ".php";
        if (file_exists($name)) {
            require_once($name);
            return new $model_name;
        } else {
            die("Cannot find the specified model at " . $name);
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
    public function redirect($url){
        if(!headers_sent()){
            header("Location: ".ABS_PATH.$url);
            }
        else{
            die('Link Error: headers already sent');
            }
    }
    public function checkIfSet($array){
        $status = true;
        foreach($array as $value){
            if(!isset($value)){
                $status = false;
                $this->notSet[] = $value;
            }
        }
        return $status;
    }
    public function login($username, $password){
        $model = $this->load_model('MonitorDb');
        $query = "SELECT * FROM users WHERE username = '".$username."' AND password = '".$password."'";
       
        $result = $model->query($query);
        if(!$result){
            die($this->error);
        }
        if(mysqli_num_rows($result) > 0){
            $row = mysqli_fetch_array($result);
            session_start();
            $_SESSION["username"] = $row["username"];
            $_SESSION["id"] = $row["id"];
            $_SESSION["access_level"] = $row["access_id"];
            return TRUE;
        }
        else{
            return FALSE;
        }
    }
    public function checkRequestMethod($type = "POST"){
        $status = FALSE;
        switch($type){
            case "POST":
            if($_SERVER["REQUEST_METHOD"] == $type){
                $status = TRUE;
            }
            else{
                $status = FALSE;
            }
            break;
            case "GET":
            if($_SERVER["REQUEST_METHOD"] == $type){
                $status = TRUE;
            }
            else{
                $status = FALSE;
            }
            break;
        }
        return $status;
        
    }
    public function file_upload($upload_filename, $destination){
        $ext = strtolower(pathinfo($_FILES[$upload_filename]['name'],PATHINFO_EXTENSION));
        
        if(move_uploaded_file($_FILES[$upload_filename]['tmp_name'],$destination)){
            return $_FILES[$upload_filename]['name'].'.'.$ext;
            
        }
        else{
            return false;
        }
    }
    public function gracefulExit($message){
        
    }
}