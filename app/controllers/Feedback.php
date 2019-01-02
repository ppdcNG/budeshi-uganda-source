<?php
class Feedback extends Controller{

    public function index($page= 1){
        if (!$this->checkLogin()) {
            $data_obj["message"] = "unauthorized, this location is forbidden";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        $db = $this->load_model('FeedbackDb');
        $feedbacks = $db->feedback($page);
        $data['feedback'] = $feedbacks;
        $data['pagination'] = $db->page_links;
        $this->load_view('backend/feedback', $data);
    }
    public function delete($id){
        if (!$this->checkLogin()) {
            $data_obj["message"] = "unauthorized, this location is forbidden";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        $id = filter_var($id, FILTER_VALIDATE_INT);
        $db = $this->load_model('FeedbackDb');
        $status = $db->delete($id);
        if($status){
            $data['ajaxstatus'] = 'Success';
            $data['message'] = 'Feedback wass successfully deleted';
            echo json_encode($data);
        }
        else{
            $data['ajaxstatus'] = 'fail';
            $data['message'] = 'Feedback was not successfully deleted';
            echo json_encode($data);

        }

    }
    public function publish($id){
        if (!$this->checkLogin()) {
            $data_obj["message"] = "unauthorized, this location is forbidden";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        $id = filter_var($id, FILTER_VALIDATE_INT);
        $db = $this->load_model('FeedbackDb');
        $status = $db->publish($id);
        if($status){
            $data['ajaxstatus'] = 'success';
            $data['message'] = 'Feedback wass successfully Published';
            echo json_encode($data);
        }
        else{
            $data['ajaxstatus'] = 'fail';
            $data['message'] = 'Feedback was not successfully Published';
            echo json_encode($data);

        }

    }
    public function contact($id){
        if (!$this->checkLogin()) {
            $data_obj["message"] = "unauthorized, this location is forbidden";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        $id = filter_var($id, FILTER_VALIDATE_INT);
        $db = $this->load_model('FeedbackDb');
        $status = $db->contact($id);
        if($status){
            $data['ajaxstatus'] = 'success';
            $data['message'] = 'Feedback wass successfully Published';
            $data['email'] = $status['email'];
            $data['phone'] = $status['phone'];
            echo json_encode($data);
        }
        else{
            $data['ajaxstatus'] = 'fail';
            $data['message'] = 'Feedback was not successfully Published';
            echo json_encode($data);

        }

    }
}
?>