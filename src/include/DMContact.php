<?php

/**
 *
 */
class DMContact
{
    private $_dataMap;
    private $_defaultDotMailerFields = array(
        'ID',
        'Email',
        'AudienceType',
        'OptInType',
        'EmailType',
        'Notes'
    );

    /**
     *
     */
    public function __construct($dataMap)
    {
        $this->_dataMap = $this->appendDefaultFields($dataMap);
    }

    /**
     *
     */
    private function appendDefaultFields($dataMap)
    {
        $dataMap['id'] = array('soap' => 'ID', 'sugar' => 'id');
        return $dataMap;
    }

    /**
     *
     */
    public function initFromSoap($result)
    {
        foreach ($this->_dataMap as $property => $keys) {
            if (isset($result->$keys['soap'])) {
                $this->$property = $result->$keys['soap'];
                continue;
            }

            // @codingStandardsIgnoreStart
            if (!isset($result->DataFields)) {
                continue;
            }

            $dataFields = $result->DataFields;
            $dataKeys = $dataFields->Keys;
            $dataValues = $dataFields->Values;
            
            // @codingStandardsIgnoreEnd

            $index = array_search($keys['soap'], $dataKeys->string);
            if ($index) {
                $this->$property = $dataValues->anyType[$index];
            }
        }

        // @codingStandardsIgnoreStart
        $this->optIn = false;
        if (isset($result->OptInType) && $result->OptInType !== 'Unknown') {
            $this->optIn = true;
        }

        $this->audienceType = null;
        if (isset($result->AudienceType)) {
            $this->audienceType = $result->AudienceType;
        }

        $this->emailType = null;
        if (isset($result->EmailType)) {
            $this->emailType = $result->EmailType;
        }
        // @codingStandardsIgnoreEnd
    }

    /**
     *
     */
    public function initFromSugarBean($bean)
    {
        foreach ($this->_dataMap as $property => $keys) {
            if (isset($bean->$keys['sugar'])) {
                $this->$property = $bean->$keys['sugar'];
            }
        }

        // @codingStandardsIgnoreStart
        $this->optIn = $bean->email_opt_out === '0' ? true : false;
        // @codingStandardsIgnoreEnd
    }

    /**
     *
     */
    public function getComparableArray()
    {
        $self = get_object_vars($this);
        unset($self['id']);
        unset($self['audienceType']);
        unset($self['emailType']);
        return $self;
    }

    /**
     *
     */
    public function compare(DMContact $contact)
    {
        return $this->getComparableArray() == $contact->getComparableArray();
    }

    /**
     *
     */
    public function toSoapParam()
    {
        $self = get_object_vars($this);

        $soapParam = array();
        
        $soapParam['OptInType'] = 'Unknown';
        if (isset($self['optIn']) && $self['optIn']) {
            $soapParam['OptInType'] = 'Single';
        }

        $soapParam['AudienceType'] = 'Unknown';
        if (isset($self['audienceType'])) {
            $soapParam['AudienceType'] = $self['audienceType'];
        }

        $soapParam['EmailType'] = 'Html';
        if (isset($self['emailType'])) {
            $soapParam['EmailType'] = $self['emailType'];
        }

        $dataFields = array();

        unset($self['dataMap'], $self['_dataMap']);
        unset($self['defaultDotMailerFields'], $self['_defaultDotMailerFields']);
        unset($self['id']);
        unset($self['optIn']);
        unset($self['audienceType']);
        unset($self['emailType']);

        foreach ($self as $property => $value) {

            $propertyName = $property;
            if (isset($this->_dataMap[$property])) {
                $propertyName = $this->_dataMap[$property]['soap'];
            }
            
            if (!in_array($propertyName, $this->_defaultDotMailerFields)) {
                $dataFields[$propertyName] = $value;
            } else {
                $soapParam[$propertyName] = $value;
            }
        }

        if (!empty($dataFields)) {
            $soapParam['DataFields'] = array();
            $soapParam['DataFields']['Keys'] = array();
            $soapParam['DataFields']['Values'] = array();

            $xsdType = XSD_STRING;
            $type = 'string';
            $nameSpace = 'http://www.w3.org/2001/XMLSchema';

            foreach ($dataFields as $property => $value) {
                $soapParam['DataFields']['Keys'][] = strtoupper($property);
                $soapParam['DataFields']['Values'][] =
                    new SoapVar($value, $xsdType, $type, $nameSpace);
            }
        }

        return $soapParam;
    }
}
