<?php

/**
 * Represents SugarCRM contact or DotMailer contact
 */
class DMContact
{
    /**
     * Data mapping between SugarCRM and DotMailer
     */
    private $_dataMap;
    
    /**
     * Default DotMailer fields
     */
    private $_defaultDotMailerFields = array(
        'ID',
        'Email',
        'AudienceType',
        'OptInType',
        'EmailType',
        'Notes'
    );

    /**
     * Constructs an instance of this class
     *
     * @param Array collection of datamap fields
     * @return void
     */
    public function __construct($dataMap)
    {
        $this->_dataMap = $this->appendDefaultFields($dataMap);
    }

    /**
     * Appends default fields to datamap
     *
     * @param  Array collection of datamap fields
     * @return Array datamap fields 
     */
    private function appendDefaultFields($dataMap)
    {
        $dataMap['id'] = array('soap' => 'ID', 'sugar' => 'id');
        return $dataMap;
    }

    /**
     * Initialises properties of this class from data collected using SOAP
     *
     * @param  StdClass An object which contains data from DotMailer
     * @return void
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
     * Initialises properties of this class from SugarBean data
     *
     * @param  StdClass An object which contains data from DotMailer
     * @return void
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
     * Filters required object fields that is need  for comparison
     *
     * @param none
     * @return void
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
     * Compares the object with another object
     *
     * @param DMContact An object of contact 
     * @return boolean
     */
    public function compare(DMContact $contact)
    {
        return $this->getComparableArray() == $contact->getComparableArray();
    }

    /**
     * Changes SugarCRM contact fields to DotMailer corresponding contact fields
     *
     * @param none
     * @return Array
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
