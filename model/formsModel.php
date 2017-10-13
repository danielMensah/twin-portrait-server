<?php

    function uploadPortraitForm() {
        $form  = '
            <form method="POST" action="/uploadPortrait" enctype="multipart/form-data">
                <input type="number" name="json" value="0" />
                <button name="api" value="1">API 1</button>
                <button name="api" value="2">API 2</button>
            </form>
        ';

        return $form;
    }

//    ***********IF NEEDED TO UPLOAD LANDMARK, CREATE A FORM HERE AS ABOVE.************