<?php

class DMContact
{
    private $dataMap;
    
    private $defaultDotMailerFields = array(
        'ID',
        'Email',
        'AudienceType',
        'OptInType',
        'EmailType',
        'Notes'
    );
    
    public function __construct($dataMap)
    {
        $this->dataMap = $this->appendDefaultFields($dataMap);
    }

    private function appendDefaultFields($dataMap)
    {
        $dataMap['id'] = array('soap' => 'ID', 'sugar' => 'id');
        return $dataMap;
    }

    public function initFromSoap($result)
    {
        foreach ($this->dataMap as $property => $keys) {
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

    public function initFromSugarBean($bean)
    {
        foreach ($this->dataMap as $property => $keys) {
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

    public function toSoapParam()
    {
        $self = get_object_vars($this);

        $soapParam = array();
        $soapParam['OptInType'] = $self['optIn'] ? 'Single' : 'Unknown';

        $dataFields = array();

        unset($self['defaultDotMailerFields'], $self['id'], $self['optIn']);

        foreach ($self as $property => $value) {
            $propertyName = isset($this->dataMap[$property]) ? $this->dataMap[$property]['soap'] : $property;

            if (!in_array($propertyName, $this->defaultDotMailerFields)) {
                $dataFields[$propertyName] = $value;
            } else {
                $soapParam[$propertyName] = $value;
            }
        }

        if (!empty($dataFields)) {
            $soapParam['DataFields'] = array();
            $soapParam['DataFields']['Keys'] = array();
            $soapParam['DataFields']['Values'] = array();

            foreach ($dataFields as $property => $value) {
                $soapParam['DataFields']['Keys'][] = strtoupper($property);
                $soapParam['DataFields']['Values'][] =
                    new SoapVar($value, XSD_STRING, 'string', 'http://www.w3.org/2001/XMLSchema');
            }
        }

        return $soapParam;
    }
}
