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

        return "Portrait: $portraitId added from the database!";
    }

    /**
     * @param PortraitModel $model
     * @return string
     */
    public function deletePortraitHelper(PortraitModel $model) {
        $portraitId = $model->getId();

        return "Portrait: $portraitId deleted from the database!";
    }

    public function convertLandmarkValueHelper() {
        return array(
                "deep-set" => 4,
                "monolid/almond" => 3,
                "downturned" => 2,
                "hooked" => 1
                );
    }

    public function generateUpdatedLandmarkValuesHelper() {
        return array(
            "mustache" => 0,
            "beard" => 0.5,
            "eyebrows" => array(
                "flat_shaped" => 3,
                "rounded" => 1.5,
                "angled" => 1.5),
            "eye" => array(
                "deep_set" => 4,
                "monolid_almond" => 2,
                "downturned" => 2,
                "hooded" => 2),
            "nose" => array(
                "aquiline" => 3,
                "flat" => 3,
                "roman_hooked" => 3,
                "snub" => 1)
        );
    }

    public function generateOldValuesDummyData() {
        return array(
            "mustache" => 0,
            "beard" => 1,
            "eyebrows" => array(
                "flat_shaped" => 3,
                "rounded" => 1,
                "angled" => 2),
            "eye" => array(
                "deep_set" => 4,
                "monolid_almond" => 1,
                "downturned" => 2,
                "hooded" => 3),
            "nose" => array(
                "aquiline" => 2,
                "flat" => 3,
                "roman_hooked" => 4,
                "snub" => 1),
            "completed" => 1
        );
    }

    public function generateNewValuesDummyData() {
        return array(
            "mustache" => false,
            "beard" => false,
            "eyebrows" => array("flat_shaped", "rounded", "angled"),
            "eye" => array("deep_set", "monolid_almond", "downturned", "hooded"),
            "nose" => array("aquiline", "flat", "roman_hooked", "snub")
        );
    }

    public function updatePortraitHelper($portraitId) {
        return json_encode(array(
           "response" => "Portrait : $portraitId was updated"
        ));
    }

    public function generatePossibleDoppelgangerHelper() {
        return json_encode(array(

        ));
    }
}