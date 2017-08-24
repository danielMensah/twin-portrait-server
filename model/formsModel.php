<?php

    function uploadPortraitForm() {
        $form  = '
            <form method="POST" action="/uploadPortrait" enctype="multipart/form-data">
                <input type="file" name="portrait[]" value="" multiple/>
                <input type="submit" value="Upload File"/>
            </form>
        ';

        return $form;
    }

//    ***********IF NEEDED TO UPLOAD LANDMARK, CREATE A FORM HERE AS ABOVE.************