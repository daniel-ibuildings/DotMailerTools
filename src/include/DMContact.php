<?php

class DMContact
{
    private function appendDefaultFields($dataMap)
    {
        $dataMap['id'] = array('soap' => 'ID', 'sugar' => 'id');
        return $dataMap;
    }

    public function initFromSoap($dataMap, $result)
    {
        $dataMap = $this->appendDefaultFields($dataMap);

        foreach ($dataMap as $property => $keys) {
            if (isset($result->$keys['soap'])) {
                $this->$property = $result->$keys['soap'];
                continue;
            }

            if (!isset($result->DataFields)) {
                continue;
            }

            $dataFields = $result->DataFields;

            if ($index = array_search($keys['soap'], $dataFields->Keys->string)) {
                $this->$property = $dataFields->Values->anyType[$index];
            }
        }

        $this->optIn = $result->OptInType !== 'Unknown' ? true : false;
    }

    public function initFromSugarBean($dataMap, $bean)
    {
        $dataMap = $this->appendDefaultFields($dataMap);

        foreach ($dataMap as $property => $keys) {
            if (isset($bean->$keys['sugar'])) {
                $this->$property = $bean->$keys['sugar'];
            }
        }

        $this->optIn = $bean->email_opt_out === '0' ? true : false;
    }

    public function getComparableArray() {
        $self = get_object_vars($this);
        unset($self['id']);
        return $self;
    }

    public function compare(DMContact $contact)
    {
        return $this->getComparableArray() == $contact->getComparableArray();
    }
}
