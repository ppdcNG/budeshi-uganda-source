<?php
class FeedbackDb extends Model{
    public $page_links = '';
    function __construct()
    {
        Parent::__construct();
    }
    function feedback($page){
        $start = ($page - 1) & PERPAGE;
        $query = "SELECT SQL_CALC_FOUND_ROWS f.*, p.title FROM feedbacks f LEFT JOIN projects p ON f.project_id = p.id LIMIT ".PERPAGE." OFFSET ".$start;
        $result = $this->query($query);
        if(mysqli_num_rows($result)> 0){
            $n_query = "SELECT FOUND_ROWS()";
                $number = $this->query($n_query);
                $number = mysqli_fetch_array($number)[0];
                

                $page_links = $this->renderPageLinks($page, $number,'uk-active',"Feedback/index/");
                $this->page_links = $page_links;
                
            $feed_backs = "";
            while($row = mysqli_fetch_assoc($result)){
                $name = $row['firstname']." ".$row['lastname'];
                $date = date('jS F Y', strtotime($row['date_posted']));
                $comment = $row['feedback_comment'];
                $published = $row['published'] == 'yes'? "Un-Publish": "Publish";
                $pubBttn = $row['published'] == 'no'? "Not Published": "Published";
                $feed_backs .= $this->render_feedbacks($row['id'], $row['title'],$date,$published,$pubBttn,$name,$comment);
                

            }
            return $feed_backs;
        }
        else{
            return "<em>No Feedbacks Received Yet!!!</em>";
        }

    }
    public function delete($id){
        $query = 'DELETE FROM feedbacks WHERE id = '.$id.' LIMIT 1';
        return $this->query($query);
    }
    public function publish($id){
        $query = "UPDATE feedbacks SET published = CASE WHEN published = 'yes' THEN 'no' ELSE 'yes' END WHERE id = ".$id;
        return $this->query($query);
    }
    public function contact($id){
        $query = 'SELECT email, phone FROM feedbacks WHERE id = '.$id;
        $result = $this->query($query);
        return mysqli_fetch_assoc($result);
    }
    private function render_feedbacks($id, $project_title, $time, $pub,$pubBttn, $name, $comments){
        $html = '<li>
        <article class="uk-comment uk-comment-primary">
            <header class="uk-comment-header uk-grid-medium uk-flex-middle" uk-grid>
                <div class="uk-width-auto">
                    <img class="uk-comment-avatar" src="'.ABS_PATH.'images/boss.png" width="80" height="80" alt="">
                </div>
                <div class="uk-width-expand">
                    <h4 class="uk-comment-title uk-margin-remove">
                        <a class="uk-link-reset" href="#">'.$name.'</a>
                    </h4>
                    <ul class="uk-comment-meta uk-subnav uk-subnav-divider uk-margin-remove-top">
                        <li>
                            <a href="#">'.$time.'</a>
                        </li>
                        <li>
                            <a href="#publish-feed"  onclick = "togglePub(\''.$id.'\')" class="uk-button uk-button-default" uk-toggle>'.$pub.'</a>
                        </li>
                        <li>
                            <a href="#contact-feed" onclick = "contact(\''.$id.'\')" class="uk-button uk-button-default" uk-toggle>Contact</a>
                        </li>
                        <li>
                            <a href="#delete-feed" onclick = "del_feedback(\''.$id.'\')" class="uk-text-danger uk-button uk-button-default" uk-toggle>Delete</a>
                        </li>
                    </ul>
                    <ul class="uk-comment-meta uk-subnav uk-subnav-divider uk-margin-remove-top">
                        <li>Status:
                            <span class="uk-text-primary uk-text-bold uk-margin-left">'.$pubBttn.'</span>
                        </li>
                    </ul>
                </div>
            </header>
            <div class="uk-comment-body">
                <h4 class="uk-heading-divider uk-text-warning">'.$project_title.'</h4>
                <p>'.$comments.'</p>
            </div>
        </article>


    </li>';
    return $html;

    }
    private function renderPageLinks($page, $total, $active_class = 'uk-active', $path = 'ProcuringEntity/projects/')
    {
        $page = (int)$page;
        $pages = ceil($total / PERPAGE);
        $start = $page - 4;
        $end = $page + 4;
        // set pages to 1 incase negetive division
        if ($pages < 1) {
            $pages = 1;
        }
        //check if start page overflows
        if ($start < 1) {
            $end -= ($start - 1);
            $start = 1;

        }
        if ($end > $pages) {
            $start -= ($end - $pages);
            $end = $pages;
        }
        if ($end >= $pages && $start <= 0) {
            $end = $pages;
            $start = 1;
        }
        $url = ABS_PATH . $path;
       
        /// loop from start to end to build the links
        $mid_links = "";
        for ($i = $start; $i <= $end; $i++) {
            $active = ($i == $page) ? $active_class : "";
            $link = $url . $i;
            $mid_links .= $this->onePageLink($i, $link, $active);
        }
        $front_link = $page > 5 ? '<li><a href="' . $url . '1">1</a></li><li class="uk-disabled"><span>...</span></li>' : "";
        $back_link = $page > ($pages - 4) ? "" : '<li class="uk-disabled"><span>...</span></li><li><a href="' . $url . $pages . '">' . $pages . '</a></li>';
        $prev = $page > 1 ? '<li><a href="' . $url . ($page - 1) . '"><span uk-pagination-previous></span></a></li>' : '<li class = "uk-disabled"><a href="#"><span uk-pagination-previous></span></a></li>';
        $next = $page >= $pages ? '<li class = "uk-disabled"><a href="#"><span uk-pagination-next></span></a></li>' : '<li><a href="' . $url . ($page + 1) . '"><span uk-pagination-next></span></a></li>';

        $pageLinks = $prev . $front_link . $mid_links . $back_link . $next;
        return $pageLinks;
    }
    private function onePageLink($number, $link, $active = "")
    {
        return '<li class = ' . $active . '><a href="' . $link . '">' . $number . '</a></li>';
    }
}
?>