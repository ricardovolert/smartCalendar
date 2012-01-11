<?php

class Facebook_model extends CI_Model{

    function getEvents(){
         ini_set('include_path',ini_get('include_path').PATH_SEPARATOR.BASEPATH.'../application/libraries/Facebook_library/');
        include_once 'src/facebook.php';
        $facebook=new Facebook(array(
           'appId'=> '217199838364750',
           'secret'=>'9c90b5a4a7962399d83eab8963c4602b',
           'cookie'=>true
        ));

        $user=$facebook->getUser();
        if($user){
            if(session_id()){

            }else{
                session_start();
            }
            //675288774
            $access_token=$facebook->getAccessToken();
            $permission_list=$facebook->api(
                '/me/events',
                'GET',
                array('access_token'=>$access_token)
            );
           $page_id=$facebook->getUser();
          // echo $page_id."<br/>";
              $fql = 'SELECT
                        eid,
                        name,
                        host,
                        start_time,
                        end_time,
                        description,
                        location
                    FROM event
                    WHERE eid IN
                        (SELECT eid
                            FROM event_member
                            WHERE uid='.$page_id.'
                        )
                    AND end_time >= ' . mktime() . '
                    ORDER BY start_time ASC
            ';
            $ret_obj = $facebook->api(array(
                'method' => 'fql.query',
                'query' => $fql,
            ));
            $html = '';
            foreach($ret_obj as $key)
            {
                $facebook_url = 'https://www.facebook.com/event.php?eid=' . $key['eid'];

                $start_time = date('M j, Y \a\t g:i A', $key['start_time']);
                $end_time = date('M j, Y \a\t g:i A', $key['end_time']);

                $html .= '
                    <div class="event">
                        <span>
                            <a href="'.$facebook_url.'">
                                <h2>'.$key['name'].'</h2>
                            </a>
                            <h4>'.$key['host'].'</h4>
                            <h4>'.$key['location'].'</h4>
                            <p>'.strip_tags($key['description']).'</p>
                            <p class="time">'.$start_time.'</p>
                            <p class="time">'.$end_time.'</p>
                            <br/><br/>
                        </span>
                    </div>
                ';
            }

            echo $html;

        }else{
            $login_url_params=array(
                'scope'=>'read_stream, publish_stream, offline_access, manage_pages, user_events',
                'fbconnect' =>  1,
                'redirect_uri' => 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']
            );
            echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $login_url=$facebook->getLoginUrl($login_url_params);
            header("Location: {$login_url}");
            exit();
        }
    }
}