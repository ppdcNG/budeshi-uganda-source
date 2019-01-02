<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail extends Model
{
    public function __construct()
    {
        Parent::__construct();
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
    public function index()
    {
        $array = ['PHPMailer', 'SMTP', 'Exception'];
        $this->load_helper($array);
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            //Server settings
            $mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.startlogic.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'amebo@procurementmonitor.org';                 // SMTP username
            $mail->Password = 'Amebo12345$';                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to
        
            //Recipients
            $mail->setFrom('amebo@procurementmonitor.org', 'Amebo');
            $mail->addAddress('kunle@procurementmonitor.org', 'Kunle');     // Add a recipient
            
            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Here is the subject';
            $mail->Body = 'This is the HTML message body <b>in bold!</b>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }

    }
    public function sendmail($to, $subject, $body)
    {
        $array = ['PHPMailer', 'SMTP', 'Exception'];
        $this->load_helper($array);
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            //Server settings
            $mail->SMTPDebug = 0;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.mailgun.org';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'postmaster@app.budeshi.ug';                 // SMTP username
            $mail->Password = 'ad3b0fc1dd77906de5a283a2f59d550f-4836d8f5-276393a2';                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to
           
        
            //Recipients
            $mail->setFrom('postmaster@app.budeshi.ng', 'Budeshi');
            $mail->addAddress($to);     // Add a recipient
            
            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            return true;
        } catch (Exception $e) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
            die();
        }

    }
    public function send()
    {
        $model = $this->load_model('user');
        $result = $model->user_cred();
        $array = ['PHPMailer', 'SMTP', 'Exception'];
        $this->load_helper($array);
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;                                 // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.startlogic.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'amebo@procurementmonitor.org';                 // SMTP username
        $mail->Password = 'Amebo12345$';                           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to
        $mail->Subject = 'Amebo Platform Testing';
        $mail->isHTML(true); 
        //Recipients
        $mail->setFrom('amebo@procurementmonitor.org', 'Amebo');
        while ($row = $result->fetch_assoc()) {
            $mail->Body = $this->html("Amebo Platform Testing", $row['firstname'], $row['username'], $row['password']);
            $mail->addAddress($row['email'], $row['firstname']);
            if (!$mail->send()) {
                echo "Mailer Error (" . str_replace("@", "&#64;", $row["email"]) . ') ' . $mail->ErrorInfo . '<br />';
                break; //Abandon sending
            } else {
                echo "Message sent to :" . $row['firstname'] . ' (' . str_replace("@", "&#64;", $row['email']) . ')<br />';
                //Mark it as sent in the DB
            }
            // Clear all addresses and attachments for next loop
            $mail->clearAddresses();
            $mail->clearAttachments();
        }
    }
    public function confirmHtml($confirmLink, $title, $text)
    {
        $html = "<html>
        
        <head>
            <title>NACA Open Contracting</title>
        </head>
        <style>
            #main-container{
                position:relative;
                width: 80%;
                margin: 0 auto;
            }
            #main-title{
                margin-top: 10px;
                padding-top: 10px;
                text-align: center;
            }
            #title-container{
                margin: 0 auto;
                margin-top: 10px;
                margin-bottom: 10px;
                padding-top: 100px;
                color: white;
                text-align: center;
                background-image: url('http://procurementmonitor.org/amebo/images/cover4.jpg');
                border-bottom: 5px solid rgb(19, 65, 128);
            }
            #mail-title{
                color: red;
                padding: 2px, 2px;
                width: 100%;
                margin: 0 auto;
                text-align: center;
                border-bottom: 3px double rgb(19, 65, 128);
            }
            p{
                width: 60%;
                margin: 0 auto;
            }
            #user{
                width: 80%;
                margin: 0 auto;
                text-align: center;
                margin-top: 10px;
                padding: 4px, 4px;
                color: black;
        
            }
            #credentials{
                padding: 10px, 10px, 10px, 10px;
            }
            .note{
                color: red;
                font-style: italic;
                margin-bottom: 20px;
                padding-bottom: 18px;
                margin-top: 20px;
            }
            #gs{
                
                display: inline-block;
                width: 100px;
                background-color: red;
                color:white;
                border-radius: 3px;
                border: 1px groove rgb(81, 0, 255);
                text-decoration: none;
                height: 30px;
                margin-top: 20px;
                vertical-align: center;
                text-align: center;
        
            }
            #footer{
                width: 100%;
                margin: 0 auto;
                text-align: center;
                font-size: 12px;
                background-color: rgb(19, 65, 128);
                color: white;
            }
        </style>
        <body>
            <div id = 'main-container'>
                <div id = 'title-container'>
                    <h1 id = 'main-title'>Amebo</h1>
                    <h3 id = 'motto-line'>PPDC's Daily Reporting Platform</h3>
                </div>
                <h2 id = 'mail-title'>" . $title . "</h2>
                <p id = 'user'><b>Hello Dear!!</b></p>
                <p>" . $text . "
                    <br>
                    Click this <a href = '" . $confirmLink . "'>link</a>  to activate your account 
                </p>
                
                <p class = 'note'> 
                   <br>
                    <a href = '" . $confirmLink . "' id = 'gs'>Go</a>
                 </p>
                <p>Yours Faithfully, Budeshi
                </br>
                Thanks;
                </p>
                
                
        
                    <div id = 'footer'>Confirm your email address from Budeshi</div>
            </div>
        </body>
        
        </html>";
        return $html;
    }
}



?>