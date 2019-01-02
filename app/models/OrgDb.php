<?php 
class OrganisationModel extends Model
{
    public $perpage;
    public function __construct()
    {
        Parent::__construct();
        $this->perpage = 11;
    }

    public function fetchOrgs($search, $start, $length, $order)
    {
        $columns = ['id', 'id', 'name', 'name'];
        $order = $order[0];
        $order_column = $columns[$order['column']];
    
        $where = empty($search) ? "" : " WHERE name LIKE '%" . mysqli_real_escape_string($this->conn, $search) . "%' OR address LIKE '%" . mysqli_real_escape_string($this->conn, $search) .
            "%' OR email LIKE '%" . mysqli_real_escape_string($this->conn, $search) . "%' OR url LIKE '%" . mysqli_real_escape_string($this->conn, $search) . "%' OR phone LIKE '%" .
            mysqli_real_escape_string($this->conn, $search) . "%'";

        $query = 'SELECT SQL_CALC_FOUND_ROWS id, name FROM institutions '.$where.' ORDER BY  id DESC  
        LIMIT ' .$length.' OFFSET '.$start;

        $arrayToReturn = [];
        $result = $this->query($query);
        if (mysqli_num_rows($result) <= 0) {
            return $arrayToReturn;
        }
        $n_query = "SELECT FOUND_ROWS()";
        $number = $this->query($n_query);
        $number = mysqli_fetch_array($number)[0];
        $this->total = $number;
        $this->filter_total = mysqli_num_rows($result);
        while($row = mysqli_fetch_assoc($result)){
            $rt = [];
            $rt['org_id'] = $row['id'];
            $rt['name'] = $row['name'];
            $arrayToReturn[] = $rt;
        }
        return $arrayToReturn;
    }
    public function renderOrg($id, $name, $rc_no)
    {
        $html = '<tr id ="row'.$id.'">
                    <td><a href="#edit-institution" onclick = "editmodal(\'' . $id . '\')" title="Edit Organization" uk-tooltip="pos: bottom" uk-toggle><span class="uk-margin-small-right" uk-icon="icon: file-edit"></span></a></td>
                    <td uk-toggle="target: #view-institution">' . $name . '</td>
                    <td><a href="#id" onclick = "delete_org(\'' . $id . '\')" title="Delete Organization" uk-tooltip="pos: bottom" uk-toggle><span class="uk-margin-small-right" uk-icon="icon: trash"></span></a></td>
                </tr>';
        return $html;
    }
    public function getOrg($id)
    {
        $query = "SELECT * FROM institutions WHERE id = " . $id;
        $json = $this->queryToJson($query);
        return $json;
    }
    public function updateOrg($id, $obj)
    {
        $data = Array();
        $data["name"] = !empty($obj->name) ? $obj->name : 'NULL';
        $data["ug_no"] = empty($obj->ug_no) ? $obj->ug_no : 'NULL';
        $data["address"] = !empty($obj->streetName) ? $obj->streetName : 'NULL';
        $data["url"] = !empty($obj->uri) ? $obj->uri: "NULL";
        $data["postal_code"] = !empty($obj->postalCode) ? $obj->postalCode : "NULL";
        $data["state"] = !empty($obj->region) ? $obj->region : 'NULL';
        $data["lga"] = !empty($obj->locality) ? $obj->locality : "NULL";
        $data["contact_name"] = !empty($obj->contactName) ? $obj->contactName : "NULL";
        $data["phone"] = !empty($obj->phone) ? $obj->phone: 'NULL';
        $data["email"] = !empty($obj->email) ? $obj->email : "NULL";
        $this->update($id, $data, "institutions", "id");
    }
    public function addOrg($obj)
    {
        $similar = $this->checkOrg($obj->name);
        if($similar){
            return $similar;
        }
        $email = !empty($obj->email) ? $obj->email : "NULL";
        $contact = !empty($obj->contactName) ? $obj->contactName : "NULL";
        $postal_code = !empty($obj->postalCode) ? $obj->postalCode : "NULL";
        $lga = !empty($obj->locality) ? $obj->locality : "NULL";
        $address = !empty($obj->streetName) ? $obj->streetName : "NULL"; 
        $uri = !empty($obj->uri) ? $obj->uri : 'NULL';
        $state = !empty($obj->region) ? $obj->region : 'NULL';
        $phone = !empty($obj->phone)? $obj->phone : "NULL";
        $ug_no = !empty($obj->ug_no)? $obj->ug_no : "NULL";


        $query = "INSERT INTO institutions (name, ug_no, phone, address, contact_name, url, postal_code, state, lga, email) VALUES ('" .mysqli_real_escape_string($this->conn, $obj->name) . "'," . $ug_no . ", '" .
            $phone . "', '" . $address . "', '" .mysqli_real_escape_string($this->conn, $contact ). "','" . $uri . "'," . $postal_code . ", '" . $state . "','" . $lga . "', '" . $email . "')";
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        return mysqli_insert_id($this->conn);

    }
    public function checkOrg($name){
        $query = "SELECT name, id FROM institutions WHERE name LIKE '".mysqli_real_escape_string($this->conn,$name)."';";
        $result = $this->query($query);
        $numrows = mysqli_num_rows($result);
        if($numrows < 1){
            return FALSE;
        }
        else{
            $names = [];
            while($row = mysqli_fetch_assoc($result)){
                $names[] = $row['name'];
            }
            return $names;
        }
    }
    public function deleteOrg($id)
    {
        $this->delete($id, "institutions");
    }
}
?>