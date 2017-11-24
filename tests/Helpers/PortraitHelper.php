<?php

class PortraitHelper {

    /**
     * @return string
     */
    public function getPortraitInfoHelper() {
        return json_encode(array(
            'title' => 'Passy (Ministre des Finances)',
            'creator' => 'Honoré Daumier',
            'date_created' => 'Unknown',
            'physical_dimensions' => 'Format: 13 3/4 x 9 1/4 in. (34.93 x 23.5 cm); plate: 9 3/4 x 7 1/4 in. (24.77 x 18.42 cm)',
            'external_link' => 'Unknown',
            'external_link_text' => 'Unknown',
        ));
    }

    /**
     * @param PortraitModel $model
     * @return string
     */
    public function addPortraitHelper(PortraitModel $model) {
        $portraitId = $model->getId();

        return json_encode(array(
           'response' => "$portraitId has been added to the database"
        ));
    }
}