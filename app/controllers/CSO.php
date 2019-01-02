<?php
require_once('app/vendor/autoload.php');
use Mailgun\Mailgun;

class CSO extends Controller
{

    public function __construct()
    {

        $conString = 'mysql:host=' . SQL_HOST . ';dbname=' . SQL_DB;
        $this->dbConn = new PDO($conString, SQL_USER, SQL_PASS);
    }
    public function mail()
    {
        $mg = Mailgun::create('key-3ce7c0dd9a3649a30b87fc1d930e4ad5');

        # Now, compose and send your message.
        # $mg->messages()->send($domain, $params);
        $mg->messages()->send('example.com', [
            'from' => 'bob@example.com',
            'to' => 'kunle@procurementmonitor.org',
            'subject' => 'The PHP SDK is awesome!',
            'text' => 'It is so simple to send a message.'
        ]);
    }

    public function index($login_error = "")
    {
        $auth = new \Delight\Auth\Auth($this->dbConn);
        if ($auth->isLoggedIn()) {
            $csoDb = $this->load_model('CSOModel');
            $cso = $csoDb->getCSO($auth->getUserId());
            if (!isset($_SESSION['cso_id'])) {
                $_SESSION['cso_id'] = $cso['cso_id'];
            }
            $out = [];
            $view['cso_name'] = $cso['name'];
            $view['csoId'] = $cso['cso_id'];
            $view['reports'] = $csoDb->getReports($cso['cso_id']);
            $this->load_view('backend/csoproject', $view);

        } else {
            $this->load_view('frontend/csologin', []);
        }
    }
    public function transact($type = "login")
    {

        switch ($type) {
            case 'login':
                if ($_SERVER["REQUEST_METHOD"] != "POST") {
                    die("Unauthorized Access");
                }

                $auth = new \Delight\Auth\Auth($this->dbConn);
                try {

                    $userId = $auth->login($_POST['email'], $_POST['password']);

                    $this->redirect('CSO');
                } catch (\Delight\Auth\InvalidEmailException $e) {
                    $this->redirect('CSO');
                } catch (\Delight\Auth\InvalidPasswordException $e) {
                    $this->redirect('CSO');

                } catch (\Delight\Auth\EmailNotVerifiedException $e) {
                    $this->redirect('CSO');
                } catch (\Delight\Auth\TooManyRequestsException $e) {


                }
                break;
            case "logout":
                $auth = new \Delight\Auth\Auth($this->dbConn);
                if (!$auth->isLoggedIn()) {
                    echo 'No access not logged in';
                    die();
                }
                $auth->logOut();
                $auth->destroySession();
                $this->redirect('CSO');
                break;
            case 'signup':
                if ($_SERVER["REQUEST_METHOD"] != "POST") {
                    die("Unauthorized Access");
                }
                $auth = new \Delight\Auth\Auth($this->dbConn);
                try {
                    $data = json_decode($_POST['data']);
                    $organisation = $data->organisation;
                    $mail = $this->load_model('Mail');
                    $user = $data->user;
                    $db = $this->load_model('CSOModel');
                    //var_dump($data);
                    $userId = $auth->register($user->email, $user->password);
                    $auth->admin()->addRoleForUserById($userId, \Delight\Auth\Role::PUBLISHER);
                    $db->register($organisation, $user, $userId);
                    $out['status'] = 'success';
                    $out['message'] = 'Successfully Signed Up Proceed to login';
                    echo json_encode($out);


                } catch (\Delight\Auth\InvalidEmailException $e) {
                    $out['status'] = 'danger';
                    $out['message'] = 'Invalid Email Provided';
                    echo json_encode($out);
                } catch (\Delight\Auth\InvalidPasswordException $e) {
                    $out['status'] = 'danger';
                    $out['message'] = 'Invalid Password Provided';
                    echo json_encode($out);
                } catch (\Delight\Auth\UserAlreadyExistsException $e) {
                    $out['status'] = 'danger';
                    $out['message'] = 'User Already Exists';
                    echo json_encode($out);
                } catch (\Delight\Auth\TooManyRequestsException $e) {
                    $out['status'] = 'danger';
                    $out['message'] = 'Too Many Request';
                    echo json_encode($out);
                }
                break;

        }
    }
    public function register()
    {
        try {
            $data = json_decode($_POST['data']);
            $organisation = $data->organisation;
            $mail = $this->load_model('Mail');
            $user = $data->user;
            $db = $this->load_model('CSOModel');
                    //var_dump($data);
            $userId = $auth->register($user->email, $user->password, $user->username, function ($selector, $token) use ($user, $organisation, $db, $mail) {
                $url = ABS_PATH . 'CSO/verifyUser/' . $selector . '/' . $token;
                $html = $mail->confirmHtml($url, 'Email Confirmation', 'Please confirm your email address to complete your registration on budeshi');
                $mail->sendmail($user->email, 'Email Verification', $html);
                $db->register($organisation, $user);
                $out['status'] = 'success';
                $out['message'] = 'A verification Mail has been sent to your mail';
                echo json_encode($out);
            });


        } catch (\Delight\Auth\InvalidEmailException $e) {
            $out['status'] = 'danger';
            $out['message'] = 'Invalid Email Provided';
            echo json_encode($out);
        } catch (\Delight\Auth\InvalidPasswordException $e) {
            $out['status'] = 'danger';
            $out['message'] = 'Invalid Password Provided';
            echo json_encode($out);
        } catch (\Delight\Auth\UserAlreadyExistsException $e) {
            $out['status'] = 'danger';
            $out['message'] = 'User Already Exists';
            echo json_encode($out);
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            $out['status'] = 'danger';
            $out['message'] = 'Too Many Request';
            echo json_encode($out);
        }
        break;

    }
    public function forgetpassword()
    {
        $auth = new \Delight\Auth\Auth($this->dbConn);
        $mail = $this->load_model('Mail');

        try {
            $auth->forgotPassword($_POST['data'], function ($selector, $token) use ($mail) {
                $url = $url = ABS_PATH . 'DataEntry/reset/' . urlencode($selector) . '/' . urlencode($token);
                $html = $mail->confirmHtml($url, "Confirm Email", "Please click the link below to activate your account");
                $mail->sendmail($_POST['data'], 'confirmation', $html);

                $out['ajaxstatus'] = 'success';
                $out['message'] = 'A password reset mail has been sent to ' . $_POST['data'];
                echo json_encode($out);


            });


        } catch (\Delight\Auth\InvalidEmailException $e) {
            $out['ajaxstatus'] = 'danger';
            $out['message'] = 'The email ' . $_POST['data'] . ' you provided is invalid: Try Again';
            echo json_encode($out);
        } catch (\Delight\Auth\EmailNotVerifiedException $e) {
            $out['ajaxstatus'] = 'danger';
            $out['message'] = 'The' . $_POST['data'] . ' has not been verified: Please verify your email';
            echo json_encode($out);
        } catch (\Delight\Auth\ResetDisabledException $e) {
            $out['ajaxstatus'] = 'danger';
            $out['message'] = 'A password reset is not activated on this system';
            echo json_encode($out);
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            $out['ajaxstatus'] = 'danger';
            $out['message'] = 'Too many request';
            echo json_encode($out);
        }
    }
    public function reset($selector, $token)
    {
        $auth = new \Delight\Auth\Auth($this->dbConn);
        if ($auth->canResetPassword($selector, $token)) {
            $out['token'] = $token;
            $out['selector'] = $selector;
            $this->load_view('backend/resetuser', $out);

        } else {
            echo 'Unauthorized Access';
        }

    }
    public function newpass()
    {
        $auth = new \Delight\Auth\Auth($this->dbConn);
        $data = json_decode($_POST['data']);
        $mail = $this->load_model('Mail');
        try {
            $auth->resetPassword($data->selector, $data->token, $data->password);
            $out['ajaxtstatus'] = 'success';
            $out['message'] = 'Password was Successfully reset';
            echo json_encode($out);
        } catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
            $out['ajaxtstatus'] = 'danger';
            $out['message'] = 'Invalid Token set for this call';
            echo json_encode($out);
        } catch (\Delight\Auth\TokenExpiredException $e) {
            $out['ajaxtstatus'] = 'danger';
            $out['message'] = 'Token has expired try reseting your password again!';
            echo json_encode($out);
        } catch (\Delight\Auth\ResetDisabledException $e) {
            $out['ajaxtstatus'] = 'danger';
            $out['message'] = 'The system does not support resetting of password';
            echo json_encode($out);
        } catch (\Delight\Auth\InvalidPasswordException $e) {
            $out['ajaxtstatus'] = 'danger';
            $out['message'] = 'The password you provided is invalid';
            echo json_encode($out);
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            $out['ajaxtstatus'] = 'danger';
            $out['message'] = 'Too Many Request';
            echo json_encode($out);
        }

    }
    public function confirm($selector, $token)
    {
        try {
            $auth->confirmEmail($selector, $token);
            echo 'email has been confirmed proceed to login';
        } catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
            die('Invalid token');
        } catch (\Delight\Auth\TokenExpiredException $e) {
            die('Token expired');
        } catch (\Delight\Auth\UserAlreadyExistsException $e) {
            die('Email address already exists');
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            die('Too many requests');
        }

    }
    public function search_data()
    {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            $data_obj["message"] = "Wrong request method";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        $filter_data = (object)$_POST["search_data"];
        $order = $_POST['order'];
        //var_dump($filter_data);
        $search_text = $_POST['search']['value'];
        $start = $_POST['start'];
        $length = $_POST['length'];
        $draw = $_POST['draw'];
        $db = $this->load_model('CSOModel');
        $data_obj = [];
        $data = $db->ajaxsearch($filter_data, $start, $length);
        if (empty($data)) {
            $data_obj['empty'] = 'true';
        }
        $data_obj['data'] = $data;

        $data_obj['recordsTotal'] = (int)$db->total;
        $data_obj['recordsFiltered'] = (int)$db->filter_total;
        $data_obj['iTotalDisplayRecords'] = (int)$db->total;
        $data_obj['draw'] = $draw;
        $data_obj['total'] = $db->total;
        $json = json_encode($data_obj);
        echo $json;
    }
    public function add_cso_project($id)
    {
        $db = $this->load_model('CSOModel');
        $auth = new \Delight\Auth\Auth($this->dbConn);
        if (!$auth->isLoggedIn()) {
            die('unauthorized access');
        }
        $cso_id = $_SESSION['cso_id'];
        $id = $db->add_project($id, $cso_id);
        $out['message'] = "Project Added";
        $out['status'] = "success";
        echo json_encode($out);
    }
    public function add_new_project()
    {
        $auth = new \Delight\Auth\Auth($this->dbConn);
        if (!$auth->isLoggedIn()) {
            die('unauthorized access');
        }
        $cso_id = $_SESSION['cso_id'];
        $obj = json_decode($_POST['data']);
        $db = $this->load_model('CSOModel');
        $db->add_new_project($cso_id, $obj);
        $out['message'] = "Project Added";
        $out['status'] = "success";
        echo json_encode($out);

    }
    public function add_report($id)
    {
        //var_dump($_FILES);
        @ini_set('upload_max_size', '64M');
        @ini_set('post_max_size', '64M');
        @ini_set('max_execution_time', '300');
            $auth = new \Delight\Auth\Auth($this->dbConn);
        if (!$auth->isLoggedIn()) die('unauthorized access');
        $ext = pathinfo($_FILES['report_pdf']['name'], PATHINFO_EXTENSION);
        if (!empty($_FILES['report_pdf']['name'])) {
            $filename = substr(md5(time()), 0, 6) . '.' . $ext;
            $report_file = REPORT_PATH . $filename;
            if (!$this->file_upload('report_pdf', $report_file)) {
                die("could not upload file");
            }
            switch ($_FILES['report_pdf']['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    echo 'no file';
                    break;
                case UPLOAD_ERR_INI_SIZE:
                    echo 'error ini size file';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    echo 'error form size file';
                    break;
                default:
                    echo 'no Unknown';
                    break;
            }
        }
        $db = $this->load_model('CSOModel');
        $obj = new StdClass;
        $obj->title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $obj->report = filter_input(INPUT_POST, 'report', FILTER_SANITIZE_STRING);
        $obj->filename = empty($filename) ? "" : $filename;
        $project_id = filter_input(INPUT_POST, 'project_id', FILTER_SANITIZE_STRING);
        $cso_id = $_SESSION['cso_id'];
       ///image files
        $filenames = [];
        $imagenames = md5(time());

        $name1 = substr($imagenames, 0, 6);
        $name2 = substr($imagenames, 7, 6);
        $name3 = substr($imagenames, 20, 6);
        $this->upload_images('image-1', MONITORING_PATH . $name1) && $filenames[] = $name1 . $this->file_extension('image-1');
        $this->upload_images('image-2', MONITORING_PATH . $name2) && $filenames[] = $name2 . $this->file_extension('image-2');
        $this->upload_images('image-3', MONITORING_PATH . $name3) && $filenames[] = $name3 . $this->file_extension('image-3');
        $obj->cover = empty($filenames) ? "" : $filenames[0];
        $report_id = $db->add_report($project_id, $cso_id, $obj);
        $db->insert_report_image($report_id, $filenames);
        $out['message'] = "Report Added";
        $out['status'] = "success";
        echo json_encode($out);

    }
    public function edit_upload_files($report_id)
    {
        $auth = new \Delight\Auth\Auth($this->dbConn);
        if (!$auth->isLoggedIn()) die('unauthorized access');
        $db = $this->load_model('CSOModel');
        $this->upload_edit($report_id, 'edit-image-1', 'edit-image-id-1', 'edit-image-name-1', $db);
        $this->upload_edit($report_id, 'edit-image-2', 'edit-image-id-2', 'edit-image-name-2', $db);
        $this->upload_edit($report_id, 'edit-image-3', 'edit-image-id-3', 'edit-image-name-3', $db);
        $this->upload_edit($report_id, 'edit-image-4', 'edit-image-id-4', 'edit-image-name-4', $db);
        $out['message'] = "Upload Success";
        $out['status'] = "success";
        echo json_encode($out);


    }
    private function upload_edit($report_id, $name, $idname, $upfilename, $db)
    {
        if (!empty($_FILES[$name]['name'])) {
            if (empty($_POST[$idname])) {
                $ext = pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);
                $filename = substr(md5(time()), 0, 6);
                $this->upload_images($name, MONITORING_PATH . $filename);
                $ins['imagename'] = $filename . '.' . $ext;
                $ins['report_id'] = $report_id;
                $db->insert($ins, 'report_images');
                $up['cover'] = $filename . '.' . $ext;
                $db->update($report_id, $up, 'cso_reports', 'id');
            } else {
                $path = MONITORING_PATH . $_POST[$upfilename];
                //echo $path;
                if (file_exists($path)) unlink($path);
                $ext = pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);
                $filename = substr(md5(time()), 0, 6);
                //echo $filename;
                $this->upload_images($name, MONITORING_PATH . $filename);
                $up['imagename'] = $filename . '.' . $ext;
                $db->update($_POST[$idname], $up, 'report_images', 'id');
                $cs['cover'] = $filename . '.' . $ext;
                $db->update($report_id, $cs, 'cso_reports', 'id');
            }

        }
    }
    private function upload_images($name, $destination)
    {
        if (!empty($_FILES[$name]['name'])) {
            $extension = pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);
            $this->file_upload($name, $destination . '.' . $extension);
            return true;
        } else {
            return false;
        }

    }
    public function get_edit_report($report_id)
    {
        $auth = new \Delight\Auth\Auth($this->dbConn);
        if (!$auth->isLoggedIn()) die('unauthorized access');
        $db = $this->load_model('CSOModel');
        $obj = $db->get_report_info($report_id);
        echo json_encode($obj);


    }
    public function projects()
    {
        $auth = new \Delight\Auth\Auth($this->dbConn);
        if (!$auth->isLoggedIn()) die('unauthorized access');
        $filter_data = (object)$_POST["search_data"];
        $order = $_POST['order'];
        //var_dump($filter_data);
        $cso_id = $_SESSION['cso_id'];
        $search_text = $_POST['search']['value'];
        $start = $_POST['start'];
        $length = $_POST['length'];
        $draw = $_POST['draw'];
        $db = $this->load_model('CSOModel');
        $data_obj = [];
        $data = $db->projects($cso_id, $filter_data, $start, $length);
        if (empty($data)) {
            $data_obj['empty'] = 'true';
        }
        $data_obj['data'] = $data;

        $data_obj['recordsTotal'] = (int)$db->total;
        $data_obj['recordsFiltered'] = (int)$db->filter_total;
        $data_obj['iTotalDisplayRecords'] = (int)$db->total;
        $data_obj['draw'] = $draw;
        $data_obj['total'] = $db->total;
        $json = json_encode($data_obj);
        echo $json;


    }
    public function edit_report_body($id)
    {
        $auth = new \Delight\Auth\Auth($this->dbConn);
        if (!$auth->isLoggedIn()) die('unauthorized access');
        $data = json_decode($_POST['data']);
        $db = $this->load_model('CSOModel');
        $db->edit_report_body($data, $id);
        $out['message'] = "Edited Successfully";
        $out['status'] = "success";
        echo json_encode($out);


    }
    public function edit_report_file()
    {
        $auth = new \Delight\Auth\Auth($this->dbConn);
        if (!$auth->isLoggedIn()) die('unauthorized access');
        if (empty($_POST['filename'])) {

        }
        $report_file = REPORT_PATH . $_POST['filename'];
        if (!$this->file_upload('report_pdf', $report_file)) {
            die("could not upload file");
        }
        $out['message'] = "File Updated";
        $out['status'] = "success";
        echo json_encode($out);



    }
    public function deleteItem($id, $item)
    {
        $auth = new \Delight\Auth\Auth($this->dbConn);
        if (!$auth->isLoggedIn()) die('unauthorized access');
        $db = $this->load_model("CSOModel");
        $table = $item == "project" ? 'cso_projects' : "cso_reports";
        if ($item == 'project') {
            $db->delete($id, $table);
        } else {
            $files = $db->get_report_files($id);
            foreach ($files as $file) {
                if (file_exists(MONITORING_PATH . $file[0])) {
                    unlink(MONITORING_PATH . $file[0]);
                    //echo "file deleted";
                }
            }
            $db->delete($id, $table);

        }
        $out['message'] = $item . " Deleted From list";
        $out['status'] = "success";
        echo json_encode($out);



    }
    public function reports($page)
    {
        $db = $this->load_model('CSOModel');
        $obj = $db->reports($page);
        echo json_encode($obj);
    }


}

?>