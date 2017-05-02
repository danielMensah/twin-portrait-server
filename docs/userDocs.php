<?php
function getDocs(){
    $response = '
       <h4>To access the User Login part of the API please follow the instructions below:</h4><br><b>This is for the /user part of the API.</b>
       <hr>
       <p>This works on a POST method from the AJAX or Form action. The variables that are used here are:</p><br>
       <ul>
           <li>\'username\' - Username that the username has entered</li>
           <li>\'password\' - Password that the username has entered</li>
       </ul><br>
       <hr>
       <p>Provided that these conditions are met, you should receive back a JSON object with the follow results</p>
       <ul>
        <li>\'u_auth\' - This is the username authorization. If marked as 1 then the user exists, if 0 doesn\'t exist.</li>
        <li>\'p_auth\' - This is the password authorization. If marked as 1 then the password is correct, if 0 password incorrect.</li>
        <li>\'u_token\' - This is the user token of the user for the current session. If marked as 0 then the token has not been generated. This would be the case if the user is not authorised.</li>
       </ul>
       <p>That concludes this function</p>
    ';
    return $response;
}