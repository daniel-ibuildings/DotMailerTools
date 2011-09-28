<?php

/**
 * Represents collection of sugar contacts
 */
class SugarBeanContacts extends SugarBean
{
    /**
     * Constructs an instance this class
     *
     * @param  none
     * @return void
     */
    public function __construct()
    {
        parent::SugarBean();
    }
    
    /**
     * Fetches contacts from SugarCRM and creates a collection of contact objects
     *
     * @param  none
     * @return Array collection of Contacts object
     */
    public function getBeanContacts() {
        $beanContacts = array();

        $result = $this->fetchContacts();
        while($row = $this->db->fetchByAssoc($result)) {
            $beanContacts[] = $this->getBeanContact($row['bean_id']);
        }
        return $beanContacts;
    }

    /**
     * Creates and populates an instance of contact
     * 
     * @param  String Id of sugar bean contact
     * @return Contact
     */
    public function getBeanContact($beanId)
    {
        $bean = $this->getBeanObject();
        $bean->retrieve($beanId);
        $bean->fill_in_additional_list_fields();            

        return $bean;
    }

    /**
     * Fetches contacts from the database
     * 
     * @param  none
     * @return Array Collection of contacts
     */
    public function fetchContacts()
    {
        $query = "  SELECT ear.bean_id, ea.email_address FROM email_addresses ea
                    LEFT JOIN email_addr_bean_rel ear ON ea.id = ear.email_address_id
                    WHERE ea.opt_out = 0 
                        AND ea.invalid_email = 0 
                        AND ear.bean_module = 'Contacts'
                        AND ear.primary_address = 1
                        AND ear.deleted = 0
                        AND ear.primary_address = 1";

        return $result = $this->db->query($query);
    }

    /**
     * Creates an instance of Contact object
     * 
     * @param  none
     * @return Contact An instance of contact
     */
    private function getBeanObject()
    {
        return new Contact();
    }
}